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

CAMPAIGN_ID = "ev_marketing_campaign_1_december_2025"

EMAIL_SUBJECT = "First Placement from our Second Batch in less than 2 months!"

EMAIL_BODY_TEMPLATE = """\
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>First Placement from our Second Batch in less than 2 months!</title>
    <style>
        body {{
            font-family: Arial, sans-serif;
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
            font-size: 28px;
            margin-bottom: 20px;
        }}
        h2 {{
            font-size: 21px;
            margin-bottom: 15px;
        }}
        h3 {{
            font-size: 18px;
            margin-bottom: 10px;
        }}
        p, li {{
            font-size: 16px;
            margin-top: 0px;
            margin-bottom: 10px;
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
        .button-container {{
            text-align: center;
            margin: 30px 0;
        }}
        .btn {{
            background-color: #28a745;
            color: #ffffff !important;
            padding: 12px 25px;
            text-align: center;
            border-radius: 5px;
            display: inline-block;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }}
        .btn:hover {{
            background-color: #218838;
            text-decoration: none;
        }}
        .footer {{
            text-align: center;
            font-size: 14px;
            color: #777777;
            margin-top: 20px;
        }}
        .logo-container {{
            text-align: center;
            margin-bottom: 20px;
        }}
        .social-links table {{
            margin: 0 auto;
        }}
        .social-links td {{
            padding: 0 10px;
        }}
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="https://visionastraa.com/images/EV_Academy.png" alt="EV Academy Logo" style="max-width: 150px;">
        </div>

        <h1>🌟 First Placement from our Second Batch in less than 2 months!</h1>

        <p>
            Congratulations to <strong>Hemanth V</strong>, (EEE, B.Tech) from Velammal Institute of Technology, Chennai for becoming the first student to get hired from the second batch (2025) of <a href="https://visionastraa.com/track/click.php?email={email}&target={vaev_website}&campaign_id={campaign_id}" target="_blank">VisionAstraa EV Academy</a>!
        </p>

        <p>
            In <span class="highlight">less than 2 months</span> after the 4-month Program started (on Aug 4), Hemanth has cleared all interview rounds at <strong>Sun Mobility</strong> and will be joining them for a Full-Time role on Oct 6.
        </p>
        
        <hr style="margin: 25px 0;">

        <h2>Your Success Story Starts Here!</h2>
        <img src="https://www.visionastraa.com/images/hemant-1.jpg" alt="Success at EV Academy" style="width:100%; max-width:600px; margin-top:20px; margin-bottom: 10px; border-radius: 8px;">
        <br><br>
        <img src="https://www.visionastraa.com/images/hemant-2.jpg" alt="Success at EV Academy" style="width:100%; max-width:600px; margin-top:20px; margin-bottom: 10px; border-radius: 8px;">
        <br><br>
        <p>
            Our next batch starts in <strong>December 2025</strong>, and admissions are now open!
        </p>
        <p>
            Don't wait, come join VisionAstraa EV Academy if you are interested for a guaranteed job in the EV Industry!
        </p>

        <div class="button-container">
            <a href="https://visionastraa.com/track/click.php?email={email}&target={apply}&campaign_id={campaign_id}" class="btn">APPLY NOW</a>
        </div>

        <hr style="margin: 25px 0;">

        <h2>Why Choose VisionAstraa EV Academy?</h2>
        <ul>
            <li><strong>100% Placement Guarantee</strong> in the EV industry (or your money back).</li>
            <li>Hands-On, Practical-Oriented Training at our Centre of Excellence.</li>
            <li>Full support & mentorship in clearing Technical interviews in EV Domain.</li>
            <li>Industrial Visits to Leading EV Companies.</li>
        </ul>

        <hr style="margin: 25px 0;">

        <h2>What are you waiting for?</h2>
        <p>Don't miss this <span class="highlight">GOLDEN opportunity</span> to join our upcoming batch starting in December 2025 and secure a job in just 4 months!</p>

        <div class="image-container">
            <img src="https://www.visionastraa.com/image/133ddeb8.jpeg" alt="Upskill in EV Technologies" style="width:100%; max-width:600px; margin-top:8px" class="responsive-image">
        </div>

        <hr style="margin: 25px 0;">
        
        <div class="footer">
            <p>For questions, contact us:</p>
            <p>Email: <a href="mailto:admissions@visionastraa.com">admissions@visionastraa.com</a></p>
            <p>Phone: <a href="tel:+918075664438">+91 80756 64438</a></p>
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

def send_email(to_address, first_name):
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
    msg['From'] = formataddr(("VisionAstraa EV Academy", SMTP_USERNAME))
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
# tables = ['email_list_4', 'email_list_5', 'email_list_6']

for tbl in tables:
    cursor.execute(f"SELECT email, first_name FROM {tbl} WHERE emailSent=0 AND email NOT IN (SELECT email FROM unsubscribed_emails)")
    # cursor.execute(f"SELECT email, name FROM {tbl} WHERE emailSent=0")
    for row in cursor.fetchall():
        if send_email(row['email'], row['first_name']):
        # if send_email(row['email'], row['name']):
            print(f"✅ Sent to {row['email']}")
            cursor.execute(f"UPDATE {tbl} SET emailSent=1 WHERE email=%s", (row['email'],))
            # cursor.execute(f"UPDATE {tbl} SET emailSent=1 WHERE email=%s", (row['email'],))
            conn.commit()

cursor.close()
conn.close()
