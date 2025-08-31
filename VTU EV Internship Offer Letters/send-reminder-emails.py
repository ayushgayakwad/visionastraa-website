import pandas as pd
import os
import smtplib
import ssl
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email import encoders

CSV_FILE_PATH = 'VTU EV Internship Offer Letters/applicants-vtu-internyet.csv'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
SENDER_EMAIL = os.getenv('SENDER_EMAIL')
SENDER_PASSWORD = os.getenv('SENDER_PASSWORD')
REMINDER_SUBJECT = 'Reminder: Action Required for VisionAstraa Internship Offer'

def send_reminder_email(name, to_email, role):
    try:
        msg = MIMEMultipart()
        msg['From'] = SENDER_EMAIL
        msg['To'] = to_email
        msg['Subject'] = REMINDER_SUBJECT

        body = f"""
        Hello {name},
        <br><br>
        This is a friendly reminder to accept your internship offer for the <strong>{role}</strong> role at <strong>VisionAstraa EV Academy</strong>.
        <br><br>
        Please log in to the VTU Portal at <a href="https://vtu.internyet.in">https://vtu.internyet.in</a> to accept the offer as soon as possible. We are excited about your potential contribution to our team and look forward to welcoming you aboard!
        <br><br>
        If you have any questions or need assistance, feel free to reach out to us on our LinkedIn page: <a href="https://in.linkedin.com/company/va-ev-academy">https://in.linkedin.com/company/va-ev-academy</a>
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

        print(f"Successfully sent reminder email to: {name} at {to_email}")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending reminder email to {name}: {e}")
        return False
    except Exception as e:
        print(
            f"An unexpected error occurred while sending reminder email to {name}: {e}")
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

        print("\nSending reminder emails...")
        for index, row in df.iterrows():
            if str(row['EmailSent']).strip().upper() == 'TRUE':
                name = str(row['Name']).strip()
                email = str(row['Email']).strip()
                role = str(row['Role']).strip()

                if name and email and role and name.lower() != 'nan' and email.lower() != 'nan' and role.lower() != 'nan':
                    send_reminder_email(name, email, role)
                else:
                    print(
                        f"Skipping row {index+1} due to missing name, email, or role.")
        print("\nReminder emailing complete.")

    except Exception as e:
        print(f"An unexpected error occurred: {e}")

if __name__ == "__main__":
    main()