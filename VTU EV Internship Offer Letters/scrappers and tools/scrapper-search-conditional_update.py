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
import os
import re

# ---------------- CONFIG ----------------
LOGIN_URL = "https://vtu.internyet.in/sign-in"
BASE_URL = "https://vtu.internyet.in"   # used for joining relative hrefs
EMAIL = "admissions@visionastraa.com"
PASSWORD = "VisionAstraa@23"
# Changed output file name to reflect the new action
OUTPUT_XLSX = "applied_to_shortlisted_applicants.xlsx" 
CSV_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "duplicate_offer_released.csv")

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
    try:
        password_el.submit()
    except Exception:
        pass

# ---------------- NEW: read CSV and iterate emails ----------------
def _norm(s):
    if not s:
        return ""
    return re.sub(r"\s+", " ", s.strip()).lower()

# Lists and mappings from the user's requirements (normalized)
COLLEGE_LIST = list(map(_norm, [
    "Angadi Institute of Technology and Management, Belagavi",
    "GIT, BELGAUM",
    "J C E R, Belgaum",
    "Jain College of Engg, Belgaum",
    "KLE Dr.MSS CET, Belgaum",
    "MMEC, Belgaum",
    "Shaikh College of Engg,Bgm",
    "VTU, PG Centre, Belagavi",
    "KLS GOGTE INSTITUTE OF TECHNOLOGY BELGAUM",
    "ANUVARTIK MIRJI BHARATESH INSTITUTE OF TECHNOLOGY, BELAGAVI",
    "Dept. of Electronics & Communication Engineering, VTU, Belagavi",
]))

ROLE_COLLEGE_ALLOWED = list(map(_norm, [
    "AI/ML FOR EV ( Data Science , Cybersecurity , Machine Learning , Data Analytics , Full Stack Development , Artificial intelligence )",
    "EMBEDDED SYSTEMS FOR EV ( Microcontrollers , Mechatronics , IOT , ADAS )",
    "DESIGN & DEVELOPMENT OF EV ( Mechanical , Mechatronics , Automobile )"
]))

BRANCH_AI = list(map(_norm, [
    "Computer Science & Engineering", "Computer Engineering", "Artificial Intelligence & Data Science",
    "Artificial Intelligence and Machine Learning", "Computer & Communication Engineering", "Computer Science & Business System",
    "Computer Science & Design", "Computer Science & Engineering (IoT)", "CSE(Artificial Intelligence & Machine Learning)",
    "CSE(Artificial Intelligence)", "CSE(Cyber Security)", "CSE(Data Science)",
    "CSE(IoT & Cyber Security including Block Chain Technology)", "Data Science", "Information Science & Engineering",
    "Robotics and Artificial Intelligence", "Robotics & Automation", "Robotics & Automation(University Department)",
    "Smart Agritech"
]))

BRANCH_EMBED = list(map(_norm, [
    "Electronics & Communication Engg", "Biomedical Engineering", "Electrical & Electronics Engineering",
    "Electronics & Instrumentation Engineering", "Electronics & Telecommunication Engg", "Medical Electronics Engineering",
    "Electronics Engg (VLSI Design and Technology)", "Energy Engineering"
]))

BRANCH_MECH = list(map(_norm, [
    "Civil engineering", "Ceramics and Cement Technology", "Construction Technology & Management", "Environmental Engineering",
    "Mining Engineering", "Biotechnology", "Industrial IoT", "Aeronautical Engineering", "Aerospace Engineering",
    "Agreecultural Engineering", "Automobile Engineering", "Chemical Engineering", "Industrial & Production Engineering",
    "Industrial Engineering & Management", "Manufacturing Science & Engineering", "Marine Engineering",
    "Mechanical & Smart Manufacturing", "Mechanical & Smart Manufacturing (University Department)", "Mechanical Engineering",
    "Mechatronics", "Petrochem Engineering", "Silk Technology", "Textile Technology"
]))

ROLE_AI_ALLOWED = list(map(_norm, [
    "AI/ML FOR ELECTRIC VEHICLE ( Data Science , Cyber Security , Machine Learning , Data Analytics , Full Stack Development , Artificial intelligence )",
    "AI/ML FOR EV ( Data Science , Cybersecurity , Machine Learning , Data Analytics , Full Stack Development , Artificial intelligence )",
]))

ROLE_EMBED_ALLOWED = list(map(_norm, [
    "EMBEDDED SYSTEMS FOR ELECTRIC VEHICLE ( Microcontrollers , IOT , Mechatronics , ADAS )",
    "EMBEDDED SYSTEMS FOR EV ( Microcontrollers , Mechatronics , IOT , ADAS )",
]))

ROLE_MECH_ALLOWED = list(map(_norm, [
    "DESIGN & DEVELOPMENT OF ELECTRIC VEHICLE ( Mechanical , Mechatronics , Automobile )",
    "DESIGN & DEVELOPMENT OF EV ( Mechanical , Mechatronics , Automobile )",
]))


df_csv = pd.read_csv(CSV_FILE)
# Work with unique emails that have status 'Offer Released'
targets = df_csv[df_csv['status'].str.lower() == 'offer released']['email'].dropna().unique()

print(f"Found {len(targets)} unique emails in CSV with status 'Offer Released'.")

applicants_data = []

for email in targets:
    try:
        print(f"\n=== Processing email: {email} ===")
        # Ensure we're on Applicants page
        try:
            applicants_btn = wait.until(
                EC.element_to_be_clickable((By.XPATH, "//span[normalize-space()='Applicants']"))
            )
            applicants_btn.click()
        except Exception:
            # maybe already there
            pass

        # Wait for search input and enter email
        try:
            search_input = wait.until(EC.presence_of_element_located((By.XPATH, "//input[@placeholder='Search by student name, email, mobile number...']")))
            # clear and send
            search_input.clear()
            search_input.send_keys(email)
        except TimeoutException:
            print("Search input not found, skipping this email.")
            continue

        # Click Search button
        try:
            search_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//button[.//p[normalize-space()='Search'] or normalize-space()='Search']")))
            search_btn.click()
            time.sleep(1)
        except Exception:
            print("Search button not found/clickable. Continuing...")

        # Apply 'Offer Released' filter (similar to earlier)
        try:
            print("Applying 'Offer Released' filter...")
            # Prefer the exact inner span for the Application Status trigger and click its ancestor button
            try:
                status_span = wait.until(EC.presence_of_element_located((By.XPATH,
                    "//span[@data-slot='select-value' and normalize-space(.)='Application Status']"
                )))
                driver.execute_script("arguments[0].scrollIntoView({block:'center'});", status_span)
                # click the parent/closest button to open the select
                driver.execute_script("arguments[0].closest('button').click();", status_span)
            except TimeoutException:
                # fallback: robustly find the Application Status select trigger (several possible attributes)
                status_filter_btn = wait.until(EC.element_to_be_clickable((By.XPATH,
                    "//button[@id='status' or @data-slot='select-trigger' or .//span[normalize-space()='Application Status']]"
                )))
                # Ensure visible and click via JS to avoid overlay/click interception issues
                driver.execute_script("arguments[0].scrollIntoView({block:'center'});", status_filter_btn)
                driver.execute_script("arguments[0].click();", status_filter_btn)

            # Wait for and click the 'Offer Released' option — try role/option/button/span variants
            offer_option = wait.until(EC.element_to_be_clickable((By.XPATH,
                "//div[@role='option' and normalize-space(.)='Offer Released'] | //button[normalize-space(.)='Offer Released'] | //span[normalize-space(.)='Offer Released'] | //li[normalize-space(.)='Offer Released'] | //a[normalize-space(.)='Offer Released']"
            )))
            driver.execute_script("arguments[0].scrollIntoView({block:'center'});", offer_option)
            driver.execute_script("arguments[0].click();", offer_option)
            time.sleep(2)
        except TimeoutException:
            print("Could not apply 'Offer Released' filter. Continuing without it.")
        except Exception as e:
            print(f"Filter error: {e}")

        # Collect edit links for this search result
        try:
            wait.until(EC.presence_of_element_located((By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]")))
            all_links = driver.find_elements(By.XPATH, "//a[contains(@href, 'dashboard/company/edit-applicant/')]")
            action_links = [link.get_attribute('href') for link in all_links if link.get_attribute('href')]
            action_links = list(dict.fromkeys(action_links))
        except TimeoutException:
            print("No matching applicants found for this email.")
            continue

        print(f"Found {len(action_links)} matching applicants for {email}.")

        for link in action_links:
            try:
                print(f"Opening {link}")
                driver.get(link)
                wait.until(EC.presence_of_element_located((By.XPATH, "//button[normalize-space()='Update Status']")))

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

                # Try to find current status button (the button before Update Status)
                try:
                    status_button = driver.find_element(By.XPATH, "//button[normalize-space()='Update Status']/preceding::button[1]")
                    current_status = _norm(status_button.text)
                except Exception:
                    status_button = None
                    current_status = ""

                print(f"  > Scraped: {scraped_info.get('Name')}, {scraped_info.get('Email')}, role={scraped_info.get('Internship')}")

                # Decision: check conditions
                college_norm = _norm(scraped_info.get('College',''))
                branch_norm = _norm(scraped_info.get('Branch',''))
                internship_norm = _norm(scraped_info.get('Internship',''))

                needs_under_review = False

                # Condition 1: if college in special list, role must be in ROLE_COLLEGE_ALLOWED
                # Condition 1: mutual check between college list and role list
                if college_norm and college_norm in COLLEGE_LIST:
                    if internship_norm not in ROLE_COLLEGE_ALLOWED:
                        print("    - College condition failed: role not allowed for this college")
                        needs_under_review = True
                # If role is one of the ROLE_COLLEGE_ALLOWED, ensure college is in the COLLEGE_LIST
                if internship_norm and internship_norm in ROLE_COLLEGE_ALLOWED:
                    if not college_norm or college_norm not in COLLEGE_LIST:
                        print("    - Role condition failed: this role requires the college to be in the special college list")
                        needs_under_review = True

                # Condition 2: branch based checks
                if branch_norm:
                    if branch_norm in BRANCH_AI:
                        if internship_norm not in ROLE_AI_ALLOWED:
                            print("    - Branch(AI) condition failed: role not allowed for this branch")
                            needs_under_review = True
                    elif branch_norm in BRANCH_EMBED:
                        if internship_norm not in ROLE_EMBED_ALLOWED:
                            print("    - Branch(Embedded) condition failed: role not allowed for this branch")
                            needs_under_review = True
                    elif branch_norm in BRANCH_MECH:
                        if internship_norm not in ROLE_MECH_ALLOWED:
                            print("    - Branch(Mech) condition failed: role not allowed for this branch")
                            needs_under_review = True

                # If either condition failed, update status to 'Under Review'
                if needs_under_review:
                    print("  > Updating status to 'Under Review'...")
                    try:
                        if status_button:
                            status_button.click()
                            # click 'Under Review' option
                            under_option = wait.until(EC.element_to_be_clickable((By.XPATH, "//span[normalize-space()='Under Review']")))
                            under_option.click()
                        else:
                            # fallback: try to click any status button text and then select
                            generic_status = wait.until(EC.element_to_be_clickable((By.XPATH, "(//button[contains(@class,'rounded')])[1]")))
                            generic_status.click()
                            under_option = wait.until(EC.element_to_be_clickable((By.XPATH, "//span[normalize-space()='Under Review']")))
                            under_option.click()

                        update_btn = wait.until(EC.element_to_be_clickable((By.XPATH, "//button[normalize-space()='Update Status']")))
                        update_btn.click()
                        wait.until(EC.staleness_of(update_btn))
                        scraped_info['Status'] = 'Under Review'
                        print("  > Status set to 'Under Review'.")
                    except Exception as e:
                        print(f"  > Failed to update status: {e}")
                        scraped_info['Status'] = current_status or ''
                else:
                    scraped_info['Status'] = scraped_info.get('Status', current_status)
                    print("  > Conditions satisfied — leaving status as is.")

                applicants_data.append(scraped_info)

            except Exception as e:
                print(f"  > Failed processing applicant link {link}: {e}")
                continue

        # After processing this email, clear search input for safety
        try:
            search_input = driver.find_element(By.XPATH, "//input[@placeholder='Search by student name, email, mobile number...']")
            search_input.clear()
        except Exception:
            pass

        # Save progress after each email
        df = pd.DataFrame(applicants_data)
        df.to_excel(OUTPUT_XLSX, index=False)
        print(f"✅ Saved {len(applicants_data)} applicants to {OUTPUT_XLSX}")

    except Exception as e:
        print(f"Unhandled error while processing email {email}: {e}")
        continue

print(f"Process complete. Total {len(applicants_data)} records saved to {OUTPUT_XLSX}.")
input("Press Enter to close the browser...")
driver.quit()
