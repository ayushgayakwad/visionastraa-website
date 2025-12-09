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
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_offer_released_notification_3.csv'
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = '[FINAL REMINDER] Urgent Action Required Regarding Your Internship Application at VisionAstraa EV Academy'

# --- HARDCODED CREDENTIALS (UPDATE THESE) ---
# Unified credentials for single-batch processing
SENDER_EMAIL = "careers@visionastraa.in"
SENDER_PASSWORD = "Z1SIOO0A9b~"

def send_internship_details_email(sender_email, sender_password, name, to_email, role, date_str):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        # Fallback if date is missing
        date_text = f"on {date_str}" if date_str and str(date_str).lower() != 'nan' else "recently"

        body = f"""
        Hello {name},
        <br><br>

        This is a final reminder that it has been a while since your <strong>internship offer for {role} role was released {date_text} on the VTU Portal</strong>, along with the official offer letter from <strong>VisionAstraa EV Academy</strong>.
        <br><br>

        Our records indicate that the <strong>internship acceptance is still pending on the VTU Portal</strong>.
        <br><br>

        <strong>The final date to accept the internship offer is 14th December 2025 before 6:00 PM. Kindly accept the offer before this date to secure your internship position.</strong>
        <br><br>

        <strong><u>Please note that failure to accept the offer by the deadline may result in the revocation of the internship offer.</u></strong>
        <br><br>

        <strong>Action Required on VTU Portal:</strong>
        <ul>
            <li>Please log in to your VTU Portal (<a href="https://vtu.internyet.in">https://vtu.internyet.in</a>).</li>
            <li>Accept VisionAstraa EV Academy's offer by <u>paying the Internship Acceptance Fee</u> on the VTU Portal.</li>
        </ul>
        <br><br>

        If you have already completed the payment or are facing difficulties on the VTU portal, please reach out to VTU Portal's support team for verification or assistance.
        <br><br>

        <strong>For any help or queries, you may contact us on LinkedIn:</strong><br>
        <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a>
        <br><br>

        We are here to support you throughout this process.  
        <br><br>

        Thank you, and we request you to complete the acceptance process at the earliest.
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
        
        # Structure assumed: ID, Name, Email, Role, Date, Status, EmailSent
        if len(df.columns) >= 5:
            # Map columns by index to ensure correct data retrieval
            df_clean = pd.DataFrame()
            df_clean['Name'] = df.iloc[:, 1]  # 2nd col
            df_clean['Email'] = df.iloc[:, 2] # 3rd col
            df_clean['Role'] = df.iloc[:, 3]  # 4th col
            df_clean['Date'] = df.iloc[:, 4]  # 5th col
            
            # Check for EmailSent column (Index 6 / 7th column)
            if len(df.columns) > 6:
                df_clean['EmailSent'] = df.iloc[:, 6]
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
            date_str = str(row['Date']).strip()
            email_sent_status = str(row['EmailSent']).strip().upper()

            # Skip if EmailSent is TRUE
            if email_sent_status == 'TRUE':
                print(f"Skipping {name}: Email already sent (Status: TRUE)")
                continue

            if name and email and role and name.lower() != 'nan' and email.lower() != 'nan':
                send_internship_details_email(SENDER_EMAIL, SENDER_PASSWORD, name, email, role, date_str)
            else:
                print(f"Skipping row due to missing data: {name}, {email}")
        
        print(f"\nProcessing complete.")

    except Exception as e:
        print(f"An unexpected error occurred in main execution: {e}")


if __name__ == "__main__":
    main()
    