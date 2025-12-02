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
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_aug_nov_offer_released_applicants.csv'
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = '[FINAL REMINDER] Important Information Regarding Your Internship Application at VisionAstraa EV Academy'

# --- HARDCODED CREDENTIALS (UPDATE THESE) ---
# Since this is a private repo, we hardcode them here to avoid GitHub Secrets
BATCH_CREDENTIALS = {
    1: {
        "EMAIL": "your_email_1@domain.com",
        "PASSWORD": "your_password_1"
    },
    2: {
        "EMAIL": "your_email_2@domain.com",
        "PASSWORD": "your_password_2"
    }
}

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

        This is a gentle reminder that it has been a while since your <strong>internship offer for {role} role was released {date_text} on the VTU Portal</strong>, along with the official offer letter from <strong>VisionAstraa EV Academy</strong>.
        <br><br>

        However, our records indicate that the <strong>internship acceptance is still pending on the VTU Portal</strong>.
        <br><br>

        <strong>The final date to accept the internship offer is 05th December 2025. Kindly accept the offer before this date to secure your internship position.</strong>

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

        # 4. Filter Data (Remove already sent)
        # We process filtering here or inside the loop? 
        # Better to handle inside loop to allow correct splitting logic based on original row counts if needed,
        # BUT splitting is usually done on the workload. 
        # Let's split first to ensure both runners get their share of the FILE, then check status.
        
        # 5. Split Data based on Batch
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
    