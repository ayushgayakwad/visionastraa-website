import random
import pandas as pd
import fitz
from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.platypus import Paragraph
import os
import io
import smtplib
import ssl
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.base import MIMEBase
from email import encoders
import argparse
import math

# ---------------- CONFIG ----------------
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_offer_released_applicants_41.csv'
PDF_TEMPLATE_PATH = 'VTU EV Internship Offer Letters/Template.pdf'
OUTPUT_DIRECTORY = 'VTU EV Internship Offer Letters/Generated_Offer_Letters'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Internship OFFER LETTER from VisionAstraa EV Academy'

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

def get_letter_paragraph(name, role):
    styles = getSampleStyleSheet()
    style = styles['BodyText']
    style.fontName = 'Times-Roman'
    style.fontSize = 14
    style.leading = 18

    text = f"""
    Dear <b>{name}</b>,<br/><br/>
    We are pleased to offer you an Internship in <b>{role}</b> at <b>VisionAstraa EV Academy</b> with effect from January 2026. We are excited to welcome you onboard.<br/><br/>
    As an Intern, you will be part of a collaborative environment where you will learn and contribute to impactful projects in the electric vehicle (EV) domain. You will gain hands-on experience and have the opportunity to apply your academic knowledge to real-world applications while developing practical skills that will strengthen your career prospects.<br/><br/>
    At <b>VisionAstraa EV Academy</b>, we are committed to a supportive and enriching learning environment. Our aim is to empower you with relevant, hands-on experience and to support your growth in the EV industry.<br/><br/>
    We look forward to working with you and hope this internship will be a valuable step in your professional journey.
    """
    return Paragraph(text, style)

def create_offer_letter(name, role):
    try:
        packet = io.BytesIO()
        c = canvas.Canvas(packet, pagesize=letter)

        page_width, page_height = letter

        p = get_letter_paragraph(name, role)

        text_width = 520
        w, h = p.wrapOn(c, text_width, 400)

        top_margin = page_height * 0.775
        y_position = top_margin - h

        left_margin = 45
        p.drawOn(c, left_margin, y_position)
        c.save()

        packet.seek(0)
        text_pdf = fitz.open(stream=packet, filetype="pdf")
        
        if not os.path.exists(PDF_TEMPLATE_PATH):
            print(f"Error: Template not found at {PDF_TEMPLATE_PATH}")
            return None
            
        template_pdf = fitz.open(PDF_TEMPLATE_PATH)

        template_page = template_pdf[0]
        template_page.show_pdf_page(template_page.rect, text_pdf, 0)

        random_number = random.randint(0, 100)

        safe_filename = "".join(
            [c for c in name if c.isalpha() or c.isdigit() or c == ' ']).rstrip()
        output_filepath = os.path.join(
            OUTPUT_DIRECTORY, f"Offer_Letter_{safe_filename}_{random_number}.pdf")

        template_pdf.save(output_filepath)
        template_pdf.close()

        print(f"Successfully created offer letter for: {name}")
        return output_filepath

    except Exception as e:
        print(f"Error creating PDF for {name}: {e}")
        return None

def send_email(sender_email, sender_password, name, to_email, role, attachment_path):
    try:
        msg = MIMEMultipart()
        msg['From'] = sender_email
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        body = f"""
        Hello {name},
        <br><br>
        <strong>CONGRATULATIONS</strong> for getting selected for Internship in <strong>{role}</strong> and your starting date is <strong>January, 2026</strong>. 
        <br><br>
        <strong>Kindly accept the offer in the VTU Portal by paying the internship acceptance fees and follow the steps below:</strong>
        <br><br>
        -> Enter the URL (<a href="https://vtu.internyet.in">https://vtu.internyet.in</a>)
        <br>
        -> Login using your username and password
        <br>
        -> Navigate to `Applied Internships` section in the dashboard
        <br>
        -> Click on the `Accept` button to accept <strong>VisionAstraa EV Academy's offer</strong>.
        <br><br>
        <strong>Please note: Kindly accept this offer within 5 days from the date of this email. Offers not accepted within this period will be considered expired</strong>.
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
        - <strong>January 2026</strong>, please join: <a href="https://chat.whatsapp.com/JJc51uchsDpHKPdRRA091Q?mode=hqrt3">January 2026 Internship Group</a>
        <br><br>
        Below are the benefits of joining VisionAstraa EV Academy (<a href="https://visionastraa.com/ev-projects.html">https://visionastraa.com/ev-projects.html</a>):
        <br>
        ▶ Top 10% to get PPOs from EV companies
        <br>
        ▶ 70% Hands-on/Lab, formal presentations
        <br>
        ▶ Capstone projects in the EV domain
        <br>
        ▶ Scholarship for the best project
        <br>
        ▶ Internship completion certificate
        <br>
        ▶ Best projects get funding and mentorship to register as startups
        <br><br>
        For any queries reach out to us on LinkedIn: <a href="https://in.linkedin.com/company/va-ev-academy">https://in.linkedin.com/company/va-ev-academy</a>
        <br>
        Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a>
        <br>
        <strong>OR, call us on: <a href="tel:+918762246518">+91 87622 46518</a></strong>
        <br><br>
        Find your offer letter attached below.
        <br><br>
        Looking forward to having you onboard!
        <br><br>
        Happy Interning!
        """
        msg.attach(MIMEText(body, 'html'))

        with open(attachment_path, "rb") as attachment:
            part = MIMEBase('application', 'octet-stream')
            part.set_payload(attachment.read())
        encoders.encode_base64(part)
        part.add_header(
            'Content-Disposition',
            f"attachment; filename= {os.path.basename(attachment_path)}",
        )
        msg.attach(part)

        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT, context=context) as server:
            server.login(sender_email, sender_password)
            server.sendmail(sender_email, to_email, msg.as_string())

        print(f"Successfully sent email to: {name} at {to_email}")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending email to {name}: {e}")
        return False
    except Exception as e:
        print(
            f"An unexpected error occurred while sending email to {name}: {e}")
        return False

def main():
    # 1. Parse Arguments
    parser = argparse.ArgumentParser(description='Send Offer Letters in batches.')
    parser.add_argument('--batch', type=int, choices=[1, 2], required=True, help='Batch number (1 or 2)')
    args = parser.parse_args()
    batch_num = args.batch

    # 2. Get Credentials for this Batch
    creds = BATCH_CREDENTIALS.get(batch_num)
    sender_email = creds["EMAIL"]
    sender_password = creds["PASSWORD"]

    if not os.path.exists(CSV_FILE_PATH):
        print(f"Error: Input data file not found at '{CSV_FILE_PATH}'")
        return
    if not os.path.exists(PDF_TEMPLATE_PATH):
        print(f"Error: PDF template not found at '{PDF_TEMPLATE_PATH}'")
        return

    if not os.path.exists(OUTPUT_DIRECTORY):
        os.makedirs(OUTPUT_DIRECTORY)
        print(f"Created output directory: '{OUTPUT_DIRECTORY}'")

    try:
        # 3. Load Data
        df = pd.read_csv(CSV_FILE_PATH, header=None)
        if len(df.columns) < 8:
            print("Error: CSV file does not have the expected 8 columns.")
            return

        df.columns = ['Name', 'Email', 'Phone', 'College', 'Specialization', 'Role', 'Status', 'EmailSent']
        
        # 4. Split Data based on Batch
        total_records = len(df)
        mid_point = math.ceil(total_records / 2)

        if batch_num == 1:
            df_batch = df.iloc[:mid_point].copy()
            print(f"--- BATCH 1 STARTING ({sender_email}) ---")
            print(f"Processing records 1 to {mid_point} (Total rows assigned: {len(df_batch)})")
        else:
            df_batch = df.iloc[mid_point:].copy()
            print(f"--- BATCH 2 STARTING ({sender_email}) ---")
            print(f"Processing records {mid_point + 1} to {total_records} (Total rows assigned: {len(df_batch)})")
        
        print("-" * 30)

        # 5. Process Batch
        for index, row in df_batch.iterrows():
            if str(row['EmailSent']).strip().upper() == 'FALSE':
                name = str(row['Name']).strip()
                email = str(row['Email']).strip()
                role = str(row['Role']).strip()

                if name and email and role and name.lower() != 'nan' and email.lower() != 'nan' and role.lower() != 'nan':
                    offer_letter_path = create_offer_letter(name, role)
                    if offer_letter_path:
                        email_sent = send_email(
                            sender_email, sender_password, name, email, role, offer_letter_path)
                        if email_sent:
                            df_batch.loc[index, 'EmailSent'] = 'TRUE'
                else:
                    print(
                        f"Skipping row due to missing name, email, or role.")
            else:
                 print(f"Skipping {row['Name']}: Email already sent.")

        print(f"\nBatch {batch_num} complete.")

    except Exception as e:
        print(f"An unexpected error occurred: {e}")

if __name__ == "__main__":
    main()
