import random
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
# This is the direct link with filters already applied
TARGET_URL = "https://vtu.internyet.in/dashboard/company/applicants?status=4&start_date=2025-08-01&end_date=2025-10-15"
BASE_URL = "https://vtu.internyet.in"   
EMAIL = "admissions@visionastraa.com"
PASSWORD = "VisionAstraa@23"
OUTPUT_XLSX = "applied_to_shortlisted_applicants.xlsx" 

def human_delay(min_s=0.5, max_s=1.5):
    try:
        time.sleep(random.uniform(min_s, max_s))
    except Exception:
        pass

# ---------------- SETUP DRIVER ----------------
chrome_options = Options()
chrome_options.add_argument("--start-maximized")
# chrome_options.add_experimental_option("detach", True)
# chrome_options.add_argument("--headless")

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
wait = WebDriverWait(driver, 20)
short_wait = WebDriverWait(driver, 5)

# ---------------- LOGIN ----------------
print("Logging in...")
driver.get(LOGIN_URL)
# wait for email field
wait.until(EC.presence_of_element_located((By.XPATH, "//input[@placeholder='Enter your email address' or @name='email' or @id='email']")))

email_el = driver.find_element(By.XPATH, "//input[@placeholder='Enter your email address' or @name='email' or @id='email']")
password_el = driver.find_element(By.XPATH, "//input[@type='password' or @placeholder='Enter your password' or @name='password']")

email_el.clear()
email_el.send_keys(EMAIL)
password_el.clear()
password_el.send_keys(PASSWORD)

try:
    driver.find_element(By.XPATH, "//button[contains(., 'Sign in') or contains(., 'Sign In') or contains(., 'Login') or contains(., 'Log in')]").click()
except:
    password_el.submit()

# Wait for login to complete (looking for Applicants button as proof of login)
print("Login submitted, waiting for dashboard...")
wait.until(
    EC.presence_of_element_located((By.XPATH, "//span[normalize-space()='Applicants']"))
)

# ---------------- DIRECT NAVIGATION ----------------
print(f"Navigating directly to filtered URL: {TARGET_URL}")
driver.get(TARGET_URL)

# Wait for table to load
try:
    # Wait for the table rows or edit links to appear to confirm the page loaded
    wait.until(
        EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]"))
    )
    print("Filtered list loaded successfully.")
    time.sleep(2) # brief pause to let animations settle
except TimeoutException:
    print("Warning: No applicants found immediately or page took too long. Proceeding to check...")

# ---------------- PROCESS APPLICANTS ----------------
applicants_data = []

while True:  # Loop over pages
    
    # 1. Collect applicant EDIT links for current page
    print("Finding edit links on the current page...")
    try:
        # Wait for at least one edit link to be present
        wait.until(
            EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]"))
        )
        
        all_links = driver.find_elements(By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]")
        
        action_links = [link.get_attribute("href") for link in all_links if link.get_attribute("href")]
        action_links = list(dict.fromkeys(action_links)) 

    except TimeoutException:
        print("No edit links found on this page. Assuming end of list.")
        break 
    except Exception as e:
        print(f"Error collecting links: {e}")
        break

    print(f"Found {len(action_links)} applicant edit links on current page.")

    # 2. Visit each applicant link
    for i, link in enumerate(action_links, start=1):
        try:
            print(f"Processing link {i}/{len(action_links)}: {link}")
            driver.get(link)

            wait.until(
                EC.presence_of_element_located((By.XPATH, "//button[normalize-space()='Update Status']"))
            )
            
            # --- Scrape Data ---
            scraped_info = {}
            # Helper to safely get text
            def get_text_safe(xpath):
                try: return driver.find_element(By.XPATH, xpath).text
                except: return ""

            scraped_info["Name"] = get_text_safe("//h3[text()='Applicant']/following::p[1]")
            scraped_info["Email"] = get_text_safe("//h3[text()='Applicant']/following::p[2]")
            scraped_info["Phone"] = get_text_safe("//h3[text()='Applicant']/following::p[3]")
            scraped_info["College"] = get_text_safe("//h3[text()='Academic Details']/following::p[1]")
            scraped_info["Branch"] = get_text_safe("//h3[text()='Academic Details']/following::p[2]")
            scraped_info["Internship"] = get_text_safe("//h3[text()='Application Overview']/following::p[1]")

            print(f"  > Scraped: {scraped_info.get('Name')}, {scraped_info.get('Email')}")

            # --- Update Status ---
            print("  > Updating status to 'Shortlisted'...")
            
            # Click status dropdown (Currently "Applied")
            status_dropdown_trigger = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//button[normalize-space(.)='Offer Released']"))
            )
            status_dropdown_trigger.click()
            
            # Click "Shortlisted"
            shortlisted_option = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//span[normalize-space()='Under Review']"))
            )
            human_delay(0.5, 1.0)
            shortlisted_option.click()
            
            # Click "Update Status" button
            update_btn = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//button[normalize-space()='Update Status']"))
            )
            human_delay(0.5, 1.0)
            update_btn.click()
            
            wait.until(EC.staleness_of(update_btn))
            print("  > Status updated successfully.")
            
            scraped_info["Status"] = "Under Review"
            applicants_data.append(scraped_info)

        except Exception as e:
            print(f"  > Row {i} FAILED: {e}")
            try:
                driver.back()
                wait.until(EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]")))
            except:
                pass 
            continue 

        # --- Go back to main applicants table page ---
        try:
            print("  > Navigating back to applicants list.")
            driver.back()
            wait.until(
                EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]"))
            )
            time.sleep(1) 
        except Exception as e:
            print(f"  > FAILED to navigate back: {e}. Reloading via Direct URL.")
            # Fallback: Just load the target URL again
            try:
                 driver.get(TARGET_URL)
                 time.sleep(3)
            except Exception as nav_e:
                print(f"  > CRITICAL: Failed to re-navigate. Stopping loop. {nav_e}")
                break 

    # Save to Excel
    df = pd.DataFrame(applicants_data)
    df.to_excel(OUTPUT_XLSX, index=False)
    print(f"✅ Saved {len(applicants_data)} applicants to {OUTPUT_XLSX}")

    # 3. Go to next page
    print("Checking for 'Next' page...")
    try:
        next_btn = driver.find_element(By.XPATH, "//button[contains(text(),'Next »')]")
        
        if next_btn.is_enabled():
            print("Clicking 'Next' page...")
            next_btn.click()
            time.sleep(3)
            wait.until(EC.presence_of_element_located((By.XPATH, "//button[contains(text(),'Next »')]")))
        else:
            print("'Next' button is disabled. Reached last page.")
            break 

    except NoSuchElementException:
        print("No 'Next' button found. Reached last page.")
        break
    except Exception as e:
        print(f"Error during pagination: {e}")
        break

print(f"Process complete. Total {len(applicants_data)} records saved to {OUTPUT_XLSX}.")
input("Press Enter to close the browser...")  
driver.quit()