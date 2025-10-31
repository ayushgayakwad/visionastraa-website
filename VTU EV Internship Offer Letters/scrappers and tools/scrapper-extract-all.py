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

# ---------------- CONFIG ----------------
LOGIN_URL = "https://vtu.internyet.in/sign-in"
BASE_URL = "https://vtu.internyet.in"   # used for joining relative hrefs
EMAIL = "admissions@visionastraa.com"
PASSWORD = "VisionAstraa@23"
OUTPUT_XLSX = "applicants.xlsx"

# ---------------- SETUP DRIVER ----------------
chrome_options = Options()
# comment out the two lines below if you want the browser to close automatically
# chrome_options.add_experimental_option("detach", True)
# chrome_options.add_argument("--headless")  # comment in if you want headless

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=chrome_options)
wait = WebDriverWait(driver, 15)

# ---------------- LOGIN ----------------
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
applicants_btn = WebDriverWait(driver, 10).until(
    EC.element_to_be_clickable((By.XPATH, "//span[normalize-space()='Applicants']"))
)
applicants_btn.click()

# Collect only applicant links from Actions column
all_links = WebDriverWait(driver, 15).until(
    EC.presence_of_all_elements_located((By.XPATH, "//table//tr/td[last()]//a"))
)

# Filter only /dashboard/company/view-applicant/*
action_links = [
    link.get_attribute("href")
    for link in all_links
    if "/dashboard/company/view-applicant/" in link.get_attribute("href")
]

print(f"Found {len(action_links)} applicant links.")

applicants_data = []

while True:  # Loop over pages
    # 1. Collect applicant links for current page
    all_links = WebDriverWait(driver, 15).until(
        EC.presence_of_all_elements_located((By.XPATH, "//table//tr/td[last()]//a"))
    )
    action_links = [
        link.get_attribute("href")
        for link in all_links
        if "/dashboard/company/view-applicant/" in link.get_attribute("href")
    ]

    print(f"Found {len(action_links)} applicant links on current page.")

    # 2. Visit each applicant link
    for i, link in enumerate(action_links, start=1):
        try:
            driver.get(link)

            # Wait for "Applicant" heading
            WebDriverWait(driver, 15).until(
                EC.presence_of_element_located((By.XPATH, "//h3[text()='Applicant']"))
            )

            # Extract Name, Email, Phone using following::p[1/2/3]
            try:
                name = driver.find_element(By.XPATH, "//h3[text()='Applicant']/following::p[1]").text
            except:
                name = ""
            try:
                email = driver.find_element(By.XPATH, "//h3[text()='Applicant']/following::p[2]").text
            except:
                email = ""
            try:
                phone = driver.find_element(By.XPATH, "//h3[text()='Applicant']/following::p[3]").text
            except:
                phone = ""

            # Academic Details
            try:
                college = driver.find_element(By.XPATH, "//h3[text()='Academic Details']/following::p[1]").text
            except:
                college = ""
            try:
                branch = driver.find_element(By.XPATH, "//h3[text()='Academic Details']/following::p[2]").text
            except:
                branch = ""

             # Application Overview
            try:
                internship = driver.find_element(By.XPATH, "//h3[text()='Application Overview']/following::p[1]").text
            except:
                internship = ""
            try:
                status = driver.find_element(By.XPATH, "//h3[text()='Application Overview']/following::span[4]").text
            except:
                status = ""

            applicants_data.append({
                "Name": name,
                "Email": email,
                "Phone": phone,
                "College": college,
                "Branch": branch,
                "Internship": internship,
                "Status": status
            })

            print(f"Row {i}: {name}, {phone}, {email}, {college}, {branch}, {internship}, {status}")

        except Exception as e:
            print(f"Row {i} failed: {e}")

        finally:
            # Go back to main applicants table page
            driver.back()  # or driver.get(MAIN_PAGE_URL)
            time.sleep(1)  # give time for table & Next button to appear

    # Save to Excel
    df = pd.DataFrame(applicants_data)
    df.to_excel("applicants.xlsx")
    print(f"✅ Saved {len(applicants_data)} applicants to applicants.xlsx")

    # 3. Go to next page
    try:
        next_btn = driver.find_element(By.XPATH, "//button[contains(text(),'Next »')]")
        next_btn.click()
        time.sleep(2)  # wait for page to load
    except:
        # No button found → last page
        print("Reached last page.")
        break



input("Press Enter to close the browser...")  
driver.quit()