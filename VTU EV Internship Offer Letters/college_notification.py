import pandas as pd
import os
import smtplib
import ssl
import sys
import argparse
import math
import mysql.connector
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formataddr

# ---------------- CONFIG ----------------
# Database Credentials
# NOTE: If running from GitHub Actions or local PC, change 'localhost' to your Hostinger Remote SQL IP/Host
DB_HOST = 'localhost' 
DB_USER = 'u707137586_User_Data'
DB_PASSWORD = 'C*&7Ua]X$k7h'
DB_NAME = 'u707137586_User_Data'
TABLE_NAME = 'karnataka_college_details'

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Internship Opportunities for Students at VisionAstraa EV Academy (VTU InternYet Portal)'

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

# Mapping of Friendly Role Name -> Database Column Name
ROLE_COLUMN_MAP = {
    "Principal": "Principal Email",
    "Placement Officer": "tpo_email_id",
    "HOD - ECE": "ECE HOD Email",
    "HOD - EEE": "EEE HOD Email",
    "HOD - Mechanical": "Mech HOD Email",
    "HOD - CSE": "CSE HOD Email",
    "HOD - AI & Data Science": "AI Data Science HOD Email",
    "HOD - AIML": "AIML HOD Email",
    "HOD - ISE": "ISE HOD Email",
    "HOD - IPE": "IPE HOD Mail",
    "HOD - DSE": "DSE HOD Email",
    "HOD - Cybersecurity": "Cybersecurity HOD Email"
}

def get_db_connection():
    try:
        connection = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        return connection
    except mysql.connector.Error as err:
        print(f"Error connecting to database: {err}")
        return None

def fetch_data_from_db():
    """Fetches only records where email_sent_flag is 0 or NULL."""
    conn = get_db_connection()
    if conn is None:
        return None
    
    # Filter for pending emails only
    query = f"SELECT * FROM {TABLE_NAME} WHERE email_sent_flag = 0 OR email_sent_flag IS NULL"
    try:
        # Use pandas to read sql directly into a DataFrame
        df = pd.read_sql(query, conn)
        conn.close()
        return df
    except Exception as e:
        print(f"Error fetching data: {e}")
        if conn.is_connected():
            conn.close()
        return None

def update_email_sent_flag(college_name):
    """Updates the email_sent_flag to 1 for the given college name."""
    conn = get_db_connection()
    if conn is None:
        return False
    
    try:
        cursor = conn.cursor()
        # Using parameterized query to handle special characters in names safely
        sql = f"UPDATE {TABLE_NAME} SET email_sent_flag = 1 WHERE Name = %s"
        cursor.execute(sql, (college_name,))
        conn.commit()
        rows_affected = cursor.rowcount
        cursor.close()
        conn.close()
        if rows_affected > 0:
            print(f"  [DB] Updated email_sent_flag for: {college_name}")
            return True
        else:
            print(f"  [DB Warning] No rows updated for: {college_name}")
            return False
    except Exception as e:
        print(f"  [DB Error] Failed to update flag for {college_name}: {e}")
        if conn.is_connected():
            conn.close()
        return False

def send_college_notification_email(sender_email, sender_password, recipient_name, to_email):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        # Constructing the HTML Body
        body = f"""
        Dear {recipient_name},
        <br><br>

        Greetings from VisionAstraa EV Academy!
        <br><br>

        We are writing to inform you that VisionAstraa EV Academy is offering internship opportunities which are registered on the <strong>VTU InternYet Portal</strong>.
        <br><br>
        We request you to kindly ask your <strong>Internship Coordinators and Placement Officers</strong> to pass these details to your students as well.
        <br><br>

        <strong><u>Internship Courses Available:</u></strong>
        <br><br>

        <strong>For CSE and Allied students:</strong>
        <ul>
            <li>
                <strong>AI/ML for Electric Vehicle</strong><br>
                <a href="https://vtu.internyet.in/internships/365-aiml-for-electric-vehicle-data-science-cyber-security-machine-learning-data-analytics-full-stack-development-artificial-intelligence">Click here to view details</a>
            </li>
            <li>
                <strong>Full Stack Development for Electric Vehicle</strong><br>
                <a href="https://vtu.internyet.in/internships/365-full-stack-development-for-electric-vehicle">Click here to view details</a>
            </li>
            <li>
                <strong>Web Development for Electric Vehicle</strong><br>
                <a href="https://vtu.internyet.in/internships/365-web-development-for-electric-vehicle">Click here to view details</a>
            </li>
            <li>
                <strong>Data Science for Electric Vehicle</strong><br>
                <a href="https://vtu.internyet.in/internships/365-data-science-for-electric-vehicle">Click here to view details</a>
            </li>
        </ul>

        <strong>For ECE/EEE and Allied students:</strong>
        <ul>
            <li>
                <strong>Embedded Systems for Electric Vehicle</strong><br>
                <a href="https://vtu.internyet.in/internships/365-embedded-systems-for-electric-vehicle-microcontrollers-iot-mechatronics-adas">Click here to view details</a>
            </li>
        </ul>

        <strong>For Mechanical and Allied students:</strong>
        <ul>
            <li>
                <strong>Design and Development of Electric Vehicle</strong><br>
                <a href="https://vtu.internyet.in/internships/365-design-development-of-electric-vehicle-mechanical-mechatronics-automobile">Click here to view details</a>
            </li>
        </ul>
        <br>

        <strong>Mode of Internship:</strong><br>
        Interns have the option to choose between:
        <ul>
            <li>Fully-online</li>
            <li>or, fully-offline (Bangalore/Belagavi center) mode of internship</li>
        </ul>
        <br>

        For more information regarding Internship modules, please visit our internship page: 
        <a href="https://visionastraa.com/ev-projects.html">https://visionastraa.com/ev-projects.html</a>
        <br><br>

        Best Regards,<br>
        <strong>VisionAstraa EV Academy</strong><br>
        <a href="https://visionastraa.com">www.visionastraa.com</a>
        """

        msg.attach(MIMEText(body, 'html'))

        context = ssl.create_default_context()
        with smtplib.SMTP_SSL(SMTP_SERVER, SMTP_PORT, context=context) as server:
            server.login(sender_email, sender_password)
            server.sendmail(sender_email, to_email, msg.as_string())

        print(f"SENT to: {recipient_name} <{to_email}>")
        return True

    except smtplib.SMTPException as e:
        print(f"SMTP Error sending email to {recipient_name}: {e}")
        return False
    except Exception as e:
        print(f"An unexpected error occurred while sending email to {recipient_name}: {e}")
        return False


def main():
    # 1. Parse Arguments
    parser = argparse.ArgumentParser(description='Send college notification emails in batches from SQL.')
    parser.add_argument('--batch', type=int, choices=[1, 2], required=True, help='Batch number (1 or 2)')
    args = parser.parse_args()
    batch_num = args.batch

    # 2. Get Credentials for this Batch
    creds = BATCH_CREDENTIALS.get(batch_num)
    sender_email = creds["EMAIL"]
    sender_password = creds["PASSWORD"]

    print("Connecting to database and fetching PENDING records (email_sent_flag = 0)...")
    df = fetch_data_from_db()
    
    if df is None or df.empty:
        print("No pending records found or database connection failed.")
        return

    # 3. Split Data based on Batch (Splitting by College Row)
    total_records = len(df)
    mid_point = math.ceil(total_records / 2)

    if batch_num == 1:
        df_batch = df.iloc[:mid_point]
        print(f"--- BATCH 1 STARTING ({sender_email}) ---")
        print(f"Processing Colleges 1 to {mid_point} (Total colleges assigned: {len(df_batch)})")
    else:
        df_batch = df.iloc[mid_point:]
        print(f"--- BATCH 2 STARTING ({sender_email}) ---")
        print(f"Processing Colleges {mid_point + 1} to {total_records} (Total colleges assigned: {len(df_batch)})")
    
    print("-" * 30)

    # 4. Iterate Colleges and Send Emails
    print("\nSending college notification emails...")
    
    total_emails_sent_count = 0
    
    for index, row in df_batch.iterrows():
        college_name = str(row['Name']).strip()
        if not college_name or college_name.lower() == 'nan':
            college_name = "College Authority"
        
        college_emails_successful = 0

        # Iterate through defined roles (Principal, HODs, TPO)
        for role_title, col_name in ROLE_COLUMN_MAP.items():
            # Check if column exists in the dataframe
            if col_name in df.columns:
                email = str(row[col_name]).strip()
                
                # Check if email is valid
                if email and email.lower() != 'nan' and email.lower() != 'none' and '@' in email:
                    
                    # Create specific salutation
                    recipient_name = f"{role_title}, {college_name}"
                    
                    # Send Email
                    success = send_college_notification_email(sender_email, sender_password, recipient_name, email)
                    if success:
                        college_emails_successful += 1
                        total_emails_sent_count += 1
        
        # If at least one email was sent for this college, update the flag in DB
        if college_emails_successful > 0:
            update_email_sent_flag(college_name)

    print(f"\nBatch {batch_num} complete. Total emails sent in this batch: {total_emails_sent_count}")

if __name__ == "__main__":
    main()