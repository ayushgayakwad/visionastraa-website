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

# NOTE: Set this to the URL of the LAST page you want to start from. 
# Ensure it includes the 'page=' parameter if possible, e.g., '...&page=10'
TARGET_URL = "https://vtu.internyet.in/dashboard/company/applicants?page=1625" 
BASE_URL = "https://vtu.internyet.in"   
EMAIL = "admissions@visionastraa.com"
PASSWORD = "VisionAstraa@23"
OUTPUT_XLSX = "scraped_applicants_data.xlsx" 

# Global variables
last_login_time = 0
current_scraping_url = TARGET_URL # Tracks the current page URL for resume capability
processed_links = set() # Tracks processed applicants to avoid duplicates on resume

def human_delay(min_s=0.5, max_s=1.5):
    try:
        time.sleep(random.uniform(min_s, max_s))
    except Exception:
        pass

def perform_login(driver, wait):
    """Handles the login process with retry logic."""
    global last_login_time
    
    while True:
        print("🔑 Performing Login...")
        try:
            driver.get(LOGIN_URL)
            time.sleep(2) # Wait for potential redirect

            # CHECK: Are we already logged in? (Redirected to dashboard immediately)
            try:
                WebDriverWait(driver, 3).until(
                    EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Logout')] | //span[normalize-space()='Applicants']"))
                )
                print("   > Already logged in (redirected to dashboard).")
                last_login_time = time.time()
                return
            except TimeoutException:
                pass # Not logged in, proceed to enter credentials

            # Wait for email field
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

            # Wait for dashboard
            print("   > Credentials submitted, waiting for dashboard...")
            try:
                # Wait for successful login indicator
                WebDriverWait(driver, 15).until(
                    EC.presence_of_element_located((By.XPATH, "//*[contains(text(), 'Logout')] | //span[normalize-space()='Applicants']"))
                )
                last_login_time = time.time() # Reset timer
                print("✅ Login successful.")
                return
            except TimeoutException:
                print("   > Login timeout. Checking state...")
                
                # Check if still on login page by URL
                if "sign-in" in driver.current_url:
                     print("⚠️ Login Failed: Still on login URL. Retrying in 10 seconds...")
                     time.sleep(10)
                     continue # Retry loop
                else:
                     print("⚠️ URL changed away from sign-in, but success markers not found. Assuming success and proceeding.")
                     last_login_time = time.time()
                     return

        except Exception as e:
            print(f"❌ Error during login attempt: {e}. Retrying in 10 seconds...")
            time.sleep(10)
            continue

def check_system_state(driver, wait):
    """
    Checks for logout/errors and restores session to the 'current_scraping_url'.
    """
    global last_login_time, current_scraping_url
    
    # 1. Check for Login Screen Redirection
    current_url = driver.current_url
    if "sign-in" in current_url:
        print("⚠️ Redirected to Login Screen unexpectedly. Logging in again...")
        perform_login(driver, wait)
        print(f"   > Resuming: Navigating back to {current_scraping_url}")
        driver.get(current_scraping_url)
        time.sleep(3)
        return "reset" # Signal to caller to reset loop if needed

    # 2. Check for "Failed to load data"
    try:
        failed_msgs = driver.find_elements(By.XPATH, "//*[contains(text(), 'Failed to load data')]")
        if failed_msgs:
            print("⚠️ Detected 'Failed to load data'.")
            retry_links = driver.find_elements(By.XPATH, "//a[contains(text(), 'Retry') or contains(., 'Retry')] | //button[contains(text(), 'Retry')]")
            if retry_links:
                print("   > Waiting 10 seconds before retrying...")
                time.sleep(10)
                print("   > Clicking 'Retry'...")
                retry_links[0].click()
                time.sleep(5) # Wait for reload
                return "retried"
    except Exception:
        pass

    # 3. Check 10-Minute Logout Timer
    if (time.time() - last_login_time) > 600: # 600 seconds = 10 minutes
        print("⏰ 10 minutes elapsed. Initiating mandatory logout/login cycle.")
        try:
            driver.execute_script("window.scrollTo(0, 0);")
            logout_btn = driver.find_element(By.XPATH, "//*[contains(text(), 'Logout')]")
            logout_btn.click()
            print("   > Logged out.")
            time.sleep(2)
        except Exception as e:
            print(f"   > Logout attempt failed (maybe already on login screen?): {e}")
        
        perform_login(driver, wait)
        print(f"   > Resuming: Navigating back to {current_scraping_url}")
        driver.get(current_scraping_url)
        time.sleep(3)
        return "reset"

    return "ok"

# ---------------- SETUP DRIVER ----------------
chrome_options = Options()
chrome_options.add_argument("--start-maximized")

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
wait = WebDriverWait(driver, 20)
short_wait = WebDriverWait(driver, 5)

# ---------------- INITIAL LOGIN ----------------
perform_login(driver, wait)

# ---------------- DIRECT NAVIGATION ----------------
print(f"Navigating directly to starting URL: {TARGET_URL}")
driver.get(TARGET_URL)
current_scraping_url = TARGET_URL # Initialize tracker

# Wait for table to load
try:
    wait.until(
        EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/view-applicant/')]"))
    )
    print("List loaded successfully.")
    time.sleep(2)
except TimeoutException:
    print("Warning: No applicants found immediately or page took too long. Proceeding to check...")

# ---------------- PROCESS APPLICANTS ----------------
applicants_data = []

while True:  # Loop over pages
    
    # Update current URL tracker if we are on the list page
    if "dashboard/company/applicants" in driver.current_url:
        current_scraping_url = driver.current_url
        print(f"📌 Tracking current page: {current_scraping_url}")

    # Check system state before processing page
    state = check_system_state(driver, wait)
    if state == "reset":
        continue # Restart loop to re-find elements

    # 1. Collect applicant EDIT links for current page
    print("Finding applicant links on the current page...")
    try:
        # Wait for at least one edit link to be present
        wait.until(
            EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/view-applicant/')]"))
        )
        
        all_links = driver.find_elements(By.XPATH, "//a[contains(@href, 'dashboard/company/view-applicant/')]")
        action_links = [link.get_attribute("href") for link in all_links if link.get_attribute("href")]
        action_links = list(dict.fromkeys(action_links)) 

    except TimeoutException:
        print("No edit links found on this page. Assuming end of list or empty page.")
        # If empty, we might try to go to previous page anyway? 
        action_links = [] 
    except Exception as e:
        print(f"Error collecting links: {e}")
        break

    print(f"Found {len(action_links)} applicant links on current page.")

    # 2. Visit each applicant link
    for i, link in enumerate(action_links, start=1):
        
        # SKIP if already processed (since we don't change status, they stay on list)
        if link in processed_links:
            continue

        # Check system state before every applicant
        state = check_system_state(driver, wait)
        if state == "reset":
            print("   > State reset triggered. Refreshing list...")
            break 

        try:
            print(f"Processing link {i}/{len(action_links)}: {link}")
            driver.get(link)

            # Check if login screen appeared after navigation
            if check_system_state(driver, wait) == "reset":
                 break # Break inner loop to restart

            # Wait for Name or Header
            wait.until(
                EC.presence_of_element_located((By.XPATH, "//h3[contains(text(), 'Applicant')]"))
            )
            
            # --- Scrape Data ---
            scraped_info = {}
            
            def get_value_by_label(label):
                """Finds the element with exact text 'label' and gets the next non-empty element's text."""
                try:
                    # Strategy A: Find element with exact text, get immediate following <p> or sibling
                    xpath_a = f"//*[normalize-space()='{label}']/following::p[1]"
                    return driver.find_element(By.XPATH, xpath_a).text.strip()
                except:
                    pass
                
                try:
                    # Strategy B: Generic following element (could be span, div, etc) that is not empty
                    xpath_b = f"//*[normalize-space()='{label}']/following::*[string-length(normalize-space(text())) > 0][1]"
                    return driver.find_element(By.XPATH, xpath_b).text.strip()
                except:
                    return ""

            # --- Extract Fields based on user reference ---
            
            # 1. Message
            try:
                # Assuming "Message" is a header or label followed by the text
                scraped_info["Message"] = driver.find_element(By.XPATH, "//h3[contains(text(),'Message')]/following::p[1]").text.strip()
            except:
                scraped_info["Message"] = get_value_by_label("Message")

            # 2. Application Overview
            scraped_info["Internship"] = get_value_by_label("Internship")
            scraped_info["Applied On"] = get_value_by_label("Applied On")

            # 3. Status
            scraped_info["Status"] = get_value_by_label("Status")

            # 4. Applicant Details
            scraped_info["Full Name"] = get_value_by_label("Full Name")
            scraped_info["Email"] = get_value_by_label("Email")
            scraped_info["Phone Number"] = get_value_by_label("Phone Number")
            scraped_info["Date of Birth"] = get_value_by_label("Date of Birth")
            scraped_info["Gender"] = get_value_by_label("Gender")
            
            # 5. Academic Details
            scraped_info["College"] = get_value_by_label("College")
            scraped_info["Department / Branch"] = get_value_by_label("Department / Branch")
            scraped_info["Semester"] = get_value_by_label("Semester")
            
            # 6. Location
            scraped_info["Address"] = get_value_by_label("Address")
            scraped_info["Country"] = get_value_by_label("Country")
            scraped_info["State"] = get_value_by_label("State")
            scraped_info["City"] = get_value_by_label("City")
            
            print(f"  > Scraped: {scraped_info.get('Full Name')}")
            
            # --- NO STATUS UPDATE ---
            
            applicants_data.append(scraped_info)
            processed_links.add(link) # Mark as done

        except Exception as e:
            print(f"  > Row {i} FAILED: {e}")
            if check_system_state(driver, wait) == "reset":
                break
            try:
                driver.back()
                wait.until(EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/view-applicant/')]")))
            except:
                pass 
            continue 

        # --- Go back to main applicants table page ---
        try:
            print("  > Navigating back to applicants list.")
            driver.back()
            wait.until(
                EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/view-applicant/')]"))
            )
        except Exception as e:
            print(f"  > FAILED to navigate back: {e}. Reloading via Current URL.")
            if check_system_state(driver, wait) == "reset":
                break
            try:
                 driver.get(current_scraping_url)
                 time.sleep(3)
            except Exception as nav_e:
                print(f"  > CRITICAL: Failed to re-navigate. Stopping loop. {nav_e}")
                break 

    # Save to Excel
    if applicants_data:
        df = pd.DataFrame(applicants_data)
        df.to_excel(OUTPUT_XLSX, index=False)
        print(f"✅ Saved {len(applicants_data)} applicants to {OUTPUT_XLSX}")

    # 3. Go to PREVIOUS page (Reverse Pagination)
    print("Checking for 'Previous' page button...")
    try:
        # Before clicking previous, check state
        if check_system_state(driver, wait) == "reset":
             continue

        # Look for "Previous" or "«" symbol usually associated with previous
        prev_btn_candidates = driver.find_elements(By.XPATH, "//button[contains(text(),'Previous') or contains(text(), '«')]")
        
        clicked_prev = False
        for btn in prev_btn_candidates:
            if btn.is_enabled() and btn.is_displayed():
                print("Clicking 'Previous' page...")
                btn.click()
                time.sleep(3)
                wait.until(EC.presence_of_element_located((By.XPATH, "//table"))) # Wait for table reload
                
                # Update tracker after successful navigation
                current_scraping_url = driver.current_url
                print(f"   > Moved to: {current_scraping_url}")
                
                clicked_prev = True
                break
        
        if not clicked_prev:
            print("No enabled 'Previous' button found. Reached first page or start of list.")
            break 

    except NoSuchElementException:
        print("No 'Previous' button found. Reached start.")
        break
    except Exception as e:
        print(f"Error during pagination: {e}")
        break

print(f"Process complete. Total {len(applicants_data)} records saved to {OUTPUT_XLSX}.")
input("Press Enter to close the browser...")  
driver.quit()