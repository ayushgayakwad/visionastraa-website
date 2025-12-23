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
CAMPAIGN_ID = "ev_marketing_campaign_4_1_december_2025"

EMAIL_SUBJECT = "Royal Enfield & Euler Motors Hire from VisionAstraa EV Academy!"

EMAIL_BODY_TEMPLATE = """\
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Royal Enfield & Euler Motors Hire from VisionAstraa EV Academy!</title>
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
            margin-bottom: 15px;
            color: #155724;
            background-color: #d4edda;
            padding: 10px;
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
        
        /* Grid Layout for Students */
        .student-grid {{
            display: table; /* Fallback for older clients */
            width: 100%;
            text-align: center;
            border-spacing: 10px; /* Space between cells */
            border-collapse: separate; 
        }}
        .student-row {{
            display: table-row;
        }}
        .student-cell {{
            display: table-cell;
            width: 50%;
            vertical-align: top;
            background: #f9f9f9;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }}
        
        /* Responsive Block for Mobile */
        @media only screen and (max-width: 480px) {{
            .student-grid, .student-row, .student-cell {{
                display: block;
                width: 100%;
                box-sizing: border-box;
            }}
            .student-cell {{
                margin-bottom: 15px;
            }}
        }}

        .student-img {{
            width: 100px;
            height: 133px; /* 3:4 Aspect Ratio */
            border-radius: 6px;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid #28a745;
            background-color: #ddd;
            display: inline-block;
        }}
        .student-name {{
            font-weight: bold;
            color: #333;
            font-size: 15px;
            display: block;
            margin-bottom: 4px;
        }}
        .student-detail {{
            font-size: 13px;
            color: #555;
            font-weight: 500;
            line-height: 1.3;
            display: block;
            margin-bottom: 2px;
        }}
        .student-college {{
            font-size: 11px;
            color: #777;
            font-style: italic;
            display: block;
            margin-top: 4px;
        }}
        .hired-tag {{
            display: block;
            margin-top: 8px;
            font-weight: bold;
            color: #d9534f; /* distinctive color for the company */
            font-size: 13px;
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
        .contact-info {{
            background-color: #e9f7ef;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin: 20px 0;
            border: 1px dashed #28a745;
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
        .hands-on-section {{
            margin: 30px 0;
        }}
        .vehicle-table {{
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }}
        .vehicle-cell-td {{
            width: 50%;
            padding: 0 4px; /* Tiny padding between images */
            vertical-align: top;
            text-align: center;
        }}
        .vehicle-img {{
            width: 100%;       /* Fill the cell */
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            display: block;    /* Removes bottom gap */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }}
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="https://visionastraa.com/images/EV_Academy.png" alt="EV Academy Logo" style="max-width: 150px;">
        </div>

        <h1>Royal Enfield & Euler Motors Hire from VisionAstraa!</h1>

        <table class="vehicle-table" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td class="vehicle-cell-td">
                     <img src="https://visionastraa.com/images/em.jpg" alt="Euler Motors" class="vehicle-img">
                </td>
                <td class="vehicle-cell-td">
                     <img src="https://visionastraa.com/images/re.jpg" alt="Royal Enfield" class="vehicle-img">
                </td>
            </tr>
        </table>

        <p>Dear {first_name},</p>

        <p>
            We are glad to share with you that <strong>Royal Enfield</strong> and <strong>Euler Motors</strong>, leaders in the Electric Vehicle Industry, have hired <strong>5 of our engineering students</strong> from our recent batch for full-time roles in their R&D and Engineering departments.
        </p>
        <p>
            This achievement reflects the strong technical foundation, hands-on exposure, and focused interview preparation each of our students receives during the <strong>4-month hands-on in-person training in Electric PowerTrain Design</strong>.
        </p>

        <h3>🎉 Congratulations to Our Placed Students</h3>

        <div class="student-grid">
            <!-- Row 1 -->
            <div class="student-row">
                <div class="student-cell">
                    <img src="https://visionastraa.com/images/abhishek.jpg" alt="Abhishek Vinchurkar" class="student-img">
                    <span class="student-name">Abhishek Vinchurkar</span>
                    <span class="student-detail">B.Tech in Mech with EV</span>
                    <span class="student-college">Vellore Institute of Technology, Chennai</span>
                    <span class="hired-tag">Hired at Euler Motors</span>
                </div>
                <div class="student-cell">
                    <img src="https://visionastraa.com/images/arun-r.jpg" alt="Arun R" class="student-img">
                    <span class="student-name">Arun R</span>
                    <span class="student-detail">B.Tech in EEE</span>
                    <span class="student-college">Bannari Amman Institute of Technology, Sathyamangalam</span>
                    <span class="hired-tag">Hired at Euler Motors</span>
                </div>
            </div>
            
            <!-- Row 2 -->
            <div class="student-row">
                <div class="student-cell">
                    <img src="https://visionastraa.com/images/rishabh.jpg" alt="Rishabh Bhansali" class="student-img">
                    <span class="student-name">Rishabh Bhansali</span>
                    <span class="student-detail">M.Tech in Mechatronics</span>
                    <span class="student-college">Vellore Institute of Technology, Vellore</span>
                    <span class="hired-tag">Hired at Euler Motors</span>
                </div>
                <div class="student-cell">
                    <img src="https://visionastraa.com/images/nisaraga.jpg" alt="Nisarga Goudar" class="student-img">
                    <span class="student-name">Nisarga Goudar</span>
                    <span class="student-detail">B.Tech in EEE</span>
                    <span class="student-college">K.L.E. Institute of Technology, Hubli</span>
                    <span class="hired-tag">Hired at Royal Enfield</span>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="student-row">
                <div class="student-cell">
                    <img src="https://visionastraa.com/images/shruti.jpg" alt="Shruti Gupta" class="student-img">
                    <span class="student-name">Shruti Gupta</span>
                    <span class="student-detail">M.Tech in Mechatronics</span>
                    <span class="student-college">Vellore Institute of Technology, Vellore</span>
                    <span class="hired-tag">Hired at Royal Enfield</span>
                </div>
                <!-- Empty cell filler to maintain table structure if needed, or leave empty -->
                <div class="student-cell" style="background: transparent; border: none; box-shadow: none;">
                </div>
            </div>
        </div>
        
        <p>
            Their placements highlight how candidates from diverse academic backgrounds can successfully transition into the EV industry with the right guidance and practical training.
        </p>
        
        <hr style="margin: 25px 0; border: 0; border-top: 1px solid #ddd;">

        <div class="hands-on-section">
            <h2 class="hands-on-title" style="text-align:center;">Learning by Doing at VisionAstraa</h2>
            <p style="text-align:center;">
                Students gain real-world EV expertise through intensive hands-on lab sessions and practical training.
            </p>
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
        </div>
        
        <hr style="margin: 25px 0; border: 0; border-top: 1px solid #ddd;">

        <h2>Why This Matters</h2>
        <p>At <strong>VisionAstraa EV Academy</strong>, we focus on:</p>
        <ul>
            <li>Industry-relevant EV fundamentals</li>
            <li>Hands-on, practical-oriented learning</li>
            <li>Strong mentorship and interview readiness</li>
            <li>Connecting talent with real EV industry opportunities</li>
        </ul>
        <p>
            Results like these motivate us to keep raising the bar for EV skill development in India.
        </p>

        <hr style="margin: 25px 0; border: 0; border-top: 1px solid #ddd;">

        <h2>🚀 Your EV Career Can Start Here</h2>
        <p>
            If interested to pursue a career in the Electric Vehicle Industry, please contact us immediately to join our next batch starting in <strong>January 2026!</strong> Seats are limited & filling up quickly!
        </p>

        <div class="contact-info">
            <p style="margin: 5px 0;"><strong>📩 Email:</strong><br><a href="mailto:admissions@visionastraa.com">admissions@visionastraa.com</a></p>
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
            AND state IN ('Karnataka', 'Maharashtra')
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
