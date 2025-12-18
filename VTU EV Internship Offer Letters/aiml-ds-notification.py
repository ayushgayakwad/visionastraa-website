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
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/jan_aiml_ds_or_1.csv'
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Know your Internship before you accept the offer | VisionAstraa EV Academy'

# --- HARDCODED CREDENTIALS (UPDATE THESE) ---
# Since this is a private repo, we hardcode them here to avoid GitHub Secrets
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

def send_internship_details_email(sender_email, sender_password, name, to_email, role, date_str):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        # --- Curriculum Sections Definition ---
        aiml_curriculum = """
        <strong>Internship Curriculum Overview</strong><br>
        <strong>For AIML Internship Track</strong><br><br>
        <strong>Module 1 – EV Foundations</strong><br>
        EV fundamentals, Battery Management Systems (BMS), traction motors, and charging infrastructure<br><br>
        <strong>Module 2 – ML for EV Powertrain</strong><br>
        Machine learning, data analytics, and feature engineering for powertrain optimization<br><br>
        <strong>Module 3 – Advanced AI Models</strong><br>
        Artificial Neural Networks (ANN), Convolutional Neural Networks (CNN), and Kalman Filters<br><br>
        <strong>Module 4 – Reinforcement Learning & Maintenance</strong><br>
        Reinforcement learning concepts, state–agent–reward–policy framework, and predictive maintenance<br><br>
        <strong>Module 5 – Gen AI & EV Security</strong><br>
        Generative AI for EV design, cybersecurity for EV systems, and anomaly detection<br><br>
        <strong>Module 6 – Practical Applications</strong><br>
        Thermal prediction, fault diagnosis, motor control strategies, and driving pattern analysis<br><br>
        """

        ds_curriculum = """
        <strong>Internship Curriculum Overview</strong><br>
        <strong>For Data Science Internship Track</strong><br><br>
        <strong>Module 1 – EV Data Sources</strong><br>
        EV fundamentals, BMS, motors, and charging infrastructure as primary data sources<br><br>
        <strong>Module 2 – Data Analytics & ML</strong><br>
        Exploratory data analysis, feature selection, and ML models for powertrain efficiency<br><br>
        <strong>Module 3 – Deep Learning Models</strong><br>
        Building ANN and CNN models, implementing Kalman Filters for vehicle state tracking<br><br>
        <strong>Module 4 – Intelligent Decision Systems</strong><br>
        Reinforcement learning for autonomous decision-making and predictive maintenance alerts<br><br>
        <strong>Module 5 – Generative AI & Cybersecurity</strong><br>
        Generative AI for component design optimization and cybersecurity via anomaly detection<br><br>
        <strong>Module 6 – Case Studies & Use Cases</strong><br>
        Thermal behavior monitoring, system fault diagnosis, motor control refinement, and driver behavior analysis<br><br>
        """

        # --- Logic to Select Curriculum ---
        curriculum_section = ""
        clean_role = role.strip()
        
        # Exact strings as provided by user
        aiml_roles = [
            "AI/ML FOR ELECTRIC VEHICLE ( Data Science , Cyber Security , Machine Learning , Data Analytics , Full Stack Development , Artificial intelligence )",
            "AI/ML FOR EV ( Data Science , Cybersecurity , Machine Learning , Data Analytics , Full Stack Development , Artificial intelligence )"
        ]
        
        ds_roles = [
            "Data Science for Electric Vehicle",
            "Data Science for EV"
        ]

        if clean_role in aiml_roles:
            curriculum_section = aiml_curriculum
        elif clean_role in ds_roles:
            curriculum_section = ds_curriculum
        
        # --- Construct Email Body ---
        body = f"""
        Hello {name},
        <br><br>
        We hope you are doing well. Before you proceed with accepting your internship offer, we would like to clearly explain what the {role} Internship at VisionAstraa EV Academy is about, so you can make an informed decision with complete clarity.
        <br><br>
        
        🔍 <strong>About the Internship</strong><br>
        This internship is a structured, learning-driven and industry-aligned program focused on the application of Artificial Intelligence, Machine Learning, and Data Science in Electric Vehicles (EVs).
        <br><br>
        
        🔹 <strong>This is a fully software-based internship.</strong><br>
        There is no hardware handling or physical EV work involved. All learning, projects, and case studies are conducted using software tools, simulations, datasets, and AI/ML models related to EV systems.
        <br><br>
        You will work on real-world EV datasets and problem statements involving battery systems, motors, charging infrastructure, powertrain optimization, predictive maintenance, and intelligent decision-making.
        <br><br>
        
        🔹 <strong>Internship Certificate Uniformity</strong><br>
        Please note that the internship certificate issued by VisionAstraa EV Academy will be the same for all interns, irrespective of the center, city, or location mentioned on the VTU Portal. The curriculum, evaluation, and certification standards remain uniform across all locations.
        <br><br>

        {curriculum_section}

        <strong>What You Gain from This Internship</strong>
        <ul>
        <li>Fully software-based exposure to AI, ML, and Data Science in EVs</li>
        <li>Hands-on experience with industry-relevant datasets and projects</li>
        <li>Career-aligned skills for EV, AI, and Data Science roles</li>
        <li>Uniform internship certificate issued by VisionAstraa EV Academy (independent of location)</li>
        </ul>
        <br>

        <strong>Need Help or Clarification?</strong><br>
        <strong>Connect with us on LinkedIn:</strong><br>
        <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
        <strong>OR call us at: <a href="tel:+918762246518">+91 87622 46518</a></strong>
        <br><br>
        We encourage you to review the internship details carefully and proceed only if the program aligns with your career goals.
        <br><br>
        Warm regards,<br>
        <strong>VisionAstraa EV Academy</strong>
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

    if "replace" in sender_email or "replace" in sender_password:
        print(f"⚠️ WARNING: You have not updated the hardcoded credentials for Batch {batch_num} in the script yet!")

    if not os.path.exists(CSV_FILE_PATH):
        print(f"Error: Input data file not found at '{CSV_FILE_PATH}'")
        return

    try:
        # 3. Load Data
        df = pd.read_csv(CSV_FILE_PATH)
        
        # We assume the file has headers or we infer them. 
        # Structure assumed: ID, Name, Email, Role, Date, Status, EmailSent
        
        if len(df.columns) >= 5:
            # Map columns by index to ensure correct data retrieval regardless of header names
            df_clean = pd.DataFrame()
            df_clean['Name'] = df.iloc[:, 1]  # 2nd col
            df_clean['Email'] = df.iloc[:, 2] # 3rd col
            df_clean['Role'] = df.iloc[:, 3]  # 4th col
            df_clean['Date'] = df.iloc[:, 4]  # 5th col
            
            # Check for EmailSent column (Index 6 / 7th column)
            if len(df.columns) > 6:
                df_clean['EmailSent'] = df.iloc[:, 6]
            else:
                # If column doesn't exist, assume not sent
                df_clean['EmailSent'] = 'FALSE' 
        else:
            print("CSV file format not recognized. Expecting at least 5 columns.")
            return

        # 4. Split Data based on Batch
        total_records = len(df_clean)
        mid_point = math.ceil(total_records / 2)

        if batch_num == 1:
            df_batch = df_clean.iloc[:mid_point]
            print(f"--- BATCH 1 STARTING ({sender_email}) ---")
            print(f"Processing records 1 to {mid_point} (Total rows assigned: {len(df_batch)})")
        else:
            df_batch = df_clean.iloc[mid_point:]
            print(f"--- BATCH 2 STARTING ({sender_email}) ---")
            print(f"Processing records {mid_point + 1} to {total_records} (Total rows assigned: {len(df_batch)})")

        print("-" * 30)

        # 5. Send Emails
        for index, row in df_batch.iterrows():
            name = str(row['Name']).strip()
            email = str(row['Email']).strip()
            role = str(row['Role']).strip()
            date_str = str(row['Date']).strip()
            email_sent_status = str(row['EmailSent']).strip().upper()

            # Skip if EmailSent is TRUE
            if email_sent_status == 'TRUE':
                print(f"Skipping {name}: Email already sent (Status: TRUE)")
                continue

            if name and email and role and name.lower() != 'nan' and email.lower() != 'nan':
                send_internship_details_email(sender_email, sender_password, name, email, role, date_str)
            else:
                print(f"Skipping row due to missing data: {name}, {email}")
        
        print(f"\nBatch {batch_num} complete.")

    except Exception as e:
        print(f"An unexpected error occurred in main execution: {e}")


if __name__ == "__main__":
    main()