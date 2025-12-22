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
# NOTE: Please ensure the 'status' ID in this URL corresponds to 'Under Review' if you want to filter automatically.
TARGET_URL = "https://vtu.internyet.in/dashboard/company/applicants?status=2"
BASE_URL = "https://vtu.internyet.in"   
EMAIL = "admissions@visionastraa.com"
PASSWORD = "VisionAstraa@23"
OUTPUT_XLSX = "under_review_to_shortlisted.xlsx" 

# Global variable to track login time
last_login_time = 0

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
                # Wait for successful login indicator (Checking for Logout button OR Applicants text as fallback)
                # We use a broad xpath to catch <a>, <button>, or <span> containing "Logout"
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
                     # If URL changed but we didn't see Logout/Applicants, maybe the page is just slow or different?
                     # We'll assume success if URL is NOT sign-in and try to proceed.
                     print("⚠️ URL changed away from sign-in, but success markers not found. Assuming success and proceeding.")
                     last_login_time = time.time()
                     return

        except Exception as e:
            print(f"❌ Error during login attempt: {e}. Retrying in 10 seconds...")
            time.sleep(10)
            continue

def check_system_state(driver, wait):
    """
    Checks for:
    1. Login screen redirection (re-logs in if found).
    2. 'Failed to load data' (retries if found).
    3. 10-minute timeout (logs out and logs in).
    """
    global last_login_time
    
    # 1. Check for Login Screen Redirection
    current_url = driver.current_url
    if "sign-in" in current_url:
        print("⚠️ Redirected to Login Screen unexpectedly. Logging in again...")
        perform_login(driver, wait)
        print("   > Navigating back to target URL...")
        driver.get(TARGET_URL)
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
            # Scroll to top to ensure button is visible
            driver.execute_script("window.scrollTo(0, 0);")
            # Use broad XPath for logout button
            logout_btn = driver.find_element(By.XPATH, "//*[contains(text(), 'Logout')]")
            logout_btn.click()
            print("   > Logged out.")
            time.sleep(2)
        except Exception as e:
            print(f"   > Logout attempt failed (maybe already on login screen?): {e}")
        
        perform_login(driver, wait)
        print("   > Navigating back to target URL...")
        driver.get(TARGET_URL)
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
print(f"Navigating directly to filtered URL: {TARGET_URL}")
driver.get(TARGET_URL)

# Wait for table to load
try:
    wait.until(
        EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]"))
    )
    print("Filtered list loaded successfully.")
    time.sleep(2)
except TimeoutException:
    print("Warning: No applicants found immediately or page took too long. Proceeding to check...")

# ---------------- PROCESS APPLICANTS ----------------
applicants_data = []

while True:  # Loop over pages
    
    # Check system state before processing page
    state = check_system_state(driver, wait)
    if state == "reset":
        continue # Restart loop to re-find elements

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
        
        # Check system state before every applicant
        state = check_system_state(driver, wait)
        if state == "reset":
            # If we reset (re-logged in), we are back at the list. 
            # We must break the inner loop to re-fetch links/resume correctly.
            print("   > State reset triggered. Refreshing list...")
            break 

        try:
            print(f"Processing link {i}/{len(action_links)}: {link}")
            driver.get(link)

            # Check if login screen appeared after navigation
            if check_system_state(driver, wait) == "reset":
                 break # Break inner loop to restart

            wait.until(
                EC.presence_of_element_located((By.XPATH, "//button[normalize-space()='Update Status']"))
            )
            
            # --- Scrape Data ---
            scraped_info = {}
            def get_text_safe(xpath):
                try: return driver.find_element(By.XPATH, xpath).text
                except: return ""

            scraped_info["Name"] = get_text_safe("//h3[text()='Applicant']/following::p[1]")
            scraped_info["Email"] = get_text_safe("//h3[text()='Applicant']/following::p[2]")
            scraped_info["Phone"] = get_text_safe("//h3[text()='Applicant']/following::p[3]")
            
            print(f"  > Scraped: {scraped_info.get('Name')}")

            # --- Update Status ---
            print("  > Updating status to 'Shortlisted'...")
            
            # 1. Click status dropdown (Currently "Under Review")
            status_dropdown_trigger = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//button[contains(., 'Under Review')]"))
            )
            status_dropdown_trigger.click()
            
            # 2. Click "Shortlisted" option
            shortlisted_option = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//span[normalize-space()='Shortlisted']"))
            )
            shortlisted_option.click()
            
            # 3. Click "Update Status" button
            update_btn = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//button[normalize-space()='Update Status']"))
            )
            update_btn.click()
            
            wait.until(EC.staleness_of(update_btn))
            print("  > Status updated successfully.")
            
            scraped_info["Status"] = "Shortlisted"
            applicants_data.append(scraped_info)

        except Exception as e:
            print(f"  > Row {i} FAILED: {e}")
            # Check if failure was due to logout
            if check_system_state(driver, wait) == "reset":
                break
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
            if check_system_state(driver, wait) == "reset":
                break
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
        # Before clicking next, check state
        if check_system_state(driver, wait) == "reset":
             continue

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