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

# SMTP_ACCOUNTS = [
#     {
#         'username': 'careers@visionastraa.in',
#         'password': 'Z1SIOO0A9b~'
#     },
#     {
#         'username': 'visionastraa@evcourse.in',
#         'password': '>p>W|jv?Kg1'
#     }
# ]

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

# Updated Campaign ID
CAMPAIGN_ID = "ev_marketing_campaign_batch2_placement_feb_2026"

EMAIL_SUBJECT = "100% Placement Success! Batch 2 Students Hired by Tata, TVS, RE & More"

EMAIL_BODY_TEMPLATE = """\
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>100% Placement Success - VisionAstraa EV Academy</title>
    <style>
        body {{
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }}
        .container {{
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }}
        h1 {{
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #28a745;
        }}
        h2 {{
            font-size: 20px;
            margin-bottom: 15px;
            margin-top: 25px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
            color: #28a745;
        }}
        .highlight-box {{
            background-color: #e9f7ef;
            border: 1px solid #28a745;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }}
        .highlight-stat {{
            font-size: 28px;
            color: #28a745;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }}
        p, li {{
            font-size: 16px;
            margin-top: 0px;
            margin-bottom: 15px;
        }}
        a {{
            color: #1a73e8;
            font-weight: bold;
            text-decoration: none;
        }}
        a:hover {{
            text-decoration: underline;
        }}
        
        /* ROBUST GRID SYSTEM FOR EMAIL (INLINE-BLOCK) */
        .company-grid-container {{
            text-align: center;
            font-size: 0; /* Removes whitespace between inline-blocks */
            padding: 10px 0;
        }}
        .company-badge {{
            display: inline-block;
            width: 120px;       /* Fixed width for stability */
            height: 90px;       /* Fixed height */
            margin: 6px;
            background-color: #ffffff;
            border: 1px solid #eee;
            border-radius: 6px;
            vertical-align: middle;
            text-align: center;
            padding: 5px;
            box-sizing: border-box; /* Ensures padding doesn't increase width */
        }}
        /* Helper to vertically align images inside the badge */
        .company-badge span {{
            display: inline-block;
            height: 100%;
            vertical-align: middle;
        }}
        .company-logo-img {{
            max-width: 100px;   /* Fit within badge width */
            max-height: 70px;   /* Fit within badge height */
            width: auto;
            height: auto;
            vertical-align: middle;
            display: inline-block;
        }}

        /* Landscape Images */
        .landscape-img {{
            width: 100%;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            display: block;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }}

        .btn {{
            background-color: #28a745;
            color: #ffffff !important;
            padding: 14px 30px;
            text-align: center;
            border-radius: 5px;
            display: inline-block;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }}
        .btn:hover {{
            background-color: #218838;
        }}
        .footer {{
            text-align: center;
            font-size: 14px;
            color: #777777;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }}
        .logo-container {{
            text-align: center;
            margin-bottom: 20px;
        }}
        .contact-card {{
            background: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 20px;
        }}
        .whatsapp-link {{
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #25D366;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            margin-top: 10px;
        }}
        .whatsapp-link img {{
            margin-right: 8px;
            width: 22px;
            height: 22px;
        }}
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="https://visionastraa.com/images/EV_Academy.png" alt="EV Academy Logo" style="max-width: 150px;">
        </div>

        <div style="margin-bottom: 25px; text-align: center;">
            <img src="https://visionastraa.com/images/group-pic-1.jpeg" alt="Batch 2 Placed Students" class="landscape-img">
        </div>

        <h1>Batch 2 (2025) Achieves 100% Placement!</h1>

        <div class="highlight-box">
            <span class="highlight-stat">35+ Students Placed</span>
            All students from Batch 2 have secured roles in top EV Companies.
        </div>

        <p>Dear {first_name},</p>

        <p>
            We are incredibly proud to announce that <strong>every single student (35+) from our Batch 2 (2025)</strong> has been successfully placed in the Electric Vehicle industry.
        </p>
        <p>
            Their hard work, combined with our intensive 4-month hands-on PowerTrain Design training, has led them to exciting careers in R&D and Engineering.
        </p>

        <h2>Hiring Partners for Batch 2</h2>
        <p>Our students have been hired by some of the most prestigious names in the EV ecosystem:</p>

        <div class="company-grid-container">
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/tm.jpg" alt="Tata Motors" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/uv-bg.jpg" alt="Ultraviolette" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/tvs.png" alt="TVS Motor" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/royal-enfield.png" alt="Royal Enfield" class="company-logo-img"></div>
            
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/euler.jpg" alt="Euler Motors" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/matter.webp" alt="Matter EV" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/sm.jpeg" alt="Sun Mobility" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/montra.png" alt="Montra Electric" class="company-logo-img"></div>
            
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/simple.jpg" alt="Simple Energy" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/mecwin.webp" alt="Mecwin" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/chara.png" alt="Chara" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/embitel.png" alt="Embitel" class="company-logo-img"></div>
            
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/yulu.avif" alt="Yulu" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/seg-logo.jpg" alt="SEG Automotive" class="company-logo-img"></div>
            
            <div class="company-badge"><span></span><img src="https://visionastraa.com/image/ipec-logo.png" alt="iPec" class="company-logo-img"></div>
            <div class="company-badge"><span></span><img src="https://visionastraa.com/images/mai.jpeg" alt="Moonrider" class="company-logo-img"></div>
        </div>

        <div style="margin: 25px 0; text-align: center;">
            <img src="https://visionastraa.com/images/tvs-iv.jpg" alt="Placement Celebration" class="landscape-img">
        </div>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #ddd;">

        <h2>🚀 Join the Next Success Story</h2>
        <p>
            Do you want to build a career in the booming EV industry? Our next batch is opening soon!
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <p style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">
                📅 Next Batch Starts:<br><span style="color: #d9534f;">March 1st Week, 2026</span>
            </p>
            <p style="margin-bottom: 25px;">
                Join our <strong>4-month EV PowerTrain Mastery Program</strong> and secure your job in the EV sector.
            </p>
            
            <a href="https://visionastraa.com/track/click.php?email={email}&target={apply}&campaign_id={campaign_id}" class="btn">APPLY NOW</a>
        </div>

        <div class="contact-card">
            <p style="margin: 0 0 10px 0; font-weight: bold;">Have questions? Contact us directly:</p>
            <p style="font-size: 20px; font-weight: bold; margin: 0;">📞 +91 80756 64438</p>
             <div style="margin-top: 15px;">
                <a href="https://visionastraa.com/track/click.php?email={email}&target={whatsapp}&campaign_id={campaign_id}" target="_blank" class="whatsapp-link">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/2044px-WhatsApp.svg.png" alt="WhatsApp">
                    Chat on WhatsApp
                </a>
            </div>
        </div>

        <div class="footer">
            <p>Talk to our CEO, Nikhil Jain C S: <a href="https://visionastraa.com/track/click.php?email={email}&target={njcs}&campaign_id={campaign_id}">LinkedIn</a></p>

            <div class="social-links">
              <table align="center" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; border-spacing: 15px;">
                  <tr>
                      <td align="center">
                          <a href="https://visionastraa.com/track/click.php?email={email}&target={linkedin}&campaign_id={campaign_id}" target="_blank" title="LinkedIn">
                              <img src="https://www.visionastraa.com/images/linkedin.webp" alt="LinkedIn" width="28" height="28" style="display: block; border: 0;">
                          </a>
                      </td>
                      <td align="center">
                          <a href="https://visionastraa.com/track/click.php?email={email}&target={instagram}&campaign_id={campaign_id}" target="_blank" title="Instagram">
                              <img src="https://www.visionastraa.com/images/instagram.webp" alt="Instagram" width="28" height="28" style="display: block; border: 0;">
                          </a>
                      </td>
                      <td align="center">
                          <a href="https://visionastraa.com/track/click.php?email={email}&target={youtube}&campaign_id={campaign_id}" target="_blank" title="YouTube">
                              <img src="https://www.visionastraa.com/images/youtube.webp" alt="YouTube" width="36" height="28" style="display: block; border: 0;">
                          </a>
                      </td>
                  </tr>
              </table>
            </div>
            <br>
            <p style="font-size:12px;color:#888;">
              If you no longer wish to receive emails from us, you can 
              <a href="https://visionastraa.com/track/unsubscribe.php?email={email}&campaign_id={campaign_id}" style="color:#1a73e8;">unsubscribe here</a>.
            </p>
        </div>
        <img src="{image_url}" width="1" height="1" style="display:none;">
    </div>
</body>
</html>
"""

def send_email(to_address, first_name, smtp_username, smtp_password):
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
            # user='u707137586_Campus_Hiring',
            # password='6q+SFd~o[go',
            # database='u707137586_Campus_Hiring',
            user = "u707137586_EV_Reg_T1_24",
            password = "DMKL0IYoP&4",
            database = "u707137586_EV_Reg_2024_T1",
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

        # query = f"""
        #     SELECT email, first_name 
        #     FROM {tbl} 
        #     WHERE emailSent=0 
        #     AND email NOT IN (SELECT email FROM unsubscribed_emails)
        #     AND MOD(CRC32(email), %s) = %s
        #     LIMIT %s;
        # """

        query = f"""
            SELECT email, name FROM {tbl} WHERE emailSent=0 AND MOD(CRC32(email), %s) = %s LIMIT %s
        """

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

            # if send_email(row['email'], row.get('first_name', 'there'), current_account['username'], current_account['password']):
            if send_email(row['email'], row['name'], current_account['username'], current_account['password']):
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
