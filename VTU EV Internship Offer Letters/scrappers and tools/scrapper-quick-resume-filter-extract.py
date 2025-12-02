import time
import pandas as pd
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
EMAIL = "admissions@visionastraa.com"
PASSWORD = "VisionAstraa@23"
OUTPUT_XLSX = "offer_released_applicants_table.xlsx"

# This URL should point to the page where the table is visible
START_URL = "https://vtu.internyet.in/dashboard/company/applicants?status=4&start_date=2025-08-01&end_date=2025-11-14"

# ---------------- SETUP DRIVER ----------------
chrome_options = Options()
chrome_options.add_argument("--start-maximized")
# chrome_options.add_argument("--headless") # Uncomment for invisible execution

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
wait = WebDriverWait(driver, 20)

# ---------------- LOGIN ----------------
print("Logging in...")
driver.get(LOGIN_URL)

try:
    # Wait for email field
    wait.until(EC.presence_of_element_located((By.XPATH, "//input[@placeholder='Enter your email address' or @name='email' or @id='email']")))
    
    email_el = driver.find_element(By.XPATH, "//input[@placeholder='Enter your email address' or @name='email' or @id='email']")
    password_el = driver.find_element(By.XPATH, "//input[@type='password' or @placeholder='Enter your password' or @name='password']")

    email_el.clear()
    email_el.send_keys(EMAIL)
    password_el.clear()
    password_el.send_keys(PASSWORD)

    # Click sign in
    try:
        driver.find_element(By.XPATH, "//button[contains(., 'Sign in') or contains(., 'Sign In') or contains(., 'Login') or contains(., 'Log in')]").click()
    except:
        password_el.submit()
        
    time.sleep(5)  # Wait for login to complete

except Exception as e:
    print(f"Login failed: {e}")
    driver.quit()
    exit()

# ---------------- NAVIGATE ----------------
print(f"Navigating to: {START_URL}")
driver.get(START_URL)

try:
    # Wait for the table to load (looking for the edit link as a sign the table is there)
    wait.until(EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]")))
    print("Page loaded successfully.")
except TimeoutException:
    print("Table not found or page took too long to load.")
    driver.quit()
    exit()

# ---------------- PROCESS TABLE ----------------
applicants_data = []

while True:  # Loop over pages
    print("Scraping current page...")
    
    try:
        # Wait for table rows to be present
        wait.until(EC.presence_of_all_elements_located((By.XPATH, "//tr[.//a[contains(@href, 'dashboard/company/edit-applicant/')]]")))
        
        # Find all rows that contain the edit applicant link
        # We assume the table structure is <tr> containing several <td>s
        rows = driver.find_elements(By.XPATH, "//tr[.//a[contains(@href, 'dashboard/company/edit-applicant/')]]")
        
        print(f"Found {len(rows)} rows on this page.")
        
        for row in rows:
            try:
                # Get all columns (td) for this row
                cols = row.find_elements(By.TAG_NAME, "td")
                
                # Check if we have enough columns (User mentioned 6 before the button)
                # If the button is in the 7th column, length should be >= 7
                if len(cols) >= 6:
                    scraped_info = {
                        "ID": cols[0].text.strip(),
                        "Name": cols[1].text.strip(),
                        "Email": cols[2].text.strip(),
                        "Internship Name": cols[3].text.strip(),
                        "Date": cols[4].text.strip(),
                        "Status": cols[5].text.strip(),
                        # "Link": cols[6].find_element(By.TAG_NAME, "a").get_attribute("href") # Optional: Capture the link
                    }
                    applicants_data.append(scraped_info)
                    print(f"  > Scraped: {scraped_info['Name']} - {scraped_info['Email']}")
                else:
                    print(f"  > Skipped row (not enough columns): {row.text[:30]}...")
                    
            except Exception as row_e:
                print(f"  > Error scraping row: {row_e}")
                continue

    except Exception as e:
        print(f"Error reading table on this page: {e}")
        break

    # Save progress after each page
    df = pd.DataFrame(applicants_data)
    df.to_excel(OUTPUT_XLSX, index=False)
    print(f"✅ Saved total {len(applicants_data)} applicants so far.")

    # ---------------- PAGINATION ----------------
    print("Checking for 'Next' page...")
    try:
        # Look for the Next button
        next_btn = driver.find_element(By.XPATH, "//button[contains(text(),'Next »')]")
        
        if next_btn.is_enabled():
            print("Clicking 'Next'...")
            # Use JS click to avoid interception
            driver.execute_script("arguments[0].click();", next_btn)
            
            time.sleep(3) # Wait for page transition
            
            # Stale element check / Wait for new rows to load
            # We wait for the table to refresh. A simple way is to wait for rows again.
            try:
                 wait.until(EC.staleness_of(rows[0])) # Wait for old rows to disappear (optional but good)
            except:
                pass
                
            wait.until(EC.presence_of_element_located((By.XPATH, "//tr[.//a[contains(@href, 'dashboard/company/edit-applicant/')]]")))
        else:
            print("'Next' button found but disabled. Reached last page.")
            break 
            
    except NoSuchElementException:
        print("No 'Next' button found. Reached last page.")
        break
    except Exception as e:
        print(f"Pagination error: {e}")
        break

print(f"Process complete. Total {len(applicants_data)} records saved to {OUTPUT_XLSX}.")
input("Press Enter to close the browser...")  
driver.quit()