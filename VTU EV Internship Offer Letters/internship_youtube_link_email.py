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
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/full_offer_accepted.csv'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Internship: Live Session Links - Feb 3, 2026 | VisionAstraa EV Academy'

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

def send_email(sender_email, sender_password, name, to_email):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        # HTML Body
        body = f"""
        <html>
        <body style="font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">
            <p>Dear {name},</p> 

            <p>Please find below the schedule and joining links for the internship sessions scheduled for <strong>February 3, 2026</strong>.</p>
            
            <p><em>Please join the link corresponding to your specific domain.</em></p>

            <div style="background-color: #f0f8ff; padding: 15px; border-radius: 5px; border: 1px solid #d6e9c6; margin: 20px 0;">
                <h3 style="color: #0056b3; margin-top: 0; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                    ☀️ Morning Session (10:00 AM - 12:00 PM)
                </h3>
                
                <p><strong>Embedded Systems and Design & Development:</strong><br>
                <a href="https://www.youtube.com/watch?v=VZqsrJurEVU" style="color: #007bff; font-weight: bold;">https://www.youtube.com/watch?v=VZqsrJurEVU</a></p>
                
                <p><strong>AIML and Data Science:</strong><br>
                <a href="https://www.youtube.com/watch?v=t0Jf_ZOf3R8" style="color: #007bff; font-weight: bold;">https://www.youtube.com/watch?v=t0Jf_ZOf3R8</a></p>
                
                <p><strong>Full-Stack and Web Development:</strong><br>
                <a href="https://www.youtube.com/watch?v=uWZJr5Nmj6w" style="color: #007bff; font-weight: bold;">https://www.youtube.com/watch?v=uWZJr5Nmj6w</a></p>
            </div>

            <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeeba; margin: 20px 0;">
                <h3 style="color: #856404; margin-top: 0; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                    🕑 Afternoon Session (2:00 PM - 4:00 PM)
                </h3>
                
                <p><strong>Embedded Systems and Design & Development:</strong><br>
                <a href="https://www.youtube.com/watch?v=ZZnns3uXDbE" style="color: #007bff; font-weight: bold;">https://www.youtube.com/watch?v=ZZnns3uXDbE</a></p>
                
                <p><strong>AIML and Data Science:</strong><br>
                <a href="https://www.youtube.com/watch?v=h6upMa_9TV8" style="color: #007bff; font-weight: bold;">https://www.youtube.com/watch?v=h6upMa_9TV8</a></p>
                
                <p><strong>Full-Stack and Web Development:</strong><br>
                <a href="https://www.youtube.com/watch?v=jA-sY8V2QjE" style="color: #007bff; font-weight: bold;">https://www.youtube.com/watch?v=jA-sY8V2QjE</a></p>
            </div>

            <p><strong>Note:</strong> Please ensure you join on time.</p>

            <strong>For any help or queries, you may contact us on LinkedIn:</strong><br>
            <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
            Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
            <strong>OR, call us at: <a href="tel:+918762246518">+91 87622 46518</a> or <a href="tel:+918075664438">+91 80756 64438</a></strong>
            <br>

            <p>Happy Interning! ✨</p>

            <p>Warm regards,<br>
            <strong>VisionAstraa EV Academy</strong></p>
        </body>
        </html>
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
        print("Please make sure you have uploaded the CSV file and updated CSV_FILE_PATH in the script.")
        return

    try:
        # 3. Load Data
        df = pd.read_csv(CSV_FILE_PATH)
        
        # If headers exist, try to find them
        cols = [c.lower() for c in df.columns]
        
        if 'name' in cols and 'email' in cols:
             pass 
        elif len(df.columns) >= 3:
            print("Warning: 'Name' or 'Email' headers not found. Using column indices 1 (Name) and 2 (Email).")
            df.rename(columns={df.columns[1]: 'Name', df.columns[2]: 'Email'}, inplace=True)
        
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

            if name and email and name.lower() != 'nan' and email.lower() != 'nan':
                send_email(sender_email, sender_password, name, email)
            else:
                print(f"Skipping row due to missing data: {name}, {email}")
        
        print(f"\nBatch {batch_num} complete.")

    except Exception as e:
        print(f"An unexpected error occurred in main execution: {e}")

if __name__ == "__main__":
    main()