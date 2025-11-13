import pandas as pd
import os
import smtplib
import ssl
import argparse
import json
import zlib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_offer_released_applicants_main.csv'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465 
SMTP_ACCOUNTS = [
    {
        'username': 'careers@visionastraa.in',
        'password': 'Z1SIOO0A9b~'
    },
    {
        'username': 'visionastraa@evcourse.in',
        'password': '>p>W|jv?Kg1'
    }
]

EMAIL_SUBJECT = '[URGENT] Reminder: Action Required for Your Internship at VisionAstraa EV Academy'

def send_internship_details_email(name, to_email, role, sender_email, sender_password):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>
        This is a reminder regarding the project-based internship starting from <strong>January 2026</strong>.
        <br><br>
        <strong>Deadline to accept the offer has been extended to an additional five days from the date of this email due to exceptionally high number of applicants.</strong>
        <br><br>
        <strong>Kindly pay the internship acceptance fees in the VTU portal and accept the offer in the VTU Portal before the deadline.</strong>
        <br><br>
        <strong>Follow the steps below to accept the offer:</strong>
        <br><br>
        -> Enter the URL (<a href="https://vtu.internyet.in">https://vtu.internyet.in</a>)
        <br>
        -> Login using your username and password
        <br>
        -> Navigate to `Applied Internships` section in the dashboard
        <br>
        -> Click on the `Accept` button to accept <strong>VisionAstraa EV Academy's offer</strong>.
        <br><br>
        We also request you to fill out the following application form at your earliest convenience. This will help us plan your internship better.
        <br><br>
        <strong>In the application form</strong>,
        <br>
        -> Please select your <strong> internship commencement date (January 2026)</strong>, 
        <br>
        -> Your <strong>preferred center (Online/Belagavi/Bangalore)</strong>, and
        <br>
        -> Confirm whether you have <strong>accepted the offer</strong> in VTU Portal <strong>(Yes/No)</strong>.
        <br><br>
        <strong>Application Form Link:</strong> <a href="https://visionastraa.com/ev-internship-application.html">https://visionastraa.com/ev-internship-application.html</a>
        <br><br>
        To ensure a smooth onboarding process, it is essential that you join the WhatsApp group:
        <br>
        - <strong>January 2026</strong>, please join: <a href="https://chat.whatsapp.com/E1ghfP3cstjDphOQEpTB7x?mode=ems_wa_t">January 2026 Internship Group</a>
        <br><br>
        For any queries reach out to us on LinkedIn: <a href="https://in.linkedin.com/company/va-ev-academy">https://in.linkedin.com/company/va-ev-academy</a>
        <br>
        Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a>
        <br><br>
        Looking forward to having you onboard!
        <br><br>
        Happy Interning!
        """
        msg.attach(MIMEText(body, 'html'))

        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT, context=context) as server:
            server.login(sender_email, sender_password)
            server.sendmail(sender_email, to_email, msg.as_string())

        print(f"Successfully sent internship details email to: {name} at {to_email} using {sender_email}")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending email to {name} using {sender_email}: {e}")
        return False
    except Exception as e:
        print(f"An unexpected error occurred while sending email to {name}: {e}")
        return False

def crc32_hash(email):
    if isinstance(email, str):
        return zlib.crc32(email.encode('utf-8')) & 0xffffffff
    return 0

def main():
    parser = argparse.ArgumentParser(description='Send final reminder emails in parallel.')
    parser.add_argument('--config', type=str, required=True, help='JSON string with job configuration')
    args = parser.parse_args()

    try:
        config = json.loads(args.config)
        account_index = config['account_index']
        mod_total = config['mod_total']
        mod_value = config['mod_value']
    except Exception as e:
        print(f"Error parsing --config JSON: {e}")
        exit(1)

    current_account = SMTP_ACCOUNTS[account_index]

    print(f"--- Starting Job ---")
    print(f"Using SMTP Account: {current_account['username']}")
    print(f"Processing email partition: {mod_value} of {mod_total}")


    if not os.path.exists(CSV_FILE_PATH):
        print(f"Error: Input data file not found at '{CSV_FILE_PATH}'")
        return

    try:
        df = pd.read_csv(CSV_FILE_PATH, header=0)

        required_cols = ['name', 'email', 'role', 'flag']
        if not all(col in df.columns for col in required_cols):
            print(f"Error: CSV file must contain the columns: {', '.join(required_cols)}")
            return

        initial_count = len(df)
        df = df[df['flag'].astype(str).str.lower() == 'false']
        print(f"Found {len(df)} entries with flag=FALSE (out of {initial_count} total).")

        df['hash'] = df['email'].apply(crc32_hash)
        df_partition = df[df['hash'] % mod_total == mod_value].copy()
        print(f"This job will process {len(df_partition)} emails for partition {mod_value}/{mod_total}.")


        print("\nSending internship details emails...")
        for index, row in df_partition.iterrows():
            name = str(row['name']).strip()
            email = str(row['email']).strip()
            role = str(row['role']).strip()

            if name and email and role and name.lower() != 'nan' and email.lower() != 'nan' and role.lower() != 'nan':
                send_internship_details_email(
                    name, 
                    email, 
                    role, 
                    current_account['username'], 
                    current_account['password']
                )
            else:
                print(f"Skipping row {index+1} due to missing name, email, or role.")
        
        print("\nInternship details emailing complete for this job.")

    except Exception as e:
        print(f"An unexpected error occurred: {e}")

if __name__ == "__main__":
    main()