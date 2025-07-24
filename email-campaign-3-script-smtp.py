# CURRENTLY SENDS 1 EMAIL EVERY 3 SECONDS (BUT MORE ROBUST AND RELIABLE)

import mysql.connector
import smtplib
import time
import random
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.application import MIMEApplication
from datetime import datetime, timedelta, timezone
from urllib.parse import quote
from email.utils import formataddr

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 587
SMTP_USERNAME = 'visionastraa@evcourse.in'
SMTP_PASSWORD = '>p>W|jv?Kg1'

# SMTP_USERNAME = 'careers@visionastraa.in'
# SMTP_PASSWORD = '1?Q#v!$adw:M'

CAMPAIGN_ID = "ev_promotional_campaign_3_2025"

EMAIL_SUBJECT = "Looking for Job in your CORE Field?"

EMAIL_BODY_TEMPLATE = """\
<html>
<head>
  <meta charset="UTF-8">
  <title>EV Academy Placement Campaign</title>
  <style>
    body {{
      font-family: Arial, sans-serif;
      background-color: #f6f6f6;
      margin: 0;
      padding: 0;
    }}
    .container {{
      max-width: 100%;
      margin: 20px auto;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 8px;
      color: #333333;
    }}
    h1, h2, h3 {{
      color: #333333;
    }}
    p {{
        margin-top: 0px;
        margin-bottom: 0px;
    }}
    .vaev-logo {{
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
        margin-top: 15px;
    }}
    .vaev-logo-item img {{
        width: 100%;
        height: auto;
        object-fit: contain;
        max-width: 120px;
        transition: filter 0.3s ease;
    }}
    .button-container {{
      text-align: center;
    }}
    .btn {{
      display: inline-block;
      padding: 12px 20px;
      margin: 10px 5px 20px 0;
      background-color: #28a745;
      color: #ffffff !important;
      text-decoration: none;
      border-radius: 4px;
      font-weight: bold;
    }}
    .faq {{
      margin-top: 20px;
    }}
    .faq h4 {{
      margin-top: 0px;
      margin-bottom: 0px;
      color: #222;
    }}
    .footer {{
      font-size: 14px;
      color: #777777;
    }}
  </style>
</head>
<body>
  <div class="container">
    <div class="vaev-logo">
      <img src="https://visionastraa.com/images/EV_Academy.png" alt="EV Academy">
    </div>
    <br>
    <h2 style="color: #d9534f;">🎯 Who is the Lucky One?</h2>
    <p><strong>Last couple of seats open.</strong> This is your <strong>last chance</strong> to join the upcoming batch and get placed by the end of the year.</p>
    <p style="color: #28a745;"><strong>✅ 28 people have already signed up. Only <span style="color:#d9534f;">2 seats left!</span></strong></p>
    <br>
    <h2>🌟 DON'T MISS OUT! OR LOSE A YEAR!</h2>
    <h3>ARE YOU LOOKING FOR A JOB IN YOUR CORE FIELD?</h3>
    <h2 style="color: #28a745;">WE GUARANTEE 100% PLACEMENT IN TOP EV COMPANIES!</h2>
    <p>Your Future in Electric Vehicles Starts with <strong><a href="https://visionastraa.com/track/click.php?email={email}&target={vaev_linkedin}&campaign_id={campaign_id}" target="_blank">VisionAstraa EV Academy</a></strong>!</p>
    <br>
    <p>Are you an engineering graduate with dreams of working in India’s fastest‑growing industry – Electric Vehicles?</p>
    <br>
    <hr>
    <br>
    <p style="color: #5943d6;"><strong>Below 25 Students who is looking for job already joined us for next batch!</strong></p>
    <br>
    <p><strong>What are you waiting for? JOIN NOW to guarantee a job!</strong></p>
    <br>
    <p><strong>Our Next Batch Starts - Aug 4, 2025!</strong></p>
    <br>
    <p style="color: #d9534f;"><strong>HURRY!! Only few seats left.</strong></p>
    <br>
    <p style="color: #5943d6;"><strong>(Mechanical already closed; Only seat for EEE/ECE left)</strong></p>
    <br>
    <p><strong>SECURE your SEAT NOW!</strong></p>    
    <br>
    <p><strong>Call/Message us - <a href="tel:+918075664438">+91 8075664438</a> for Admissions!</strong></p>
    <br>
    <img src="https://visionastraa.com/images/campaign-4.jpg" style="width:100%; max-width:600px; border-radius:8px; display:block; margin: auto auto;">
    <br>
    <p style="color: #c82333; font-size: 16px; font-weight: bold; margin-top: 10px; text-align: left;">
        Don't Miss this GOLDEN opportunity to secure a guaranteed job in EV Industry by Nov, 2025 if you join today!
    </p>
    <br>
    <p>
      ✅ <strong>100% Placement Guarantee (or your money back)</strong><br>
      ✅ <strong>Highest Package: ₹12 LPA</strong><br>
      ✅ <strong>Average Package: ₹5.5 LPA</strong><br>
      ✅ <strong>Lowest Package: ₹4 LPA (Minimum Package guarantee)</strong>
    </p>
    <br>
    <div class="button-container">
      <a href="https://visionastraa.com/track/click.php?email={email}&target={apply}&campaign_id={campaign_id}" class="btn">APPLY NOW</a>
      <a href="https://visionastraa.com/track/click.php?email={email}&target={placements}&campaign_id={campaign_id}" class="btn">PLACEMENTS</a>
      <a href="https://visionastraa.com/track/click.php?email={email}&target={curriculum}&campaign_id={campaign_id}" class="btn">CURRICULUM</a>
    </div>
    <hr>
    <p>For questions:</p>
    <p>Email: <a href="mailto:admissions@visionastraa.com">admissions@visionastraa.com</a></p>
    <p>Phone: +91 80756 64438, +91 81973 55166</p>
    <p>Talk to our Founder & CEO: <a href="https://www.linkedin.com/in/nikhiljaincs" target="_blank">Nikhil Jain C S</a></p>
    <hr>
    <div class="footer">
      <h3 style="font-weight: bold; text-align: center;">VisionAstraa EV Academy</h3>
      <table align="center">
        <tr>
          <td><a href="https://in.linkedin.com/company/va-ev-academy" target="_blank" style="color:#0077B5; text-decoration:none; font-weight:bold;">LinkedIn</a></td>
          <td><a href="https://www.instagram.com/va_ev_academy/" target="_blank" style="color:#C13584; text-decoration:none; font-weight:bold;">Instagram</a></td>
          <td><a href="https://www.youtube.com/@VisionAstraaEVAcademy" target="_blank" style="color:#FF0000; text-decoration:none; font-weight:bold;">YouTube</a></td>
        </tr>
      </table>
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

def send_email(to_address, first_name):
    vaev_linkedin = quote("https://www.linkedin.com/company/va-ev-academy", safe='')
    vaev_website = quote("https://www.visionastraa.com", safe='')
    apply = quote("https://www.visionastraa.com/ev-application.html", safe='')
    placements = quote("https://www.visionastraa.com/ev-jobs.html", safe='')
    curriculum = quote("https://www.visionastraa.com/ev-course.html", safe='')
    payments = quote("https://www.visionastraa.com/ev-payments.html", safe='')
    youtube = quote("https://www.youtube.com/watch?v=8CgZoxnYy_k", safe='')
    random_token = random.randint(100000, 999999)
    image_url = f"https://visionastraa.com/track/open.php?email={quote(to_address)}&campaign_id={CAMPAIGN_ID}&r={random_token}"

    body = EMAIL_BODY_TEMPLATE.format(
        first_name=first_name,
        email=quote(to_address),
        campaign_id=CAMPAIGN_ID,
        image_url=image_url,
        vaev_linkedin=vaev_linkedin,
        apply=apply,
        placements=placements,
        curriculum=curriculum,
        payments=payments,
        youtube=youtube,
        vaev_website=vaev_website
    )

    msg = MIMEMultipart('mixed')
    msg['Subject'] = EMAIL_SUBJECT
    msg['From'] = formataddr(("EV Jobs", SMTP_USERNAME))
    msg['To'] = to_address
    msg.attach(MIMEText(body, 'html'))

    try:
        with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
            server.starttls()
            server.login(SMTP_USERNAME, SMTP_PASSWORD)
            server.sendmail(SMTP_USERNAME, to_address, msg.as_string())
        return True
    except Exception as e:
        print(f"❌ Error sending to {to_address}: {e}")
        return False

conn = mysql.connector.connect(
    host='srv1640.hstgr.io',
    user='u707137586_Campus_Hiring',
    password='6q+SFd~o[go',
    database='u707137586_Campus_Hiring'
    # user = "u707137586_EV_Reg_T1_24",
    # password = "DMKL0IYoP&4",
    # database = "u707137586_EV_Reg_2024_T1"
)
cursor = conn.cursor(dictionary=True)
# tables = ['test']
tables = ['crdf25', 'crdf25_north', 'crdf25_south']
# tables = ['email_list_6', 'email_list_7']

for tbl in tables:
    cursor.execute(f"SELECT email, first_name FROM {tbl} WHERE state='Andhra Pradesh' AND emailSent_2=0 AND email NOT IN (SELECT email FROM unsubscribed_emails)")
    # cursor.execute(f"SELECT email, name FROM {tbl} WHERE emailSent=0")
    for row in cursor.fetchall():
        if send_email(row['email'], row['first_name']):
        # if send_email(row['email'], row['name']):
            print(f"✅ Sent to {row['email']}")
            cursor.execute(f"UPDATE {tbl} SET emailSent_2=1 WHERE email=%s", (row['email'],))
            # cursor.execute(f"UPDATE {tbl} SET emailSent=1 WHERE email=%s", (row['email'],))
            conn.commit()

cursor.close()
conn.close()
