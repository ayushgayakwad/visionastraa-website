import mysql.connector
import smtplib
import time
import random
import argparse
import json
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.application import MIMEApplication
from datetime import datetime, timedelta, timezone
from urllib.parse import quote
from email.utils import formataddr

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 587

# Using the same SMTP accounts
SMTP_ACCOUNTS = [
    {
        'username': 'visionastraa@evinternships.com',
        'password': 'a[kE?V6lm7G='
    },
    {
        'username': 'visionastraa@evinternships.in',
        'password': ']9jw>Upu//Y'
    }
]

# Updated Campaign ID for Batch 3
CAMPAIGN_ID = "ev_marketing_campaign_batch3_march_2026"

EMAIL_SUBJECT = "Applications Open: Batch 3 Starts March 1st Week (100% Placement)"

EMAIL_BODY_TEMPLATE = """\
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch 3 Admission - VisionAstraa EV Academy</title>
    <style>
        body {{
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #e0f2f1; /* Very light teal background */
            color: #333;
        }}
        .container {{
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px; /* Standard rounded corners */
            overflow: hidden; /* Ensures header corners clip */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }}
        /* DISTINCT HEADER DESIGN */
        .header {{
            background-color: #004d40; /* Deep Teal */
            padding: 25px;
            text-align: center;
        }}
        .header img {{
            background-color: #ffffff;
            padding: 8px;
            border-radius: 4px;
            max-width: 140px;
        }}
        .content {{
            padding: 30px;
        }}
        /* ACADEMIC TYPOGRAPHY */
        h1 {{
            font-family: Georgia, 'Times New Roman', Times, serif; /* Serif for academic feel */
            font-size: 26px;
            margin-bottom: 20px;
            text-align: center;
            color: #004d40;
            line-height: 1.3;
        }}
        h2 {{
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-size: 20px;
            margin-bottom: 15px;
            margin-top: 25px;
            border-bottom: 2px solid #b2dfdb;
            padding-bottom: 8px;
            color: #00695c;
        }}
        p {{
            font-size: 16px;
            margin-bottom: 15px;
            color: #444;
        }}
        
        /* TIMELINE COMPONENT */
        .timeline-box {{
            background-color: #fafafa;
            border-left: 4px solid #00796b;
            padding: 20px;
            margin: 25px 0;
        }}
        .timeline-step {{
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }}
        .timeline-step:last-child {{
            margin-bottom: 0;
        }}
        .step-icon {{
            background-color: #00796b;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            text-align: center;
            line-height: 28px;
            font-weight: bold;
            font-size: 14px;
            margin-right: 12px;
            flex-shrink: 0;
        }}
        .step-content strong {{
            display: block;
            color: #004d40;
            font-size: 17px;
        }}
        
        /* GUARANTEE BADGE */
        .guarantee-badge {{
            background-color: #fff8e1; /* Light Gold */
            border: 1px dashed #ffa000;
            color: #ff6f00;
            text-align: center;
            padding: 15px;
            border-radius: 6px;
            font-weight: bold;
            margin-bottom: 20px;
        }}

        .landscape-img {{
            width: 100%;
            height: auto;
            border-radius: 6px;
            display: block;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }}

        .btn {{
            background-color: #004d40;
            color: #ffffff !important;
            padding: 15px 30px;
            text-align: center;
            border-radius: 4px;
            display: block; /* Full width button */
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            margin: 25px 0;
            letter-spacing: 0.5px;
        }}
        .btn:hover {{
            background-color: #00251a;
        }}
        
        .footer {{
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #666;
            border-top: 1px solid #eee;
        }}
        
        /* Compact Contact Strip */
        .contact-strip {{
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }}
        .contact-item {{
            display: inline-flex;
            align-items: center;
            font-weight: bold;
            color: #00796b;
            text-decoration: none;
        }}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://visionastraa.com/images/EV_Academy.png" alt="EV Academy Logo">
        </div>

        <div class="content">
            <h1>Start Your EV Career in March.<br>Get Hired by July.</h1>

            <img src="https://visionastraa.com/images/group-pic-1.jpeg" alt="Batch 2 Classroom" class="landscape-img">

            <div class="guarantee-badge">
                ★ 100% Placement Guarantee or Money Back ★
            </div>

            <p>Dear {first_name},</p>

            <p>
                The admission process for <strong>Batch 3 (March 2026)</strong> is officially open. This is a rigorous, completely offline program designed for one outcome: <strong>Your Employment.</strong>
            </p>

            <div class="timeline-box">
                <div class="timeline-step">
                    <div class="step-icon">1</div>
                    <div class="step-content">
                        <strong>March 1st Week, 2026</strong>
                        Batch Starts @ RV College of Engineering, Bengaluru.
                    </div>
                </div>
                <div class="timeline-step">
                    <div class="step-icon" style="background:transparent; color:#ccc;">|</div>
                </div>
                 <div class="timeline-step">
                    <div class="step-icon">2</div>
                    <div class="step-content">
                        <strong>March - June 2026</strong>
                        4 Months of Intensive PowerTrain Design Training.
                    </div>
                </div>
                <div class="timeline-step">
                    <div class="step-icon" style="background:transparent; color:#ccc;">|</div>
                </div>
                <div class="timeline-step">
                    <div class="step-icon">3</div>
                    <div class="step-content">
                        <strong>July 2026</strong>
                        Placement Drives & Job Offer in Hand.
                    </div>
                </div>
            </div>

            <h2>Admission Process</h2>
            <p>
                We maintain a strict quality standard. Admissions are granted only after a virtual interview to assess your aptitude and intent.
            </p>

            <a href="https://visionastraa.com/track/click.php?email={email}&target={apply}&campaign_id={campaign_id}" class="btn">APPLY FOR ADMISSION</a>

            <div class="contact-strip">
                <div class="contact-item">
                     📞 +91 80756 64438
                </div>
                <a href="https://visionastraa.com/track/click.php?email={email}&target={whatsapp}&campaign_id={campaign_id}" class="contact-item" style="color: #25D366;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/2044px-WhatsApp.svg.png" alt="WA" width="20" style="margin-right:5px;"> Chat on WhatsApp
                </a>
            </div>
        </div>

        <div class="footer">
            <p style="margin-bottom: 10px;">Connect with our CEO, Nikhil Jain C S: <a href="https://visionastraa.com/track/click.php?email={email}&target={njcs}&campaign_id={campaign_id}" style="color: #00796b;">LinkedIn</a></p>

            <div class="social-links">
                <a href="https://visionastraa.com/track/click.php?email={email}&target={linkedin}&campaign_id={campaign_id}" style="text-decoration:none; margin: 0 8px;">LinkedIn</a> |
                <a href="https://visionastraa.com/track/click.php?email={email}&target={instagram}&campaign_id={campaign_id}" style="text-decoration:none; margin: 0 8px;">Instagram</a> |
                <a href="https://visionastraa.com/track/click.php?email={email}&target={youtube}&campaign_id={campaign_id}" style="text-decoration:none; margin: 0 8px;">YouTube</a>
            </div>
            <br>
            <p style="font-size:11px; color:#999;">
              <a href="https://visionastraa.com/track/unsubscribe.php?email={email}&campaign_id={campaign_id}" style="color:#777;">Unsubscribe</a> from future updates.
            </p>
        </div>
        <img src="{image_url}" width="1" height="1" style="display:none;">
    </div>
</body>
</html>
"""

def send_email(to_address, first_name, smtp_username, smtp_password):
    # Tracking links
    vaev_linkedin = quote("https://www.linkedin.com/company/va-ev-academy", safe='')
    vaev_website = quote("https://visionastraa.com/ev-jobs.html", safe='')
    apply = quote("https://www.visionastraa.com/ev-application.html", safe='')
    whatsapp = quote("https://wa.me/918075664438", safe='')
    whatsapp_group = quote("https://chat.whatsapp.com/EhvWb9kldqI7Np2MbfCW3u", safe='')
    njcs = quote("https://in.linkedin.com/in/nikhiljaincs", safe='')
    linkedin = quote("https://in.linkedin.com/company/va-ev-academy", safe='')
    instagram = quote("https://www.instagram.com/va_ev_academy", safe='')
    youtube = quote("https://www.youtube.com/@VisionAstraaEVAcademy", safe='')
    
    random_token = random.randint(100000, 999999)
    image_url = f"https://visionastraa.com/track/open.php?email={quote(to_address)}&campaign_id={CAMPAIGN_ID}&r={random_token}"

    body = EMAIL_BODY_TEMPLATE.format(
        first_name=first_name,
        email=quote(to_address),
        campaign_id=CAMPAIGN_ID,
        image_url=image_url,
        vaev_linkedin=vaev_linkedin,
        apply=apply,
        vaev_website=vaev_website,
        whatsapp=whatsapp,
        whatsapp_group=whatsapp_group,
        njcs=njcs,
        linkedin=linkedin,
        instagram=instagram,
        youtube=youtube
    )

    msg = MIMEMultipart('mixed')
    msg['Subject'] = EMAIL_SUBJECT
    msg['From'] = formataddr(("VisionAstraa EV Academy", smtp_username))
    msg['To'] = to_address
    msg.attach(MIMEText(body, 'html'))

    try:
        with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
            server.starttls()
            server.login(smtp_username, smtp_password)
            server.sendmail(smtp_username, to_address, msg.as_string())
        return True
    except Exception as e:
        print(f"❌ Error sending to {to_address}: {e}")
        return False

def main():
    parser = argparse.ArgumentParser(description='Send email campaign in parallel.')
    parser.add_argument('--config', type=str, required=True, help='JSON string with job configuration')
    args = parser.parse_args()

    try:
        config = json.loads(args.config)
        account_index = config['account_index']
        max_emails_to_send = config['limit']
        tables = config['tables']
        mod_total = config['mod_total']
        mod_value = config['mod_value']
    except Exception as e:
        print(f"❌ Error parsing --config JSON: {e}")
        exit(1)

    current_account = SMTP_ACCOUNTS[account_index]
    print(f"--- Starting Job ---")
    print(f"Using SMTP Account: {current_account['username']}")
    print(f"Processing tables: {tables}")
    print(f"Email limit for this job: {max_emails_to_send}")
    print(f"Processing email partition: {mod_value} of {mod_total} (e.g., MOD(CRC32(email), {mod_total}) = {mod_value})")

    try:
        conn = mysql.connector.connect(
            host='srv1640.hstgr.io',
            user='u707137586_Campus_Hiring',
            password='6q+SFd~o[go',
            database='u707137586_Campus_Hiring',
            # user = "u707137586_EV_Reg_T1_24",
            # password = "DMKL0IYoP&4",
            # database = "u707137586_EV_Reg_2024_T1",
            connect_timeout=20
        )
        cursor = conn.cursor(dictionary=True)
    except mysql.connector.Error as err:
        print(f"❌ Error connecting to database: {err}")
        exit(1)

    emails_sent_count = 0
    consecutive_failures = 0
    FAILURE_THRESHOLD = 10
    stop_campaign_due_to_errors = False

    for tbl in tables:
        if emails_sent_count >= max_emails_to_send:
            print(f"\nReached the job limit of {max_emails_to_send} emails. Stopping.")
            break

        emails_to_fetch = max_emails_to_send - emails_sent_count

        query = f"""
            SELECT email, first_name 
            FROM {tbl} 
            WHERE emailSent=0 
            AND email NOT IN (SELECT email FROM unsubscribed_emails)
            AND MOD(CRC32(email), %s) = %s
            LIMIT %s;
        """

        # query = f"""
        #     SELECT email, name FROM {tbl} WHERE emailSent=0 AND MOD(CRC32(email), %s) = %s LIMIT %s
        # """

        try:
            cursor.execute(query, (mod_total, mod_value, emails_to_fetch))
            rows_to_process = cursor.fetchall()
        except mysql.connector.Error as err:
            print(f"❌ Error querying table {tbl}: {err}")
            continue

        if not rows_to_process:
            print(f"No emails to send in table: {tbl} for this partition.")
            continue

        print(f"Found {len(rows_to_process)} emails to process in {tbl} for this partition")

        for row in rows_to_process:
            if emails_sent_count >= max_emails_to_send:
                print(f"Reached job limit mid-table. Stopping.")
                break

            if send_email(row['email'], row.get('first_name', 'there'), current_account['username'], current_account['password']):
            # if send_email(row['email'], row['name'], current_account['username'], current_account['password']):
                consecutive_failures = 0
                emails_sent_count += 1
                print(f"✅ ({emails_sent_count}/{max_emails_to_send}) Sent to {row['email']} using {current_account['username']}")
                
                try:
                    conn.ping(reconnect=True, attempts=3, delay=5)
                except mysql.connector.Error as err:
                    print(f"❌ Error reconnecting to DB: {err}. Skipping update for {row['email']}")
                    continue

                update_cursor = conn.cursor()
                try:
                    update_cursor.execute(f"UPDATE {tbl} SET emailSent=1 WHERE email=%s", (row['email'],))
                    conn.commit()
                except mysql.connector.Error as e:
                    print(f"❌ Error updating emailSent flag for {row['email']}: {e}")
                    conn.rollback()
                finally:
                    update_cursor.close()
                    
                delay = random.uniform(0.5, 2.0)
                time.sleep(delay)
            else:
                consecutive_failures += 1
                print(f"⚠️ Consecutive send failures: {consecutive_failures}")
                if consecutive_failures >= FAILURE_THRESHOLD:
                    print(f"\n❌ STOPPING JOB: Reached {FAILURE_THRESHOLD} consecutive send errors.")
                    print("This likely means the email provider has blocked the account for the day.")
                    stop_campaign_due_to_errors = True
                    break
        
        if stop_campaign_due_to_errors:
            break

    cursor.close()
    conn.close()

    print(f"\n--- Job Finished ---")
    print(f"Total emails sent in this job: {emails_sent_count}")

if __name__ == "__main__":
    main()