import pandas as pd
import os
import smtplib
import ssl
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_aug_sept_offer_released_applicants_1.csv'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
SENDER_EMAIL = os.getenv('SENDER_EMAIL')
SENDER_PASSWORD = os.getenv('SENDER_PASSWORD')
EMAIL_SUBJECT = 'Important Information Regarding Your Internship Application at VisionAstraa EV Academy'

def send_internship_details_email(name, to_email, role):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", SENDER_EMAIL))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>

        This is a gentle reminder that it has been <strong>around two months</strong> since your <strong>internship offer for {role} role was released on the VTU Portal</strong>, along with the official offer letter from <strong>VisionAstraa EV Academy</strong>.
        <br><br>

        However, our records indicate that the <strong>internship acceptance is still pending on the VTU Portal</strong>.
        <br><br>

        <strong>Action Required on VTU Portal:</strong>
        <ul>
            <li>Please log in to your VTU Portal (<a href="https://vtu.internyet.in">https://vtu.internyet.in</a>).</li>
            <li>Accept VisionAstraa EV Academy's offer by <u>paying the Internship Acceptance Fee</u> on the VTU Portal.</li>
        </ul>
        <br><br>

        If you have already completed the payment or are facing difficulties on the VTU portal, please feel free to reach out to us for verification or assistance.
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
            df.columns = ['Name', 'Email', 'Role', 'Status', 'EmailSent']
        else:
            print("CSV file has an unexpected number of columns. Processing the first 5.")
            df = df.iloc[:, :5]
            df.columns = ['Name', 'Email', 'Role', 'Status', 'EmailSent']

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
