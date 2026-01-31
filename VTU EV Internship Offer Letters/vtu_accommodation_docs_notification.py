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
# UPDATE THIS PATH TO YOUR ACTUAL CSV FILE NAME
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/vtu_belagavi_accomodation_list_1.csv' 
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Important: Documents Required for Accommodation at VTU Belagavi | VisionAstraa EV Academy'

# --- HARDCODED CREDENTIALS ---
BATCH_CREDENTIALS = {
    1: {
        "EMAIL": "careers@visionastraa.in",
        "PASSWORD": "Z1SIOO0A9b~"
    },
    2: {
        "EMAIL": "visionastraa@evcourse.in",
        "PASSWORD": ">p>W|jv?Kg1"
    }
}

def send_accommodation_email(sender_email, sender_password, name, to_email):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>

        We are writing to you regarding the <strong>accommodation form</strong> you filled out for the VisionAstraa EV Academy's internship program at VTU Belagavi center.
        <br><br>

        To process your accommodation and verify your details upon arrival, <strong>please ensure you bring the following documents with you</strong>. These are mandatory for your check-in process.
        <br><br>

        <strong><u>Required Documents List:</u></strong>
        <ol>
            <li><strong>Student ID Card</strong> (Must carry original)</li>
            <li><strong>Main Payment Receipt</strong> (Proof of payment towards companies/institute)</li>
            <li><strong>Student Aadhaar Card Copy</strong></li>
            <li><strong>College Consent Letter</strong> (Must be approved, endorsed by the Principal, and uploaded to the website)</li>
            <li><strong>Parent Consent Letter</strong> (Duly signed by parent/guardian)</li>
        </ol>
        <br>

        <strong>Missing Documents:</strong><br>
        Please ensure all documents are ready. If any documents are missing, please inform the desk upon arrival, though strict compliance is appreciated to ensure a smooth onboarding.
        <br><br>

        <strong>For any help or queries, you may contact us on LinkedIn:</strong><br>
        <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
        <strong>OR call us on: <a href="tel:+918762246518">+91 87622 46518</a> or <a href="tel:+918075664438">+91 80756 64438</a></strong>
        <br><br>

        Safe travels, and we look forward to seeing you at VTU Belagavi.
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

    if not os.path.exists(CSV_FILE_PATH):
        print(f"Error: Input data file not found at '{CSV_FILE_PATH}'")
        return

    try:
        df = pd.read_csv(CSV_FILE_PATH, header=None)
        
        if len(df.columns) < 8:
            print(f"Error: CSV file must have at least 8 columns. Found {len(df.columns)}.")
            return

        df_clean = pd.DataFrame()
        df_clean['Name'] = df.iloc[:, 0]
        df_clean['Email'] = df.iloc[:, 2]
        df_clean['EmailSent'] = df.iloc[:, 7]

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

        # 5. Send Emails
        for index, row in df_batch.iterrows():
            name = str(row['Name']).strip()
            email = str(row['Email']).strip()
            email_sent_status = str(row['EmailSent']).strip().upper()

            # Skip if EmailSent is TRUE
            if email_sent_status == 'TRUE':
                print(f"Skipping {name}: Email already sent (Status: TRUE)")
                continue

            if name and email and name.lower() != 'nan' and email.lower() != 'nan':
                send_accommodation_email(sender_email, sender_password, name, email)
            else:
                print(f"Skipping row due to missing data: {name}, {email}")
        
        print(f"\nBatch {batch_num} complete.")

    except Exception as e:
        print(f"An unexpected error occurred in main execution: {e}")


if __name__ == "__main__":
    main()