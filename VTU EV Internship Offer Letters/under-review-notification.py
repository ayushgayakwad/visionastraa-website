import pandas as pd
import os
import smtplib
import ssl
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_under_review_applicants_5.csv'

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
