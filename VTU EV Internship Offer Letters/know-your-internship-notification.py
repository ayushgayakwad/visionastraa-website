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
CSV_FILE_PATH = 'VTU EV Internship Offer Letters/know_your_internship_6.csv'
SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 465
EMAIL_SUBJECT = 'Know Your Internship | VisionAstraa EV Academy'

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

# BATCH_CREDENTIALS = {
#      1: {
#          "EMAIL": "careers@visionastraa.in",
#          "PASSWORD": "Z1SIOO0A9b~"
#      },
#      2: {
#          "EMAIL": "visionastraa@evcourse.in",
#          "PASSWORD": ">p>W|jv?Kg1"
#      }
# }

def send_internship_details_email(sender_email, sender_password, name, to_email, role, date_str):
    try:
        msg = MIMEMultipart()
        msg['From'] = formataddr(("VisionAstraa EV Academy", sender_email))
        msg['To'] = to_email
        msg['Subject'] = EMAIL_SUBJECT

        # --- Curriculum Sections Definition ---
        aiml_curriculum = """
        🔹 <strong>Internship Plan</strong><br><br>
        <strong>Machine Learning</strong><br>
        Machine learning, data analytics, and feature engineering for powertrain optimization<br><br>
        <strong>Advanced AI Models</strong><br>
        Artificial Neural Networks (ANN), Convolutional Neural Networks (CNN), and Kalman Filters<br><br>
        <strong>Reinforcement Learning & Maintenance</strong><br>
        Reinforcement learning concepts, state–agent–reward–policy framework, and predictive maintenance<br><br>
        <strong>Gen AI & EV Security</strong><br>
        Generative AI for EV design, cybersecurity for EV systems, and anomaly detection<br><br>
        <strong>Practical Applications</strong><br>
        Thermal prediction, fault diagnosis, motor control strategies, and driving pattern analysis<br><br>
        """

        ds_curriculum = """
        🔹 <strong>Internship Plan</strong><br><br>
        <strong>Data Analytics & ML</strong><br>
        Exploratory data analysis, feature selection, and ML models for powertrain efficiency<br><br>
        <strong>Deep Learning Models</strong><br>
        Building ANN and CNN models, implementing Kalman Filters for vehicle state tracking<br><br>
        <strong>Intelligent Decision Systems</strong><br>
        Reinforcement learning for autonomous decision-making and predictive maintenance alerts<br><br>
        <strong>Generative AI & Cybersecurity</strong><br>
        Generative AI for component design optimization and cybersecurity via anomaly detection<br><br>
        <strong>Case Studies & Use Cases</strong><br>
        Thermal behavior monitoring, system fault diagnosis, motor control refinement, and driver behavior analysis<br><br>
        """

        web_curriculum = """
        🔹 <strong>Internship Plan</strong><br><br>
        <strong>Frontend Fundamentals</strong><br>
        Building responsive user interfaces for EV dashboards and information portals using HTML5, CSS3, and modern JavaScript (ES6+)<br><br>
        <strong>Dynamic UI with Frontend Frameworks</strong><br>
        Developing interactive components such as charging station locators and vehicle status displays using modern frontend frameworks like React<br><br>
        <strong>Backend Development with Node.js</strong><br>
        Creating RESTful APIs using Node.js and Express to manage user data, vehicle information, and charging session details<br><br>
        <strong>Database Management for Vehicle Data</strong><br>
        Designing and managing NoSQL (MongoDB) or SQL (PostgreSQL) databases to store user profiles, telematics data, and service history<br><br>
        <strong>API Integration & Real-time Data</strong><br>
        Integrating third-party services such as mapping APIs and implementing WebSockets to visualize live EV data<br><br>
        <strong>Deployment & Cloud Services</strong><br>
        Deploying and maintaining EV web applications on cloud platforms like AWS, GCP, Azure, or Vercel, with an introduction to serverless architecture<br><br>
        """

        fullstack_curriculum = """
        🔹 <strong>Internship Plan</strong><br><br>
        <strong>Full Stack EV Application Architecture</strong><br>
        Introduction to the MERN stack (MongoDB, Express.js, React, Node.js) and client–server architecture for EV management systems<br><br>
        <strong>Advanced Frontend</strong><br>
        Building complex, state-managed dashboards for real-time battery monitoring, trip planning, and remote vehicle controls using advanced React concepts<br><br>
        <strong>Secure Backend & API Development</strong><br>
        Developing secure and scalable backends for EV telematics, user authentication (JWT), and charging infrastructure communication<br><br>
        <strong>Real-time Communication & IoT Protocols</strong><br>
        Implementing WebSockets and MQTT for real-time, bi-directional data streaming between vehicle systems, servers, and client interfaces<br><br>
        <strong>Cloud Integration & Database Modeling</strong><br>
        Advanced database schema design for fleet management and cloud service integration (e.g., AWS S3) for vehicle logs and user data<br><br>
        <strong>DevOps, Containerization & Deployment</strong><br>
        Setting up CI/CD pipelines, containerizing applications using Docker, and deploying scalable, production-ready EV software solutions<br><br>
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

        web_roles = [
            "Web Development for Electric Vehicle",
            "Web Development for EV"
        ]

        fullstack_roles = [
            "Full Stack Development for Electric Vehicle",
            "Full Stack Development for EV"
        ]

        if clean_role in aiml_roles:
            curriculum_section = aiml_curriculum
        elif clean_role in ds_roles:
            curriculum_section = ds_curriculum
        elif clean_role in web_roles:
            curriculum_section = web_curriculum
        elif clean_role in fullstack_roles:
            curriculum_section = fullstack_curriculum
        

        # OFFER RELEASED APPLICANTS

        # body = f"""
        # Hello {name},
        # <br><br>
        # We hope you are doing well. Before you proceed with accepting your internship offer, we would like to clearly explain what the <strong><u>{role}</u></strong> Internship at VisionAstraa EV Academy is about, so you can make an informed decision with complete clarity.
        # <br><br>
        
        # 🔍 <strong>About the Internship</strong><br>
        
        # 🔹 <strong>This is a fully software-based internship.</strong><br>
        # There is no hardware handling or physical EV work involved. All learning, projects, and case studies are conducted using software tools, simulations, datasets, and AI/ML models related to EV systems.
        # <br><br>
        # You will work on real-world EV datasets.
        # <br><br>
        
        # 🔹 <strong>Internship Certificate Uniformity</strong><br>
        # Please note that the internship certificate issued by VisionAstraa EV Academy will be the same for all interns, irrespective of the center, city, or location mentioned on the VTU Portal.
        # <br><br>
        # 🔹 <strong>Interns have the option to choose between:</strong>
        # <ul>
        # <li>Fully-online</li>
        # <li>or, fully-offline (Bangalore/Belagavi) mode of internship</li>
        # </ul>

        # 🔹 <strong>Internship Duration</strong><br>
        # <ul>
        # <li>Internship will commence from January (after the conclusion of 7th Semester examinations)</li>
        # <li>Internship includes 1 month of training and 3 months of project work</li>
        # <li>Internship certificates will be issued after the conclusion of internship</li>
        # </ul>
        # <br><br>

        # {curriculum_section}

        # 🔹 <strong>What You Gain from This Internship</strong>
        # <ul>
        # <li>Fully software-based exposure in EVs</li>
        # <li>Hands-on experience with industry-relevant datasets and projects</li>
        # <li>Career-aligned skills software-based roles</li>
        # <li>Uniform internship certificate issued by VisionAstraa EV Academy (independent of location)</li>
        # </ul>
        # <br>

        # <strong>Need Help or Clarification?</strong><br>
        # <strong>Connect with us on LinkedIn:</strong><br>
        # <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        # Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
        # <strong>OR, if you are really interested in joining VisionAstraa EV Academy then call us on: <a href="tel:+918762246518">+91 87622 46518</a> or <a href="tel:+918075664438">+91 80756 64438</a></strong>
        # <br><br>
        # We are here to support you through the onboarding process and ensure a smooth internship experience.  
        # <br><br>
        # Happy Coding!
        # <br><br>
        # Warm regards,<br>
        # <strong>VisionAstraa EV Academy</strong>
        # """


        # SHORTLISTED APPLICANTS

        body = f"""
        Hello {name},
        <br><br>
        We are pleased to inform you that you have been shortlisted for the <strong>{role}</strong> Internship at VisionAstraa EV Academy.
        <br><br>

        If you are shortlisted and have not yet received an internship offer from us, we request you to contact us at the earliest. Upon confirmation, we will proceed with releasing your internship offer.
        <br><br>
        
        🔍 <strong>About the Internship</strong><br>
        
        🔹 <strong>This is a fully software-based internship.</strong><br>
        There is no hardware handling or physical EV work involved. All learning, projects, and case studies are conducted using software tools, simulations, datasets, and AI/ML models related to EV systems.
        <br><br>
        You will work on real-world EV datasets.
        <br><br>
        
        🔹 <strong>Internship Certificate Uniformity</strong><br>
        Please note that the internship certificate issued by VisionAstraa EV Academy will be the same for all interns, irrespective of the center, city, or location mentioned on the VTU Portal.
        <br><br>
        🔹 <strong>Interns have the option to choose between:</strong>
        <ul>
        <li>Fully-online</li>
        <li>or, fully-offline (Bangalore/Belagavi) mode of internship</li>
        </ul>

        🔹 <strong>Internship Duration</strong><br>
        <ul>
        <li>Internship will commence from January (after the conclusion of 7th Semester examinations)</li>
        <li>Internship includes 1 month of training and 3 months of project work</li>
        <li>Internship certificates will be issued after the conclusion of internship</li>
        </ul>
        <br><br>

        {curriculum_section}

        🔹 <strong>What You Gain from This Internship</strong>
        <ul>
        <li>Fully software-based exposure in EVs</li>
        <li>Hands-on experience with industry-relevant datasets and projects</li>
        <li>Career-aligned skills software-based roles</li>
        <li>Uniform internship certificate issued by VisionAstraa EV Academy (independent of location)</li>
        </ul>
        <br>

        <strong>Next Step (Important)</strong><br>
        If you are shortlisted and have not received an offer yet, please reach out to us immediately using the contact details below so we can release your offer without delay.
        <br><br>

        <strong>Need Help or Clarification?</strong><br>
        <strong>Connect with us on LinkedIn:</strong><br>
        <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
        <strong>OR, if you are really interested in joining VisionAstraa EV Academy then call us on: <a href="tel:+918762246518">+91 87622 46518</a> or <a href="tel:+918075664438">+91 80756 64438</a></strong>
        <br><br>
        We are here to support you through the onboarding process and ensure a smooth internship experience.  
        <br><br>
        Happy Coding!
        <br><br>
        Warm regards,<br>
        <strong>VisionAstraa EV Academy</strong>
        """

        # UNDER REVIEW APPLICANTS

        # body = f"""
        # Hello {name},
        # <br><br>

        # We hope you are doing well.
        # <br><br>

        # This email is to inform you that your application for the <strong>{role}</strong> Internship at VisionAstraa EV Academy is currently under review.
        # <br><br>

        # If you have not yet received an internship offer from us, we request you to contact us at the earliest. Upon confirmation, we will proceed with releasing your internship offer.
        # <br><br>
        
        # 🔍 <strong>About the Internship</strong><br>
        
        # 🔹 <strong>This is a fully software-based internship.</strong><br>
        # There is no hardware handling or physical EV work involved. All learning, projects, and case studies are conducted using software tools, simulations, datasets, and AI/ML models related to EV systems.
        # <br><br>
        # You will work on real-world EV datasets.
        # <br><br>
        
        # 🔹 <strong>Internship Certificate Uniformity</strong><br>
        # Please note that the internship certificate issued by VisionAstraa EV Academy will be the same for all interns, irrespective of the center, city, or location mentioned on the VTU Portal.
        # <br><br>
        # 🔹 <strong>Interns have the option to choose between:</strong>
        # <ul>
        # <li>Fully-online</li>
        # <li>or, fully-offline (Bangalore/Belagavi center) mode of internship</li>
        # </ul>

        # 🔹 <strong>Internship Duration</strong><br>
        # <ul>
        # <li>Internship will commence from January (after the conclusion of 7th Semester examinations)</li>
        # <li>Internship includes 1 month of training and 3 months of project work</li>
        # <li>Internship certificates will be issued after the conclusion of internship</li>
        # </ul>
        # <br><br>

        # {curriculum_section}

        # 🔹 <strong>What You Gain from This Internship</strong>
        # <ul>
        # <li>Fully software-based exposure in EVs</li>
        # <li>Hands-on experience with industry-relevant datasets and projects</li>
        # <li>Career-aligned skills software-based roles</li>
        # <li>Uniform internship certificate issued by VisionAstraa EV Academy (independent of location)</li>
        # </ul>
        # <br>

        # <strong>Next Step (Important)</strong><br>
        # If you have not received an offer yet, please reach out to us immediately using the contact details below so we can release your offer without delay.
        # <br><br>

        # <strong>Need Help or Clarification?</strong><br>
        # <strong>Connect with us on LinkedIn:</strong><br>
        # <a href="https://in.linkedin.com/company/va-ev-academy">VisionAstraa EV Academy</a><br>
        # Talk to our CEO: <a href="https://in.linkedin.com/in/nikhiljaincs">Nikhil Jain C S</a><br>
        # <strong>OR, if you are really interested in joining VisionAstraa EV Academy then call us on: <a href="tel:+918762246518">+91 87622 46518</a> or <a href="tel:+918075664438">+91 80756 64438</a></strong>
        # <br><br>
        # We are here to support you through the onboarding process and ensure a smooth internship experience.  
        # <br><br>
        # Happy Coding!
        # <br><br>
        # Warm regards,<br>
        # <strong>VisionAstraa EV Academy</strong>
        # """

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