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
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_status_update_alert_1.csv'
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Status Update Alert: Internship Application at VisionAstraa EV Academy'

# --- HARDCODED CREDENTIALS ---
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

def send_internship_details_email(sender_email, sender_password, name, to_email):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>

        We are writing to inform you that your internship application status has been updated to <strong>Shortlisted</strong> on the VTU Portal for the internship at VisionAstraa EV Academy.
        <br><br>

        <strong>Important Action Required:</strong>
        <br>
        If you have <strong>NOT</strong> received any offer letter from us yet, please contact us immediately. 
        <br><br>
        Once you reach out to us, we will verify your details and <strong>release your offer letter</strong> directly to you.
        <br><br>

        <strong>How to Contact Us:</strong>
        <br>
        You may reach out via LinkedIn or phone:
        <br><br>
        LinkedIn: <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        Speak directly with our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
        <strong>OR, call us on: <a href="tel:+918762246518">+91 87622 46518</a></strong>
        <br><br>

        We look forward to having you onboard!
        <br><br>
        Best regards,<br>
        <strong>VisionAstraa EV Academy</strong>
        """

        msg.attach(MIMEText(body, 'html'))

        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT, context=context) as server:
            server.login(sender_email, sender_password)
            server.sendmail(sender_email, to_email, msg.as_string())

        print(f"Successfully sent status update email to: {name} at {to_email}")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending email to {name}: {e}")
        return False
    except Exception as e:
        print(f"An unexpected error occurred while sending email to {name}: {e}")
        return False


def main():
    # 1. Parse Arguments
    parser = argparse.ArgumentParser(description='Send shortlisted emails in batches.')
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
        # 3. Load Data
        df = pd.read_csv(CSV_FILE_PATH, header=None)
        
        # Ensure correct columns: Name, Email, Status, EmailSent
        if len(df.columns) < 4:
            df.columns = ['Name', 'Email', 'Status', 'EmailSent']
        else:
            # If file has more columns, just take the first 4
            df = df.iloc[:, :4]
            df.columns = ['Name', 'Email', 'Status', 'EmailSent']

        # 4. Split Data based on Batch
        total_records = len(df)
        mid_point = math.ceil(total_records / 2)

        if batch_num == 1:
            df_batch = df.iloc[:mid_point]
            print(f"--- BATCH 1 STARTING ({sender_email}) ---")
            print(f"Processing records 1 to {mid_point} (Total rows assigned: {len(df_batch)})")
        else:
            df_batch = df.iloc[mid_point:]
            print(f"--- BATCH 2 STARTING ({sender_email}) ---")
            print(f"Processing records {mid_point + 1} to {total_records} (Total rows assigned: {len(df_batch)})")

        print("-" * 30)
        print("\nSending status update emails...")
        
        # 5. Send Emails
        for index, row in df_batch.iterrows():
            name = str(row['Name']).strip()
            email = str(row['Email']).strip()
            # We don't strictly need 'Status' for the email logic, but we read it
            
            # Basic validation
            if name and email and name.lower() != 'nan' and email.lower() != 'nan':
                send_internship_details_email(sender_email, sender_password, name, email)
            else:
                print(f"Skipping row {index+1} due to missing name or email.")
        
        print(f"\nBatch {batch_num} email sending complete.")

    except Exception as e:
        print(f"An unexpected error occurred: {e}")


if __name__ == "__main__":
    main()