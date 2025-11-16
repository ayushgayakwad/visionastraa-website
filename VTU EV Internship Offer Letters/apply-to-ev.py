import pandas as pd
import os
import smtplib
import ssl
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_shortlisted_applicants_1.csv'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
SENDER_EMAIL = os.getenv('SENDER_EMAIL')
SENDER_PASSWORD = os.getenv('SENDER_PASSWORD')
EMAIL_SUBJECT = '[URGENT] Action Required for Your Internship at VisionAstraa EV Academy'

def send_internship_details_email(name, to_email, role):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", SENDER_EMAIL))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>
        This email is regarding the project-based internship for the role of <strong>{role}</strong> starting from <strong>January 2026</strong>.
        <br><br>
        Kindly apply for the <strong>"Embedded Systems for EV"</strong> internship on the VTU portal. The location will show as <strong>Belagavi</strong>, but please note that the actual location will be either <strong>Bangalore</strong> or <strong>Online</strong>.
        <br><br>
        Once you have applied and updated your preference, please inform us, and we will release your offer letter. You can then proceed with the payment and acceptance process.
        <br><br>
        To apply, follow these steps:
        <br><br>
        -> Enter the URL (<a href="https://vtu.internyet.in">https://vtu.internyet.in</a>)
        <br>
        -> Login using your username and password
        <br>
        -> Navigate to the `Applied Internships` section in the dashboard
        <br>
        -> Apply for the "Embedded Systems for EV" internship.
        <br><br>
        After applying, please let us know, and we will release the offer letter.
        <br><br>
        Once the offer is released, you can proceed with paying the internship acceptance fees in the VTU portal and accept the offer.
        <br><br>
        <strong>Application Form Link:</strong> <a href="https://visionastraa.com/ev-internship-application.html">https://visionastraa.com/ev-internship-application.html</a>
        <br><br>
        To ensure a smooth onboarding process, it is essential that you join the WhatsApp group:
        <br>
        - <strong>January 2026</strong>, please join: <a href="https://chat.whatsapp.com/E1ghfP3cstjDphOQEpTB7x?mode=ems_wa_t">January 2026 Internship Group</a>
        <br><br>
        For any queries, reach out to us on LinkedIn: <a href="https://in.linkedin.com/company/va-ev-academy">https://in.linkedin.com/company/va-ev-academy</a>
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
        if len(df.columns) < 8:
            df.columns = ['Name', 'Email', 'Phone', 'College', 'Specialization', 'Role', 'Status', 'EmailSent']
        else:
            print("CSV file has an unexpected number of columns. Processing the first 8.")
            df = df.iloc[:, :8]
            df.columns = ['Name', 'Email', 'Phone', 'College', 'Specialization', 'Role', 'Status', 'EmailSent']


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
