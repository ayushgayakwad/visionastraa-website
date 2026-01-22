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
# UPDATE THIS FILENAME to match your uploaded CSV
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/internship_mode_selection_1.csv'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Action Required: Choose Your Internship Mode - VisionAstraa EV Academy'

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
        <body>
            <p>Dear {name},</p>

            <p>We are excited to welcome you all for your Internship at VisionAstraa EV Academy.</p>

            <p>Based on the <strong>VTU guidelines and instructions</strong>, we are starting Internships at <strong>VTU Nagarbavi</strong> (Bangalore Center), at <strong>VTU Belagavi</strong> (Belagavi Center), and Online parallelly.</p>
            
            <p style="font-size: 16px;"><strong>Please fill out the Google Form below to choose your preferred mode (Online / Offline):</strong></p>
            <p><a href="https://forms.gle/naaETnaEtZmHa1ev8" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Click Here to Fill the Form</a></p>
            <p>Or use this link: <a href="https://forms.gle/naaETnaEtZmHa1ev8">https://forms.gle/naaETnaEtZmHa1ev8</a></p>

            <p><strong>Internship Start Date:</strong> Feb 2nd</p>

            <p><strong>Daily Class Timings:</strong></p>
            <ul>
                <li>Monday to Friday (same for online and offline)</li>
                <li>Morning Session: 10:00 AM to 12:00 PM</li>
                <li>Lunch Break: 12:00 PM to 2:00 PM</li>
                <li>Afternoon Session: 2:00 PM to 4:00 PM</li>
                <li>Doubt Clearance Session: 4:00 PM to 5:00 PM (if any)</li>
            </ul>

            <p><strong>Feb 3rd - Feb 13th: <span style="color:#d9534f;">Online classes only</span></strong></p>
            <ul>
                <li>Live Classes will be conducted, and recordings will be sent by EOD.</li>
                <li><strong>All students</strong> (including those who opted for offline) should attend online during this period (2 weeks given to help students and parents to plan for nearby accommodations).</li>
            </ul>

            <p><strong>Feb 16th - Feb 27th: Offline classes</strong></p>
            <ul>
                <li>Only for those who have chosen offline; the rest can continue online.</li>
            </ul>

            <p><strong>Mar 2nd: Projects will start</strong></p>
            <ul>
                <li>Projects can be done online, offline, or at your convenient place.</li>
                <li>Projects can be executed individually or in groups of your choice.</li>
                <li>Rest of the project details will be given at the end of February.</li>
            </ul>

            <strong>For any help or queries, you may contact us on LinkedIn:</strong><br>
            <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
            Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
            <strong>OR, call us at: <a href="tel:+918762246518">+91 87622 46518</a> or <a href="tel:+918075664438">+91 80756 64438</a></strong>
            <br><br>

            <p>We are here to support and help you all complete your internship successfully. Don't panic or worry about anything.</p>
            
            <p>Happy Interning 😊</p>

            <br>
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
        
        # Assumption: Columns are [ID, Name, Email, ...] or similar.
        # We try to find Name and Email columns intelligently or by index.
        
        # If headers exist, try to find them
        cols = [c.lower() for c in df.columns]
        
        if 'name' in cols and 'email' in cols:
            # Good, use column names
             pass # df is already good to go
        elif len(df.columns) >= 3:
            # Fallback to indices: Col 1 = Name, Col 2 = Email (0-indexed)
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