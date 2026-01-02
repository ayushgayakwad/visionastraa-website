import pandas as pd
import os
import smtplib
import ssl
import sys
import argparse
import math
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

# ---------------- CONFIG ----------------
# Update this to your new CSV file if needed
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/full_quick_test.csv' 
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Accommodation at VTU Belagavi starting at just ₹25 | VisionAstraa EV Academy'

# --- HARDCODED CREDENTIALS (UPDATE THESE) ---
# Since this is a private repo, we hardcode them here to avoid GitHub Secrets
BATCH_CREDENTIALS = {
    1: {
        "EMAIL": "visionastraa@evinternships.com",
        "PASSWORD": "a[kE?V6lm7G="
    },
    2: {
        "EMAIL": "visionastraa@evinternships.in",
        "PASSWORD": "]9jw>Upu//Y"
    }
}

def send_internship_details_email(sender_email, sender_password, name, to_email, role, date_str):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        # Fallback if date is missing (kept from original logic, though less used in this text)
        date_text = f"on {date_str}" if date_str and str(date_str).lower() != 'nan' else "recently"

        body = f"""
        Hello {name},
        <br><br>

        We are pleased to share details regarding accommodation for interns joining the <strong>Belagavi center</strong> at Visvesvaraya Technological University (VTU) Belagavi campus for internship at <strong>VisionAstraa EV Academy.</strong>
        <br><br>
        
        <strong><u>IMPORTANT NOTE:</u> This information is ONLY for interns preferring the Belagavi center for their internship. If you are preferring the Bangalore center, please ignore this email.</strong>
        <br><br>

        <strong>Please don't call us or spam us for accommodation in Bangalore, which we cannot provide. Online opting students, kindly ignore this email. A separate Google Form will be shared with those preferring the Bangalore center or opting for online options.</strong>
        <br><br>

        We have arranged affordable stay and food options on campus to ensure a comfortable experience during your internship:

        <ul>
            <li><strong>Accommodation Only:</strong> ₹25 per day (Stay only).</li>
            <li><strong>Accommodation with Food:</strong> ₹150 per day (Includes Stay + 3 Main Meals + 1 Evening Snack).</li>
        </ul>
        <br>

        <strong>Action Required:</strong><br>
        If you are interested in availing these facilities at our Belagavi center (VTU), kindly fill out the Google Form below so we can make the necessary arrangements.
        <br><br>
        
        <strong><a href="https://docs.google.com/forms/d/e/1FAIpQLSfMrCG94HNuuoRjIUF1yvFSSGyH_2PWSPJ_nSeKy1ibqlfXpQ/viewform?usp=dialog">>> CLICK HERE TO FILL THE ACCOMMODATION INTEREST FORM <<</a></strong>
        <br><br>

        <strong>Note regarding Offer Letter:</strong><br>
        If you still haven't received any offer letter from us yet, then you can reply to this email directly and we will release an offer letter for you.
        <br><br>

        We look forward to seeing you there!
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
    # 1. Parse Arguments
    parser = argparse.ArgumentParser(description='Send emails in batches.')
    parser.add_argument('--batch', type=int, choices=[1, 2], required=True, help='Batch number (1 or 2)')
    args = parser.parse_args()
    batch_num = args.batch

    # 2. Get Credentials for this Batch
    creds = BATCH_CREDENTIALS.get(batch_num)
    sender_email = creds["EMAIL"]
    sender_password = creds["PASSWORD"]

    if "replace" in sender_email or "replace" in sender_password:
        print(f"⚠️ WARNING: You have not updated the hardcoded credentials for Batch {batch_num} in the script yet!")

    if not os.path.exists(CSV_FILE_PATH):
        print(f"Error: Input data file not found at '{CSV_FILE_PATH}'")
        return

    try:
        # 3. Load Data
        df = pd.read_csv(CSV_FILE_PATH)
        
        # We assume the file has headers or we infer them. 
        # Structure assumed: ID, Name, Email, Role, Date, Status, EmailSent
        
        if len(df.columns) >= 5:
            # Map columns by index to ensure correct data retrieval regardless of header names
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
        
        # 4. Split Data based on Batch
        total_records = len(df_clean)
        mid_point = math.ceil(total_records / 2)

        if batch_num == 1:
            df_batch = df_clean.iloc[:mid_point]
            print(f"--- BATCH 1 STARTING ({sender_email}) ---")
            print(f"Processing records 1 to {mid_point} (Total rows assigned: {len(df_batch)})")
        else:
            df_batch = df_clean.iloc[mid_point:]
            print(f"--- BATCH 2 STARTING ({sender_email}) ---")
            print(f"Processing records {mid_point + 1} to {total_records} (Total rows assigned: {len(df_batch)})")

        print("-" * 30)

        # 6. Send Emails
        for index, row in df_batch.iterrows():
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
                send_internship_details_email(sender_email, sender_password, name, email, role, date_str)
            else:
                print(f"Skipping row due to missing data: {name}, {email}")
        
        print(f"\nBatch {batch_num} complete.")

    except Exception as e:
        print(f"An unexpected error occurred in main execution: {e}")


if __name__ == "__main__":
    main()