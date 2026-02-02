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
EMAIL_SUBJECT = 'Internship: Live Session Link - Inauguration Day | VisionAstraa EV Academy'

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

            <p>Finally the time has arrived for your internship to commence!</p> 

            <p>We’re excited to invite you all to the <strong>VisionAstraa EV Academy Internship - Inauguration Day</strong>.</p>

            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #e9ecef; margin: 20px 0;">
                <h3 style="color: #0056b3; margin-top: 0;">VisionAstraa EV Academy Internship - Inauguration Day Agenda - Feb 2, 2026!</h3>
                <ol style="padding-left: 20px;">
                    <li>Welcome Address by <strong>Nikhil Jain C S</strong>, Co-founder & CEO, VisionAstraa EV Academy</li>
                    <li>Internship Inaugural Address by <strong>Honorable Dr. Vidyashankar Sir</strong>, Vice Chancellor, VTU</li>
                    <li>Keynote Address & Introduction to VisionAstraa EV Academy by <strong>Yedu Jathavedan</strong>, Co-founder & Chairman, VisionAstraa EV Academy</li>
                    <li>Q/A Session and Conclusion</li>
                </ol>
            </div>

            <p>
                <strong>🕑 Time (Feb 2, 2026):</strong> 2:00 PM – 4:00 PM<br>
                <strong>📍 Platform:</strong> YouTube Live
            </p>

            <p style="margin: 20px 0;">
                🔗 <strong>Join the YouTube live stream using the following link:</strong><br>
                <a href="https://www.youtube.com/watch?v=hp85pXj6EqY" style="color: #007bff; font-weight: bold; text-decoration: underline;">https://www.youtube.com/watch?v=hp85pXj6EqY</a>
            </p>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <p><strong>Important Note on Session Timings:</strong></p>
            <ul>
                <li><strong>Tomorrow (2nd Feb 2026):</strong> There will be <strong>only one session</strong> from <strong>2:00 PM to 4:00 PM</strong>.</li>
                <li><strong>From 3rd Feb 2026 onwards:</strong> There will be <strong>two sessions every day</strong>:
                    <ul>
                        <li>Morning Session: <strong>10:00 AM – 12:00 PM</strong></li>
                        <li>Afternoon Session: <strong>2:00 PM – 4:00 PM</strong></li>
                    </ul>
                </li>
            </ul>

            <p>We look forward to your enthusiastic participation as you begin this exciting internship journey.</p> 

            <p>All the best! ✨</p>

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