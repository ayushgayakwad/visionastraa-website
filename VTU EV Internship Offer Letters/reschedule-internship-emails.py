import pandas as pd
import os
import smtplib
import ssl
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

CSV_FILE_PATH = 'VTU EV Internship Offer Letters/applicants-vtu-internyet-3.csv'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
SENDER_EMAIL = os.getenv('SENDER_EMAIL')
SENDER_PASSWORD = os.getenv('SENDER_PASSWORD')
RESCHEDULE_SUBJECT = 'Important Update: Internship Commencement Date Rescheduled'

def send_reschedule_email(name, to_email, role):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", SENDER_EMAIL))
        msg['To'] = to_email
        msg['Subject'] = RESCHEDULE_SUBJECT

        body = f"""
        Hello {name},
        <br><br>
        We are writing to inform you about a change in the commencement date for your internship in <strong>{role}</strong> at <strong>VisionAstraa EV Academy</strong>. The new start date is <strong>September 8th, 2025</strong>, instead of the previously communicated September 3rd. We sincerely apologize for any inconvenience this may cause.
        <br><br>
        A detailed schedule with timings for the internship will be mailed to you by <strong>September 6th, 2025</strong>.
        <br><br>
        In the meantime, we request you to fill out the application form at your earliest convenience. This will help us plan your internship better.
        <br>
        <a href="https://visionastraa.com/ev-internship-application.html">https://visionastraa.com/ev-internship-application.html</a>
        <br><br>
        <strong>In the application form</strong>, please select your <strong>preferred internship commencement date (September 2025 or January 2026)</strong>, your <strong>preferred center (Belagavi or Bangalore)</strong>, and also <strong>confirm in the VTU Portal that you have accepted the offer.</strong>
        <br><br>
        We are looking forward to you joining us.
        <br><br>
        Thank you.
        <br><br>
        For any further queries, please reach out to us on our LinkedIn page: <a href="https://in.linkedin.com/company/va-ev-academy">https://in.linkedin.com/company/va-ev-academy</a>
        <br>
        Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a>
        """
        msg.attach(MIMEText(body, 'html'))

        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT, context=context) as server:
            server.login(SENDER_EMAIL, SENDER_PASSWORD)
            server.sendmail(SENDER_EMAIL, to_email, msg.as_string())

        print(f"Successfully sent reschedule email to: {name} at {to_email}")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending reschedule email to {name}: {e}")
        return False
    except Exception as e:
        print(
            f"An unexpected error occurred while sending reschedule email to {name}: {e}")
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
            print("Error: CSV file does not have the expected 5 columns.")
            return

        df.columns = ['ID', 'Name', 'Email', 'Role', 'EmailSent']

        print("\nSending reschedule emails...")
        for index, row in df.iterrows():
                name = str(row['Name']).strip()
                email = str(row['Email']).strip()
                role = str(row['Role']).strip()

                if name and email and role and name.lower() != 'nan' and email.lower() != 'nan' and role.lower() != 'nan':
                    send_reschedule_email(name, email, role)
                else:
                    print(
                        f"Skipping row {index+1} due to missing name, email, or role.")
        print("\nReschedule emailing complete.")

    except Exception as e:
        print(f"An unexpected error occurred: {e}")

if __name__ == "__main__":
    main()