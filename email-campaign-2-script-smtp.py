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

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 587
SMTP_USERNAME = 'visionastraa@evcourse.in'
SMTP_PASSWORD = '>p>W|jv?Kg1'

CAMPAIGN_ID = "ev_promotional_campaign_2_2025"

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
    <div class="vaev-logo">
      <img src="https://visionastraa.com/images/EV_Academy.png" alt="EV Academy">
    </div>
    <br>
    <h2>🌟 From Rural Roots to EV Industry Leader – Your Journey Starts Here!</h2>
    <h3>ARE YOU LOOKING FOR A JOB IN YOUR CORE FIELD?</h3>
    <h2 style="color: #28a745;">WE GUARANTEE 100% PLACEMENT IN TOP EV COMPANIES!</h2>
    <p>Your Future in Electric Vehicles Starts with <strong><a href="https://visionastraa.com/track/click.php?email={email}&target={vaev_linkedin}&campaign_id={campaign_id}" target="_blank">VisionAstraa EV Academy</a></strong>!</p>
    <br>
    <p>Are you an engineering graduate with dreams of working in India’s fastest‑growing industry – Electric Vehicles?</p>
    <br>
    <p>At <strong><a href="https://visionastraa.com/track/click.php?email={email}&target={vaev_linkedin}&campaign_id={campaign_id}" target="_blank">VisionAstraa EV Academy</a></strong> we understand your journey. Many of our successful students come from rural families, where fathers are farmers, mothers are homemakers, and dreams are big but resources are limited.</p>
    <br>
    <p>And yet – they made it. So can you.</p>
    <p>
      ✅ <strong>100% Placement Guarantee</strong><br>
      ✅ <strong>Average Package: ₹5.5 LPA</strong><br>
      ✅ <strong>Highest Package: ₹12 LPA</strong><br>
      ✅ <strong>Lowest Package: ₹4 LPA</strong>
    </p>
    <br>
    <p>Our <strong>EV Powertrain Design Mastery Program</strong> has helped students from colleges like:</p>
    <ul>
      <li>College of Engineering, Trivandrum (CET)</li>
      <li>Government Engineering College, Thrissur (GECT)</li>
      <li>DSCE, Bengaluru</li>
      <li>Dr. Ambedkar Institute of Technology, Bengaluru</li>
      <li>KLE Institute of Technology, Hubli</li>
      <li>CMR Institute of Technology, Bengaluru</li>
      <li>Sri Siddhartha Institute of Technology, Tumkur</li>
      <li>GEC Ramnagara</li>
      <li>Bapuji Institute of Engineering & Technology, Davangere</li>
      <li>and many more.</li>
    </ul>
    <br>
    <p>They are now working in top EV companies across India – building the vehicles of tomorrow.</p>
    <br>
    <p>If they can, why not you?</p>
    <br>
    <p>📅 New batch starting soon. Limited seats.<br>
       🎓 Scholarships available for deserving rural students.<br>
       🚀 Learn from real industry experts, not just teachers.</p>
    <br>
    <p><strong>Don’t wait. Your future is electric.</strong></p>
    <hr>
    <h3>Placement Stats from 2024</h3>
    <ul>
      <li><strong>Highest Package:</strong> ₹12 LPA</li>
      <li><strong>Average Package:</strong> ₹5.5 LPA</li>
      <li><strong>Lowest Package:</strong> ₹4 LPA (Minimum Package guarantee)</li>
    </ul>
    <p><strong>Our students, on average, got the opportunity to interview with around 5 EV companies and secured multiple offers.</strong></p>
    <hr>
    <h3 style="color: #d9534f;">Our Next Batch Starts – July 2025!</h3>
    <p><strong>Admissions are filling up quickly. HURRY!! Only few seats left.</strong></p>
    <img src="https://visionastraa.com/images/ev-2.jpg" style="width:100%; max-width:600px; border-radius:8px; display:block; margin: auto auto;">
    <p><strong>18/18 – 100% placement from previous batch!</strong></p>
    <p style="color: #c82333; font-size: 16px; font-weight: bold; margin-top: 10px; text-align: left;">
            Don't Miss this GOLDEN opportunity to secure a guaranteed job in EV Industry by Nov, 2025 if you join today!
        </p>
    <a href="https://visionastraa.com/track/click.php?email={email}&target={apply}&campaign_id={campaign_id}" class="btn">APPLY NOW</a>
    <a href="https://visionastraa.com/track/click.php?email={email}&target={placements}&campaign_id={campaign_id}" class="btn">PLACEMENTS</a>
    <a href="https://visionastraa.com/track/click.php?email={email}&target={curriculum}&campaign_id={campaign_id}" class="btn">CURRICULUM</a>
    <hr>
    <h3>Companies that come for Hiring at VisionAstraa EV Academy:</h3>
    <br>
    <img src="https://visionastraa.com/images/ev-3.jpg" style="width:100%; max-width:600px; border-radius:8px; display:block; margin: auto auto;">
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
    <br>
    <a href="https://visionastraa.com/track/click.php?email={email}&target={youtube}&campaign_id={campaign_id}" target="_blank" style="display:inline-block; position:relative; text-align:center;">
    <img src="https://visionastraa.com/images/campaign-2.png" alt="Watch Video" style="width:100%; max-width:600px; border-radius:8px; display:block;">
    </a>
    <br>

    <hr>
    <p>For questions:</p>
    <p>Email: <a href="mailto:admissions@visionastraa.com">admissions@visionastraa.com</a></p>
    <p>Phone: +91 80756 64438, +91 81973 55166</p>
    <p>Talk to our Founder & CEO: <a href="https://www.linkedin.com/in/nikhiljaincs" target="_blank">Nikhil Jain C S</a></p>

    <hr>
    <div class="footer">
      <p style="font-weight: bold; text-align: center;">VisionAstraa EV Academy</p>
      <table align="center">
        <tr>
          <td><a href="https://in.linkedin.com/company/va-ev-academy" target="_blank" style="color:#0077B5; text-decoration:none; font-weight:bold;">LinkedIn</a></td>
          <td><a href="https://www.instagram.com/va_ev_academy/" target="_blank" style="color:#C13584; text-decoration:none; font-weight:bold;">Instagram</a></td>
          <td><a href="https://www.youtube.com/@VisionAstraaEVAcademy" target="_blank" style="color:#FF0000; text-decoration:none; font-weight:bold;">YouTube</a></td>
        </tr>
      </table>
    </div>

    <img src="{image_url}" width="1" height="1" style="display:none;">
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
        youtube=youtube
    )

    msg = MIMEMultipart('mixed')
    msg['Subject'] = EMAIL_SUBJECT
    msg['From'] = SMTP_USERNAME
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
tables = ['test']
# tables = ['crdf25', 'crdf25_north', 'crdf25_south']

for tbl in tables:
    cursor.execute(f"SELECT email, first_name FROM {tbl} WHERE state='Kerala' AND emailSent=0")
    for row in cursor.fetchall():
        if send_email(row['email'], row['first_name']):
            print(f"✅ Sent to {row['email']}")
            cursor.execute(f"UPDATE {tbl} SET emailSent=1 WHERE email=%s", (row['email'],))
            conn.commit()

cursor.close()
conn.close()
