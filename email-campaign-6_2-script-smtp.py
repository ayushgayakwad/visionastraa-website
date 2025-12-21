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

SMTP_ACCOUNTS = [
    {
        'username': 'careers@visionastraa.in',
        'password': 'Z1SIOO0A9b~'
    },
    {
        'username': 'visionastraa@evcourse.in',
        'password': '>p>W|jv?Kg1'
    }
]

# Updated Campaign ID
CAMPAIGN_ID = "ev_marketing_campaign_3_2_december_2025"

EMAIL_SUBJECT = "Kerala Talent Making Strong Moves in the EV & Automotive Industry"

EMAIL_BODY_TEMPLATE = """\
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerala Talent Making Strong Moves in the EV & Automotive Industry</title>
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
        h1, h2, h3 {{
            color: #28a745;
        }}
        h1 {{
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            line-height: 1.3;
        }}
        h2 {{
            font-size: 20px;
            margin-bottom: 15px;
            margin-top: 25px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }}
        h3 {{
            font-size: 18px;
            margin-bottom: 20px;
            color: #155724;
            background-color: #d4edda;
            padding: 12px;
            border-radius: 4px;
            text-align: center;
        }}
        p, li {{
            font-size: 16px;
            margin-top: 0px;
            margin-bottom: 15px;
        }}
        ul {{
            margin-bottom: 15px;
            padding-left: 20px;
        }}
        li {{
            margin-bottom: 8px;
        }}
        a {{
            color: #1a73e8;
            font-weight: bold;
            text-decoration: none;
        }}
        a:hover {{
            text-decoration: underline;
        }}
        .highlight {{
            color: #ff5722;
            font-weight: bold;
        }}
        
        /* Student Card Styles */
        .student-card {{
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 15px;
            text-align: center;
            height: 100%; /* Fill cell height */
            box-sizing: border-box;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }}
        .student-img {{
            width: 100%;
            max-width: 120px;
            height: 160px; /* 3:4 Aspect Ratio */
            border-radius: 8px;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid #28a745;
            background-color: #ddd;
            display: inline-block;
        }}
        .student-name {{
            font-weight: bold;
            color: #333;
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
            line-height: 1.2;
        }}
        .student-detail {{
            font-size: 13px;
            color: #555;
            line-height: 1.3;
            display: block;
            margin-bottom: 3px;
        }}
        .placement-badge {{
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
        }}
        
        .button-container {{
            text-align: center;
            margin: 30px 0px 10px 0px;
        }}
        .cta-button-container {{
            text-align: center;
            margin: 0px 0px 30px 0px;
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
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }}
        .contact-info {{
            background-color: #e9f7ef;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin: 20px 0;
            border: 1px dashed #28a745;
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
        
        /* Table overrides for mobile responsiveness */
        @media screen and (max-width: 480px) {{
            .student-col {{
                display: block;
                width: 100% !important;
                padding: 0 0 15px 0 !important;
            }}
        }}
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="https://visionastraa.com/images/EV_Academy.png" alt="EV Academy Logo" style="max-width: 150px;">
        </div>

        <h1>Kerala Talent Making Strong Moves in the EV & Automotive Industry</h1>

        <p>Dear {first_name},</p>

        <p>
            Students from Kerala continue to demonstrate how strong fundamentals and focused industry training can translate into real career outcomes in the EV and automotive sector.
        </p>

        <p>If you are an engineering graduate with B.Tech or M.Tech degree in EEE, ECE, Mechanical (or allied branches ) and interested to pursue a career in the Electric Vehicle Industry, please contact us immediately to join our next batch starting in January 2026! Seats are limited & filling up quickly!</p>

        <p>We’re happy to share the successful placements of the following students:</p>

        <h3>🎓 Student Placements – Kerala</h3>

        <!-- Student Grid Start -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <!-- Row 1 -->
            <tr>
                <td width="50%" valign="top" class="student-col" style="padding-right: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/devika-m.jpg" alt="Devika Manoj" class="student-img">
                        <span class="student-name">Devika Manoj</span>
                        <span class="student-detail">M.Tech, Power Electronics, 2025</span>
                        <span class="student-detail">College of Engineering, Trivandrum</span>
                        <div class="placement-badge">Placed at Montra Electric</div>
                    </div>
                </td>
                <td width="50%" valign="top" class="student-col" style="padding-left: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/amrutha.jpg" alt="Amrutha Mohan" class="student-img">
                        <span class="student-name">Amrutha Mohan</span>
                        <span class="student-detail">M.Tech, Mechatronics, 2025</span>
                        <span class="student-detail">VIT, Vellore</span>
                        <div class="placement-badge">Placed at Montra Electric</div>
                    </div>
                </td>
            </tr>
            <!-- Row 2 -->
            <tr>
                <td width="50%" valign="top" class="student-col" style="padding-right: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/ardra.jpg" alt="Ardra K" class="student-img">
                        <span class="student-name">Ardra K</span>
                        <span class="student-detail">B.Tech, Electrical & Electronics Engg., 2025</span>
                        <span class="student-detail">Cochin University of Science and Technology, Kochi</span>
                        <div class="placement-badge">Placed at Montra Electric</div>
                    </div>
                </td>
                <td width="50%" valign="top" class="student-col" style="padding-left: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/pranav.jpg" alt="Pranav P" class="student-img">
                        <span class="student-name">Pranav P</span>
                        <span class="student-detail">B.Tech, Mechanical Engineering, 2025</span>
                        <span class="student-detail">Cochin University of Science and Technology, Kochi</span>
                        <div class="placement-badge">Placed at Tata Motors</div>
                    </div>
                </td>
            </tr>
            <!-- Row 3 -->
            <tr>
                <td width="50%" valign="top" class="student-col" style="padding-right: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/p10.jpg" alt="Athul K N" class="student-img">
                        <span class="student-name">Athul K N</span>
                        <span class="student-detail">B.Tech, Mechanical Engineering, 2024</span>
                        <span class="student-detail">Government Engineering College, Thrissur</span>
                        <div class="placement-badge">Placed at Ultraviolette Automotive</div>
                    </div>
                </td>
                <td width="50%" valign="top" class="student-col" style="padding-left: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/p2.jpg" alt="Aiswarya R" class="student-img">
                        <span class="student-name">Aiswarya R</span>
                        <span class="student-detail">M.Tech, Electrical & Electronics Engg., 2023</span>
                        <span class="student-detail">College of Engineering, Trivandrum</span>
                        <div class="placement-badge">Placed at Ultraviolette Automotive</div>
                    </div>
                </td>
            </tr>
             <!-- Row 4 -->
            <tr>
                <td width="50%" valign="top" class="student-col" style="padding-right: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/p5.jpg" alt="Ashwin Thampi" class="student-img">
                        <span class="student-name">Ashwin Thampi</span>
                        <span class="student-detail">B.Tech, Mechanical Engineering, 2024</span>
                        <span class="student-detail">Government Engineering College, Thrissur</span>
                        <div class="placement-badge">Placed at Mecwin Technologies</div>
                    </div>
                </td>
                <td width="50%" valign="top" class="student-col" style="padding-left: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/p18.jpg" alt="Fayas Khan" class="student-img">
                        <span class="student-name">Fayas Khan</span>
                        <span class="student-detail">B.Tech, Mechanical Engineering, 2024</span>
                        <span class="student-detail">Government Engineering College, Thrissur</span>
                        <div class="placement-badge">Placed at Sun Mobility</div>
                    </div>
                </td>
            </tr>
             <!-- Row 5 -->
            <tr>
                <td width="50%" valign="top" class="student-col" style="padding-right: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/p6.jpg" alt="Najiya N" class="student-img">
                        <span class="student-name">Najiya N</span>
                        <span class="student-detail">M.Tech, Electrical & Electronics Engg., 2023</span>
                        <span class="student-detail">College of Engineering, Trivandrum</span>
                        <div class="placement-badge">Placed at Mecwin Technologies</div>
                    </div>
                </td>
                <td width="50%" valign="top" class="student-col" style="padding-left: 5px; padding-bottom: 10px;">
                    <div class="student-card">
                        <img src="https://visionastraa.com/images/p19.jpg" alt="Shalina Nelson" class="student-img">
                        <span class="student-name">Shalina Nelson</span>
                        <span class="student-detail">B.Tech, Electrical & Electronics Engg., 2024</span>
                        <span class="student-detail">Government Engineering College, Thrissur</span>
                        <div class="placement-badge">Placed at Embitel</div>
                    </div>
                </td>
            </tr>
             <!-- Row 6 -->
            <tr>
                <td width="50%" valign="top" class="student-col" style="padding-right: 5px; padding-bottom: 10px;">
                   <div class="student-card">
                        <!-- <img src="REPLACE_WITH_AVYAJITH_IMAGE_URL" alt="Avyajith S" class="student-img"> -->
                        <span class="student-name">Avyajith S</span>
                        <span class="student-detail">B.Tech, Electrical & Electronics Engg., 2025</span>
                        <span class="student-detail">College of Engineering, Trivandrum</span>
                        <div class="placement-badge">Placed at Chara Technologies</div>
                    </div>
                </td>
                <td width="50%" valign="top" class="student-col" style="padding-left: 5px; padding-bottom: 10px;">
                    <!-- Empty Cell for balance -->
                </td>
            </tr>
        </table>
        <!-- Student Grid End -->

        <hr style="margin: 25px 0; border: 0; border-top: 1px solid #ddd;">

        <h2>What This Reflects</h2>
        <p>
            These placements highlight how students from diverse engineering streams — Electrical, Electronics, Mechanical, Mechatronics, and Power Electronics — can successfully enter the EV and automotive industry when learning is aligned with real-world requirements.
        </p>
        <p>At <strong>VisionAstraa EV Academy</strong>, the focus remains on:</p>
        <ul>
            <li>Industry-relevant technical clarity</li>
            <li>Hands-on exposure</li>
            <li>Structured interview preparation</li>
            <li>Connecting talent with meaningful opportunities</li>
        </ul>

        <table width="100%" cellpadding="0" cellspacing="0" align="center" style="margin-top:20px;">
            <tr>
                <td align="center" width="50%" style="padding-right:10px;">
                    <img src="https://visionastraa.com/images/hands-on-2.jpg"
                        style="width:100%; max-width:260px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                </td>

                <td align="center" width="50%" style="padding-left:10px;">
                    <img src="https://visionastraa.com/images/hands-on-5.jpg"
                        style="width:100%; max-width:260px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                </td>
            </tr>
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" align="center" style="margin-top:15px;">
            <tr>
                <td align="center" width="50%" style="padding-right:10px;">
                    <img src="https://visionastraa.com/images/hands-on-3.jpg"
                        style="width:100%; max-width:260px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                </td>

                <td align="center" width="50%" style="padding-left:10px;">
                    <img src="https://visionastraa.com/images/hands-on-4.jpg"
                        style="width:100%; max-width:260px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15);">
                </td>
            </tr>
        </table>

        <hr style="margin: 25px 0; border: 0; border-top: 1px solid #ddd;">

        <h2>📩 Interested in Building Your EV Career?</h2>

        <div class="contact-info">
            <p style="margin: 5px 0;"><strong>📧 Email:</strong><br><a href="mailto:admissions@visionastraa.com">admissions@visionastraa.com</a></p>
            <p style="margin: 5px 0;"><strong>📞 Phone / WhatsApp:</strong><br><a href="https://visionastraa.com/track/click.php?email={email}&target={whatsapp}&campaign_id={campaign_id}">+91 80756 64438</a></p>
            <p style="margin-top: 10px; color: #d9534f; font-weight: bold;">Admissions for upcoming batch starting in January 2026 is now open!<br><br>Hurry, limited seats!</p>
        </div>

        <div class="button-container">
            <a href="https://visionastraa.com/track/click.php?email={email}&target={apply}&campaign_id={campaign_id}" class="btn">APPLY NOW</a>
        </div>
        
        <div class="footer">
            <p><strong>Please connect with us directly on WhatsApp if you have any questions on our Program and Placement opportunity.</strong></p>
            
            <div class="cta-button-container">
                <a href="https://visionastraa.com/track/click.php?email={email}&target={whatsapp}&campaign_id={campaign_id}" target="_blank" class="whatsapp-link">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/2044px-WhatsApp.svg.png" alt="WhatsApp Logo">
                    Chat with us on WhatsApp
                </a>
            </div>

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
            AND state IN ('Kerala', 'Tamil Nadu')
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