from random import random
import time
import pandas as pd
from urllib.parse import urljoin
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import NoSuchElementException, TimeoutException

# ---------------- CONFIG ----------------
LOGIN_URL = "https://vtu.internyet.in/sign-in"
BASE_URL = "https://vtu.internyet.in"   # used for joining relative hrefs
EMAIL = "admissions@visionastraa.com"
PASSWORD = "VisionAstraa@23"
# Changed output file name to reflect the new action
OUTPUT_XLSX = "applied_to_shortlisted_applicants.xlsx" 

internship = "EMBEDDED SYSTEMS FOR EV ( Microcontrollers , Mechatronics , IOT , ADAS )"
status = "Under Review"

def human_delay(min_s=0.5, max_s=1.5):
    try:
        time.sleep(random.uniform(min_s, max_s))
    except Exception:
        # defensive: in case sleep is interrupted for some reason, don't crash
        pass

# ---------------- SETUP DRIVER ----------------
chrome_options = Options()
# Make sure the browser window is maximized to ensure all elements are visible
chrome_options.add_argument("--start-maximized")
# comment out the two lines below if you want the browser to close automatically
# chrome_options.add_experimental_option("detach", True)
# chrome_options.add_argument("--headless")  # comment in if you want headless, but UI interaction can be tricky headless

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
# Increased wait time for potentially slower page loads/updates
wait = WebDriverWait(driver, 20)
short_wait = WebDriverWait(driver, 5) # For quick checks

# ---------------- LOGIN ----------------
print("Logging in...")
driver.get(LOGIN_URL)
# wait for email field
wait.until(EC.presence_of_element_located((By.XPATH, "//input[@placeholder='Enter your email address' or @name='email' or @id='email']")))

# update the selectors below if your login fields are different
email_el = driver.find_element(By.XPATH, "//input[@placeholder='Enter your email address' or @name='email' or @id='email']")
password_el = driver.find_element(By.XPATH, "//input[@type='password' or @placeholder='Enter your password' or @name='password']")

email_el.clear()
email_el.send_keys(EMAIL)
password_el.clear()
password_el.send_keys(PASSWORD)

# try to click sign-in button (adjust text if needed)
try:
    driver.find_element(By.XPATH, "//button[contains(., 'Sign in') or contains(., 'Sign In') or contains(., 'Login') or contains(., 'Log in')]").click()
except:
    password_el.submit()

# Wait until the "Applicants" span is clickable, then click it
print("Navigating to Applicants page...")
applicants_btn = wait.until(
    EC.element_to_be_clickable((By.XPATH, "//span[normalize-space()='Applicants']"))
)
applicants_btn.click()

# ---------------- NEW: FILTER BY INTERNSHIP NAME ----------------
try:
    print("Applying 'Internship Name' filter...")
    # 1. Click on the "Internship Name" filter button to open the dropdown
    internship_filter_btn = wait.until(
        EC.element_to_be_clickable((By.XPATH, "//button[contains(., 'Internship Name')]"))
    )
    internship_filter_btn.click()
    
    # 2. Click on the specific internship option from the dropdown
    internship_option_text = internship
    
    # ------------------- FIX 1 START -------------------
    # The error message indicated the clickable element is a <div role="option">
    # This XPath is much more specific and less likely to be ambiguous.
    internship_option_xpath = f"//div[@role='option'][normalize-space(.)='{internship_option_text}']"
    
    # Wait for the option to be PRESENT in the DOM
    internship_option = wait.until(
        EC.presence_of_element_located((By.XPATH, internship_option_xpath))
    )
    
    # Scroll the option into view using JavaScript
    print("Scrolling to internship option...")
    driver.execute_script("arguments[0].scrollIntoView(true);", internship_option)
    
    # Now that it's in view, wait for it to be clickable
    wait.until(
        EC.element_to_be_clickable((By.XPATH, internship_option_xpath))
    )
    
    # Use a JavaScript click to bypass the "element click intercepted" error
    print("Clicking internship option via JavaScript...")
    driver.execute_script("arguments[0].click();", internship_option)
    # ------------------- FIX 1 END -------------------
    
    # 3. Wait for the table to refresh
    print(f"Waiting for '{internship_option_text}' filter to apply...")
    time.sleep(3) # Give 3 seconds for the table list to populate

except TimeoutException:
    print("Could not find or apply the 'Internship Name' filter. Exiting.")
    driver.quit()
    exit()
except Exception as e:
    print(f"An error occurred while filtering by Internship Name: {e}")
    driver.quit()
    exit()
# ---------------- END OF NEW INTERNSHIP FILTER ----------------


# ---------------- FILTER BY STATUS (MODIFIED) ----------------
try:
    print("Applying 'Under Review' filter...") # MODIFIED
    # 1. Click on the "Application Status" filter button to open the dropdown
    # This selector assumes it's a button with text. Adjust if it's different.
    status_filter_btn = wait.until(
        EC.element_to_be_clickable((By.XPATH, "//button[contains(., 'Application Status')]"))
    )
    status_filter_btn.click()
    
    # 2. Click on the "Under Review" option from the dropdown (MODIFIED)
    # ------------------- FIX 4 START -------------------
    # Apply the same robust clicking logic as the internship filter
    status_option_text = status
    status_option_xpath = f"//div[@role='option'][normalize-space(.)='{status_option_text}']"
    
    applied_option = wait.until(
        EC.element_to_be_clickable((By.XPATH, status_option_xpath)) # MODIFIED XPath
    )
    
    # Use JavaScript click to prevent interception
    print("Clicking 'Under Review' option via JavaScript...")
    driver.execute_script("arguments[0].click();", applied_option) # MODIFIED click
    # ------------------- FIX 4 END -------------------
    
    # 3. Wait for the table to refresh
    print("Waiting for 'Under Review' filter to apply...") # MODIFIED
    time.sleep(3) # Give 3 seconds for the table list to populate

except TimeoutException:
    print("Could not find or apply the 'Under Review' filter. Exiting.") # MODIFIED
    driver.quit()
    exit()
except Exception as e:
    print(f"An error occurred while filtering: {e}")
    driver.quit()
    exit()


# ---------------- PROCESS APPLICANTS (MODIFIED) ----------------
applicants_data = []

while True:  # Loop over pages
    
    # 1. Collect applicant EDIT links for current page
    print("Finding edit links on the current page...")
    try:
        # Wait for at least one edit link to be present
        wait.until(
            EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]"))
        )
        
        # Find all edit links
        all_links = driver.find_elements(By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]")
        
        # Get the actual href attribute from each link
        # We must do this *before* iterating, as the page will change
        action_links = [link.get_attribute("href") for link in all_links if link.get_attribute("href")]
        # Filter out any potential duplicates if multiple icons/links go to the same URL
        action_links = list(dict.fromkeys(action_links)) 

    except TimeoutException:
        print("No edit links found on this page. Assuming end of list.")
        break # Exit page loop if no links are found
    except Exception as e:
        print(f"Error collecting links: {e}")
        break

    print(f"Found {len(action_links)} applicant edit links on current page.")

    # 2. Visit each applicant link
    for i, link in enumerate(action_links, start=1):
        try:
            print(f"Processing link {i}/{len(action_links)}: {link}")
            driver.get(link)

            # Wait for "Update Status" button to ensure page is loaded
            wait.until(
                EC.presence_of_element_located((By.XPATH, "//button[normalize-space()='Update Status']"))
            )
            
            # --- Scrape Data (using original XPaths, adjust if needed) ---
            scraped_info = {}
            try:
                scraped_info["Name"] = driver.find_element(By.XPATH, "//h3[text()='Applicant']/following::p[1]").text
            except NoSuchElementException:
                scraped_info["Name"] = ""
            try:
                scraped_info["Email"] = driver.find_element(By.XPATH, "//h3[text()='Applicant']/following::p[2]").text
            except NoSuchElementException:
                scraped_info["Email"] = ""
            try:
                scraped_info["Phone"] = driver.find_element(By.XPATH, "//h3[text()='Applicant']/following::p[3]").text
            except NoSuchElementException:
                scraped_info["Phone"] = ""
            try:
                scraped_info["College"] = driver.find_element(By.XPATH, "//h3[text()='Academic Details']/following::p[1]").text
            except NoSuchElementException:
                scraped_info["College"] = ""
            try:
                scraped_info["Branch"] = driver.find_element(By.XPATH, "//h3[text()='Academic Details']/following::p[2]").text
            except NoSuchElementException:
                scraped_info["Branch"] = ""
            try:
                scraped_info["Internship"] = driver.find_element(By.XPATH, "//h3[text()='Application Overview']/following::p[1]").text
            except NoSuchElementException:
                scraped_info["Internship"] = ""

            print(f"  > Scraped: {scraped_info.get('Name')}, {scraped_info.get('Email')}")

            # --- Update Status (MODIFIED) ---
            print("  > Updating status to 'Offer Released'...") # MODIFIED
            # 1. Click the status dropdown (which should currently show "Under Review")
            # This selector is now simpler and more robust.
            # It looks for a button where its exact visible text (including children) is "Under Review".
            status_dropdown_trigger = wait.until(
                EC.element_to_be_clickable((By.XPATH, f"//button[normalize-space(.)='{status}']")) # MODIFIED
            )
            status_dropdown_trigger.click()
            
            # 2. Click the "Offer Released" option (MODIFIED)
            shortlisted_option = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//span[normalize-space()='Offer Released']")) # MODIFIED
            )
            human_delay(0.2, 0.7)
            shortlisted_option.click() # MODIFIED
            
            # 3. Click the "Update Status" button
            update_btn = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//button[normalize-space()='Update Status']"))
            )
            human_delay(0.3, 0.9)
            update_btn.click()
            
            # Wait for confirmation or navigation. Let's wait for the "Update" button to disappear (become stale)
            wait.until(EC.staleness_of(update_btn))
            print("  > Status updated successfully.")
            
            # Add final status to our scraped data
            scraped_info["Status"] = "Offer Released" # MODIFIED
            applicants_data.append(scraped_info)

        except Exception as e:
            print(f"  > Row {i} FAILED: {e}")
            # Try to recover by going back, but skip this record
            try:
                driver.back()
                wait.until(EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]")))
            except:
                pass # If back fails, we might be stuck, but loop will try next link
            continue # Move to the next link

        # --- Go back to main applicants table page ---
        try:
            print("  > Navigating back to applicants list.")
            driver.back()
            # Wait for the table/links to be visible again before proceeding
            wait.until(
                EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]"))
            )
            time.sleep(1) # Extra pause to ensure JS has loaded
        except Exception as e:
            print(f"  > FAILED to navigate back: {e}. Attempting to reload applicants page.")
            # As a fallback, try to click the "Applicants" button again
            try:
                 driver.find_element(By.XPATH, "//span[normalize-space()='Applicants']").click()
                 
                 # Re-apply INTERNSHIP filter
                 print("  > Re-applying 'Internship Name' filter...")
                 internship_filter_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//button[contains(., 'Internship Name')]")))
                 internship_filter_btn.click()
                 
                 # ------------------- FIX 2 START -------------------
                 # This text must match the original filter text from line 100
                 internship_option_text = internship
                 # ------------------- FIX 2 END -------------------
                 
                 # Use the more robust XPath and click from FIX 1
                 internship_option_xpath = f"//div[@role='option'][normalize-space(.)='{internship_option_text}']"
                 
                 # Wait for the option to be PRESENT
                 internship_option = wait.until(
                    EC.presence_of_element_located((By.XPATH, internship_option_xpath))
                 )
                 
                 # Scroll to it using JavaScript
                 print("  > Scrolling to internship option...")
                 driver.execute_script("arguments[0].scrollIntoView(true);", internship_option)
                 
                 # Wait for clickable and click
                 wait.until(EC.element_to_be_clickable((By.XPATH, internship_option_xpath)))
                 driver.execute_script("arguments[0].click();", internship_option)
                 time.sleep(3) # Wait for filter to apply

                 # Re-apply STATUS filter (MODIFIED)
                 print("  > Re-applying 'Under Review' filter...") # MODIFIED
                 status_filter_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//button[contains(., 'Application Status')]")))
                 status_filter_btn.click()
                 
                 # ------------------- FIX 5 START -------------------
                 # This must match the original status filter logic
                 status_option_text = status
                 status_option_xpath = f"//div[@role='option'][normalize-space(.)='{status_option_text}']"
                 
                 applied_option = wait.until(EC.element_to_be_clickable((By.XPATH, status_option_xpath))) # MODIFIED XPath
                 
                 print("  > Clicking 'Under Review' option via JavaScript...")
                 driver.execute_script("arguments[0].click();", applied_option) # MODIFIED click
                 # ------------------- FIX 5 END -------------------
                 
                 time.sleep(3)
            except Exception as nav_e:
                print(f"  > CRITICAL: Failed to re-navigate. Stopping loop. {nav_e}")
                break # Break inner loop

    # Save to Excel after each page
    df = pd.DataFrame(applicants_data)
    df.to_excel(OUTPUT_XLSX, index=False)
    print(f"✅ Saved {len(applicants_data)} applicants to {OUTPUT_XLSX}")

    # 3. Go to next page
    print("Checking for 'Next' page...")
    try:
        # Find the 'Next' button
        next_btn = driver.find_element(By.XPATH, "//button[contains(text(),'Next »')]")
        
        # Check if it's disabled (common for last page)
        if next_btn.is_enabled():
            print("Clicking 'Next' page...")
            next_btn.click()
            time.sleep(3)  # wait for page to load
            
            # After click, wait for an element from the *new* page to appear
            # We can wait for the 'Next' button itself to be present again
            wait.until(EC.presence_of_element_located((By.XPATH, "//button[contains(text(),'Next »')]")))
        else:
            print("'Next' button is disabled. Reached last page.")
            break # Exit 'while True' loop

    except NoSuchElementException:
        # No 'Next' button found → probably only one page
        print("No 'Next' button found. Reached last page.")
        break # Exit 'while True' loop
    except Exception as e:
        print(f"Error during pagination: {e}")
        break # Exit 'while True' loop


print(f"Process complete. Total {len(applicants_data)} records saved to {OUTPUT_XLSX}.")
input("Press Enter to close the browser...")  
driver.quit()
