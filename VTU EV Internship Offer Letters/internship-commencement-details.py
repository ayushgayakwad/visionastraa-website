import pandas as pd
import os
import smtplib
import ssl
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

CSV_FILE_PATH = 'VTU EV Internship Offer Letters/applicants-vtu-internyet-main.csv'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
SENDER_EMAIL = os.getenv('SENDER_EMAIL')
SENDER_PASSWORD = os.getenv('SENDER_PASSWORD')
EMAIL_SUBJECT = 'Important Update: Internship Commencement Details'

def send_internship_details_email(name, to_email, role):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", SENDER_EMAIL))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>
        We are excited to welcome you to the VisionAstraa EV Academy!
        <br><br>
        Your project-based internship for the <strong>{role}</strong> role is scheduled to start on <strong>September 8th, 2025, at 10 AM</strong>. A detailed schedule and Google Meet links will be shared with you soon in the official WhatsApp group.
        <br><br>
        Please join the appropriate WhatsApp group based on your internship start date:
        <br>
        - For interns starting in <strong>September 2025</strong>, please join: <a href="https://chat.whatsapp.com/LWRFIbB73yu1OvVbE8goUX?mode=ems_wa_t">September 2025 Internship Group</a>
        <br>
        - For interns starting in <strong>January 2026</strong>, please join: <a href="https://chat.whatsapp.com/E1ghfP3cstjDphOQEpTB7x?mode=ems_wa_t">January 2026 Internship Group</a>
        <br><br>
        For any queries, please do not hesitate to reach out to us on LinkedIn. We are here to help you in any way we can.
        <br>
        - Company LinkedIn Page: <a href="https://in.linkedin.com/company/va-ev-academy">https://in.linkedin.com/company/va-ev-academy</a>
        <br>
        - Talk to our CEO, Nikhil Jain C S: <a href="https://in.linkedin.com/in/nikhiljaincs">https://in.linkedin.com/in/nikhiljaincs</a>
        <br><br>
        We are looking forward to you joining our team and contributing to the future of electric vehicle technology!
        <br><br>
        Best regards,
        <br>
        VisionAstraa EV Academy
        """
        msg.attach(MIMEText(body, 'html'))

        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT, context=context) as server:
            server.login(SENDER_EMAIL, SENDER_PASSWORD)
            server.sendmail(SENDER_EMAIL, to_email, msg.as_string())

        print(f"Successfully sent internship details email to: {name} at {to_email}")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending email to {name}: {e}")
        return False
    except Exception as e:
        print(f"An unexpected error occurred while sending email to {name}: {e}")
        return False

def main():
    if not SENDER_EMAIL or not SENDER_PASSWORD:
        print("Error: SENDER_EMAIL or SENDER_PASSWORD environment variables not set.")
        return

    if not os.path.exists(CSV_FILE_PATH):
        print(f"Error: Input data file not found at '{CSV_FILE_PATH}'")
        return

    try:
        df = pd.read_csv(CSV_FILE_PATH, header=None)
        if len(df.columns) < 5:
            df.columns = ['ID', 'Name', 'Email', 'Role', 'EmailSent']
        else:
            print("CSV file has an unexpected number of columns. Processing the first 5.")
            df = df.iloc[:, :5]
            df.columns = ['ID', 'Name', 'Email', 'Role', 'EmailSent']


        print("\nSending internship details emails...")
        for index, row in df.iterrows():
            name = str(row['Name']).strip()
            email = str(row['Email']).strip()
            role = str(row['Role']).strip()

            if name and email and role and name.lower() != 'nan' and email.lower() != 'nan' and role.lower() != 'nan':
                send_internship_details_email(name, email, role)
            else:
                print(f"Skipping row {index+1} due to missing name, email, or role.")
        print("\nInternship details emailing complete.")

    except Exception as e:
        print(f"An unexpected error occurred: {e}")

if __name__ == "__main__":
    main()