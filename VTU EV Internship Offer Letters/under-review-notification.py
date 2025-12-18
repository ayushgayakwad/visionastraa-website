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
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_under_review_applicants_6.csv'
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Important Information Regarding Your Internship Application at VisionAstraa EV Academy'

# --- HARDCODED CREDENTIALS (BATCH PROCESSING) ---
# Hardcoded to avoid GitHub Secrets complexity for this private repo
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

def send_internship_details_email(sender_email, sender_password, name, to_email, role):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>

        <strong>Please Note:</strong><br>
        This email is intended <u>only for applicants who have NOT yet received any offer letter</u> from VisionAstraa EV Academy.
        <br><br>

        <strong>Important Points:</strong>
        <ul>
            <li>This email is meant only for applicants who have not received any offer letter from VisionAstraa EV Academy so far.</li>
            <li>If you have not received any offer letter from VisionAstraa EV Academy till now, please contact us immediately.</li>
            <li>If you are unsure whether your offer letter was generated or not, you may reach out to us for confirmation.</li>
            <li>If you have already received your offer letter earlier, you may ignore this message.</li>
        </ul>
        <br>

        We are here to support you and ensure a smooth onboarding process.  
        If you have any queries related to the offer letter or internship confirmation, feel free to reach out to us.
        <br><br>

        For any queries, you may contact us on LinkedIn:<br>
        <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        Speak directly with our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
        <strong>OR, call us on: <a href="tel:+918762246518">+91 87622 46518</a></strong>
        <br><br>

        Looking forward to supporting you through the onboarding process!  
        <br><br>
        Happy Interning!
        """

        msg.attach(MIMEText(body, 'html'))

        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT, context=context) as server:
            server.login(sender_email, sender_password)
            server.sendmail(sender_email, to_email, msg.as_string())

        print(f"Successfully sent internship details email to: {name} at {to_email}")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending email to {name}: {e}")
        return False
    except Exception as e:
        print(f"An unexpected error occurred while sending email to {name}: {e}")
        return False


def main():
    # 1. Parse Arguments
    parser = argparse.ArgumentParser(description='Send under review emails in batches.')
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
        
        # Handle column names based on file structure (handling variable column counts)
        if len(df.columns) < 5:
            df.columns = ['Name', 'Email', 'Role', 'Status', 'EmailSent']
        else:
            # If there are extra columns, just take the first 5 relevant ones
            # print("CSV file has an unexpected number of columns. Processing the first 5.")
            df = df.iloc[:, :5]
            df.columns = ['Name', 'Email', 'Role', 'Status', 'EmailSent']

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

        # 5. Send Emails
        for index, row in df_batch.iterrows():
            name = str(row['Name']).strip()
            email = str(row['Email']).strip()
            role = str(row['Role']).strip()

            # Basic validation
            if name and email and role and name.lower() != 'nan' and email.lower() != 'nan' and role.lower() != 'nan':
                send_internship_details_email(sender_email, sender_password, name, email, role)
            else:
                print(f"Skipping row {index+1} due to missing name, email, or role.")
        
        print(f"\nBatch {batch_num} emailing complete.")

    except Exception as e:
        print(f"An unexpected error occurred: {e}")


if __name__ == "__main__":
    main()
