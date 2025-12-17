import pandas as pd
import os
import smtplib
import ssl
import sys
import math
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

# ---------------- CONFIG ----------------
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/pending_payments.csv'
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = '[Action Required] Complete Internship Acceptance Fee Payment | VisionAstraa EV Academy'

# --- HARDCODED CREDENTIALS (UPDATE THESE) ---
# Unified credentials for single-batch processing
SENDER_EMAIL = "careers@visionastraa.in"
SENDER_PASSWORD = "Z1SIOO0A9b~"

def send_pending_payments_email(sender_email, sender_password, name, to_email, role):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>

        This is a gentle reminder regarding your <strong>internship offer for the {role} role</strong>, which was released on the VTU Portal</strong> along with the official offer letter from <strong>VisionAstraa EV Academy</strong>.
        <br><br>
        
        Our records indicate that while you have <strong>successfully accepted the internship offer on the VTU Portal</strong>, the <strong>Internship Acceptance Fee payment is still pending</strong>.
        <br><br>

        <strong><u>Kindly complete the internship acceptance fee payment on the VTU Portal to finalize your internship confirmation.</u></strong>
        <br><br>

        <strong>Please note that the acceptance process will be considered complete only after the Internship Acceptance Fee is paid on the VTU Portal.</strong>
        <br><br>
 
        <strong>Action Required on VTU Portal:</strong>
        <ul>
            <li>Please log in to your VTU Portal (<a href="https://vtu.internyet.in">https://vtu.internyet.in</a>).</li>
            <li>Complete the process by <u>paying the Internship Acceptance Fee</u> for VisionAstraa EV Academy.</li>
        </ul>
        <br><br>

        If you have already completed the payment or are facing difficulties on the VTU portal, please reach out to VTU Portal's support team for verification or assistance.
        <br><br>

        <strong>For any help or queries, you may contact us on LinkedIn:</strong><br>
        <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
        <strong>OR, call us on: <a href="tel:+918762246518">+91 87622 46518</a></strong>
        <br><br>

        We are here to support you throughout this process.  
        <br><br>

        Thank you, and we request you to complete the internship acceptance payment process at the earliest.
        <br><br>

        Warm regards,<br>
        <strong>VisionAstraa EV Academy</strong>
        """

        msg.attach(MIMEText(body, 'html'))

        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT, context=context) as server:
            server.login(sender_email, sender_password)
            server.sendmail(sender_email, to_email, msg.as_string())

        print(f"Successfully sent email to: {name} ({to_email})")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending email to {name}: {e}")
        return False
    except Exception as e:
        print(f"An unexpected error occurred while sending email to {name}: {e}")
        return False


def main():
    # 1. Credentials Check
    if "replace" in SENDER_EMAIL or "replace" in SENDER_PASSWORD:
        print(f"⚠️ WARNING: You have not updated the hardcoded credentials in the script yet!")

    if not os.path.exists(CSV_FILE_PATH):
        print(f"Error: Input data file not found at '{CSV_FILE_PATH}'")
        return

    try:
        # 2. Load Data
        df = pd.read_csv(CSV_FILE_PATH)
        
        # Structure assumed: Name, Email, Role, Status, EmailSent
        if len(df.columns) >= 5:
            # Map columns by index to ensure correct data retrieval
            df_clean = pd.DataFrame()
            df_clean['Name'] = df.iloc[:, 0]  # 1st col
            df_clean['Email'] = df.iloc[:, 1] # 2nd col
            df_clean['Role'] = df.iloc[:, 2]  # 3rd col

            # Check for EmailSent column (Index 4 / 5th column)
            if len(df.columns) > 4:
                df_clean['EmailSent'] = df.iloc[:, 4]
            else:
                # If column doesn't exist, assume not sent
                df_clean['EmailSent'] = 'FALSE' 
        else:
            print("CSV file format not recognized. Expecting at least 5 columns.")
            return

        # 3. Process All Records (No splitting)
        total_records = len(df_clean)
        print(f"--- STARTING EMAIL PROCESS ({SENDER_EMAIL}) ---")
        print(f"Total records to process: {total_records}")
        print("-" * 30)

        # 4. Send Emails
        for index, row in df_clean.iterrows():
            name = str(row['Name']).strip()
            email = str(row['Email']).strip()
            role = str(row['Role']).strip()
            email_sent_status = str(row['EmailSent']).strip().upper()

            # Skip if EmailSent is TRUE
            if email_sent_status == 'TRUE':
                print(f"Skipping {name}: Email already sent (Status: TRUE)")
                continue

            if name and email and role and name.lower() != 'nan' and email.lower() != 'nan':
                send_pending_payments_email(SENDER_EMAIL, SENDER_PASSWORD, name, email, role)
            else:
                print(f"Skipping row due to missing data: {name}, {email}")
        
        print(f"\nProcessing complete.")

    except Exception as e:
        print(f"An unexpected error occurred in main execution: {e}")


if __name__ == "__main__":
    main()
    