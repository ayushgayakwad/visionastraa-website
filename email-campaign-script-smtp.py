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
SMTP_USERNAME = 'careers@visionastraa.in'
SMTP_PASSWORD = '4@upm7$K'

CAMPAIGN_ID = "ev_promotional_campaign_2025"

EMAIL_SUBJECT = "Want to get Job in EV Industry?"

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
      max-width: 700px;
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
    <div class="vaev-logo"><img src="https://visionastraa.com/images/EV_Academy.png" alt="EV Academy"></div>
    <br>
    <h2>Dear {first_name},</h2>
    <h3 style="margin-bottom: 0px;">ARE YOU LOOKING FOR A JOB IN EV INDUSTRY?</h3>
    <br>
    <h2 style="color: #28a745; margin-top: 0px; margin-bottom: 0px;">WE GUARANTEE 100% PLACEMENT!</h2>
    <br>
    <p>Look no further – Join <strong><a href="https://visionastraa.com/track/click.php?email={email}&target={vaev_linkedin}&campaign_id={campaign_id}" target="_blank">VisionAstraa EV Academy</a></strong> and get 100% guaranteed placement. <em>(If you don't get placed, you'll get your full money back!!)</em></p>
    <br>
    <p>The automotive industry, home to global giants like <strong>Volkswagen, Toyota, General Motors, Tesla, and BYD</strong>—all generating over $100B in revenue—is undergoing a major transition from traditional petrol/diesel engines to cleaner technologies, with battery-powered Electric Vehicles (EVs) currently leading the shift. In India, major players such as <strong>Maruti, Tata Motors, Mahindra, TVS, Hero, Bajaj, Royal Enfield, and Ashok Leyland</strong> are actively developing or launching new EV models for both domestic and international markets.</p>
    <br>
    <p><strong>All these companies and many others are looking for talented engineers for their EV Divisions - from R&D, Testing & Validation, Product Planning, Management and Production roles.</strong></p>
    <br>
    <p><strong><a href="https://visionastraa.com/track/click.php?email={email}&target={vaev_linkedin}&campaign_id={campaign_id}" target="_blank">VisionAstraa EV Academy</a></strong> is in a unique position as only one of the Premium EV Skill providers in India with deep connections with over 40+ EV Companies across India and can help you land a job in your dream company in EV Industry.</p>
    <br>
    <p>Our first batch of students are all placed with very good packages across multiple companies in the EV Industry! Below are the stats:</p>
    <hr>
    <h3>Companies that hire from VisionAstraa EV Academy</h3>
    <br>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
    <tr>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/image/tm.jpg" alt="Tata" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/image/tvs.png" alt="TVS" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/chara.png" alt="chara" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/yulu.avif" alt="yulu" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/uv.png" alt="uv" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
    </tr>
    <tr>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/image/ather.jpg" alt="ather" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/ola.webp" alt="ola" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/embitel.png" alt="embitel" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/sm.jpeg" alt="sm" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/euler.jpg" alt="euler" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
    </tr>
    <tr>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://cdn.evindia.online/uploads/blog/2022-11-20-06-11-63-blob" alt="Matter" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://astarcventures.com/wp-content/uploads/2017/07/Zypp.png" alt="Zypp" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/rev.png" alt="rev" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/mai.jpeg" alt="mai" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/bull.jpeg" alt="bull" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
    </tr>
    <tr>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/osm.webp" alt="osm" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/image/ipec-logo.png" alt="ipec" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/mecwin.webp" alt="mecwin" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
        <img src="https://visionastraa.com/images/simple.jpg" alt="simple" style="width:100%; max-width:100px; height:auto; display:block; border:0;">
        </td>
        <td width="20%" align="center" style="padding: 10px;">
            </td>
    </tr>
    </table>
    <hr>
    <h3 style="font-size: 24px;">Placement Stats:</h3>
    <ul style="font-size: 18px;">
      <li><strong>Highest Package:</strong> ₹12 LPA</li>
      <li><strong>Average Package:</strong> ₹5.5 LPA</li>
      <li><strong>Minimum Guaranteed:</strong> ₹4 LPA</li>
      <li><strong>Avg. Interviews per student:</strong> 5+</li>
    </ul>
    <hr>
    <div class="faq">
      <h3>📌 Frequently Asked Questions</h3>
      <h4>1. How long is the training program?</h4>
      <p>Ans: Training period would be 4-5 months in-person hands-on.</p>
      <br>
      <h4>2. When does placement start?</h4>
      <p>Ans: Placement cycle starts immediately after the training period ends. Placement cycle typically would be 1-2 months.</p>
      <br>
      <h4>3. Is training online or offline?</h4>
      <p>Ans: Training is completely offline (in-person) at our EV Center of Excellence at RV College of Engineering, Bengaluru.</p>
      <br>
      <h4>4. What is the training schedule?</h4>
      <p>Ans: Training would be in-person from Monday to Friday; 9:00am–5:00pm. It is mandatory for students to attend in-person classes with 90% attendance to be eligible for placements.</p>
      <br>
      <h4>5. Would there be exams conducted during the training?</h4>
      <p>Ans: Yes, there are theory and practical lab exams after the completion of each module with evaluation.</p>
      <br>
      <h4>6. Is it theory or practical training?</h4>
      <p>Ans: 70% hands-on practicals and 30% basics and theory.</p>
      <br>
      <h4>7. What is the selection criteria for admission to EV Academy?</h4>
      <p>Ans: CGPA cutoff, EV project works and interview.</p>
      <br>
      <h4>8. How much is the course fee?</h4>
      <p>Ans: Lowest compared to any other offline hands-on EV Programs for Engineering graduates. Payable in installments. Final installment only after placement. Details here - <a href="https://visionastraa.com/track/click.php?email={email}&target={curriculum}&campaign_id={campaign_id}" target="_blank">Fee Details</a></p>
      <br>
      <h4>9. How do I secure my admission?</h4>
      <p>Just pay Admission Fee of INR 10,000 + GST (included in total fees) (Fully refundable if not admitted) - <a href="https://visionastraa.com/track/click.php?email={email}&target={payments}&campaign_id={campaign_id}" target="_blank">Admission Fees</a></p></p>
      <br>
      <h4>10. Will I receive any recognized certification after completing the course?</h4>
      <p>Ans: Yes, you will receive ASDC certified credentials upon successful completion.</p>
      <br>
      <h4>11. Is Placement Guaranteed?</h4>
      <p>Ans: Yes, placement is 100% guaranteed.</p>
      <br>
      <h4>12. What is the package range?</h4>
      <p>Ans: Package range is from INR 4LPA to 12LPA.</p>
      <br>
      <h4>13. What career roles are offered after this course?</h4>
      <p>Ans: Ans: R&D Engineer; Embedded Systems & Firmware Engineer; Product Development & Management Engineer; Testing, Validation & Integration Engineer; EV Systems & Powertrain Engineer; Battery Design & BMS Engineer; Motor Design & Control Engineer.</p>
      <br>
      <h4>14. What if I don't get a job?</h4>
      <p>Ans: If you don't get placed, you'll get your full money back, no questions asked!!</p>
    </div>
    <hr>
    <div style="margin: 20px 0; text-align:center;">
        <img src="https://visionastraa.com/images/home1.jpg" alt="Golden Opportunity" style="max-width:100%; border-radius:8px;">
        <p style="color: #c82333; font-size: 16px; font-weight: bold; margin-top: 10px; text-align: left;">
            Don't Miss this GOLDEN opportunity to secure a guaranteed job in EV Industry by Nov, 2025 if you join today!
        </p>
    </div>
    <p><strong>Admissions are filling up quickly. </strong></p>
    <p><strong>Seats are Limited – Only 25!</strong></p>
    <p><strong>SECURE your SEAT NOW!</strong></p>

    <a href="https://visionastraa.com/track/click.php?email={email}&target={apply}&campaign_id={campaign_id}" class="btn" style="background-color: #28a745;">APPLY NOW</a>
    <a href="https://visionastraa.com/track/click.php?email={email}&target={placements}&campaign_id={campaign_id}" class="btn" style="background-color: #28a745;">PLACEMENTS</a>
    <a href="https://visionastraa.com/track/click.php?email={email}&target={curriculum}&campaign_id={campaign_id}" class="btn" style="background-color: #28a745;">CURRICULUM</a>
    
    <h3 style="color:#d9534f;">🚀 Next Batch starts on August 4th, 2025.</h3>
    <h3>So hurry before spots are taken!</h3>
    <hr>
    <h3 style="font-size: 24px;">See why top companies prefer our graduates:</h3>
    <br>
    <p><strong>🎥 Watch:</strong> Dr. Shiva, Founder of Mecwin Technologies speaks about VisionAstraa.</p>
    <br>    
    <a href="https://visionastraa.com/track/click.php?email={email}&target={youtube}&campaign_id={campaign_id}" target="_blank" style="display:inline-block; position:relative; text-align:center;">
    <img src="https://visionastraa.com/images/hqdefault-overlay.jpg" alt="Watch Video" style="width:100%; max-width:600px; border-radius:8px; display:block;">
    </a>
    <br>
    <hr>
    <h3>Still thinking?</h3>
    <p>Grab your guaranteed EV Job <strong>before someone else does!</strong></p>
    <a href="https://visionastraa.com/track/click.php?email={email}&target={apply}&campaign_id={campaign_id}" class="btn" style="background-color: #28a745;">APPLY NOW</a> 
    <hr>
    <div class="footer" style="text-align: center;">
    <p>Email: <a href="mailto:admissions@visionastraa.com">admissions@visionastraa.com</a></p>
    <p>WhatsApp: +91 8075664438, +91 8197355166</p>
    <p>Talk to CEO: <a href="https://www.linkedin.com/in/nikhiljaincs" target="_blank">Nikhil Jain C S</a></p>

    <hr>

    <p style="font-weight: bold; font-size: 16px; margin-bottom: 10px;">VisionAstraa EV Academy</p>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center" style="border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
        <tr>
            <td style="padding: 0 5px;">
                <a href="https://in.linkedin.com/company/va-ev-academy" target="_blank" style="color: #0077B5; text-decoration: none; font-weight: bold;">LinkedIn</a>
            </td>
            <td style="padding: 0 5px;">
                <a href="https://www.instagram.com/va_ev_academy/" target="_blank" style="color: #C13584; text-decoration: none; font-weight: bold;">Instagram</a>
            </td>
            <td style="padding: 0 5px;">
                <a href="https://www.youtube.com/@VisionAstraaEVAcademy" target="_blank" style="color: #FF0000; text-decoration: none; font-weight: bold;">YouTube</a>
            </td>
        </tr>
    </table>
    <br>
    <p style="font-size:12px;color:#888;">
      If you no longer wish to receive emails from us, you can 
      <a href="https://visionastraa.com/track/unsubscribe.php?email={email}&campaign_id={campaign_id}" style="color:#1a73e8;">unsubscribe here</a>.
    </p>
    </div>
    <img src="{image_url}" width="1" height="1" style="display:none;" />
  </div>
</body>
</html>
"""

def send_email(to_address, first_name):
    vaev_linkedin = quote("https://www.linkedin.com/company/va-ev-academy", safe='')
    apply = quote("https://www.visionastraa.com/ev-application.html", safe='')
    placements = quote("https://www.visionastraa.com/ev-jobs.html", safe='')
    curriculum = quote("https://www.visionastraa.com/ev-course.html", safe='')
    payments = quote("https://www.visionastraa.com/ev-payments.html", safe='')
    youtube = quote("https://www.youtube.com/watch?v=FHFrmgKikOs", safe='')
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
        youtube=youtube
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
)
cursor = conn.cursor(dictionary=True)
# tables = ['test']
tables = ['crdf25', 'crdf25_north', 'crdf25_south']

for tbl in tables:
    cursor.execute(f"SELECT email, first_name FROM {tbl} WHERE state='Kerala' AND emailSent_2=0 AND email NOT IN (SELECT email FROM unsubscribed_emails)")
    for row in cursor.fetchall():
        if send_email(row['email'], row['first_name']):
            print(f"✅ Sent to {row['email']}")
            cursor.execute(f"UPDATE {tbl} SET emailSent_2=1 WHERE email=%s", (row['email'],))
            conn.commit()

cursor.close()
conn.close()
