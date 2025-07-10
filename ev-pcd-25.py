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
SMTP_PASSWORD = '1?Q#v!$adw:M'

CAMPAIGN_ID = "ev_pcd_25"

EMAIL_SUBJECT = "VisionAstraa EV Academy - Details about Pooled Placement Drive for ITI/Diploma Candidates for Automotive Companies"

EMAIL_BODY_TEMPLATE = """\
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pooled Placement Drive Invitation</title>
    <style>
        body {{
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }}
        .container {{
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }}
        h2 {{
            color: #4CAF50;
            font-size: 24px;
        }}
        p {{
            font-size: 16px;
            line-height: 1.5;
            margin: 10px 0;
        }}
        ul {{
            list-style-type: disc;
            padding: 8px;
        }}
        ul li {{
            margin: 5px 0;
        }}
        a {{
            color: #1a73e8;
            text-decoration: none;
        }}
        .footer {{
            text-align: center;
            font-size: 14px;
            color: #888888;
            margin-top: 20px;
        }}
        .bold {{
            font-weight: bold;
        }}
        .event-info {{
            background-color: #e8f4e5;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }}
        .event-info p {{
            margin: 5px 0;
        }}
        .cta {{
            background-color: #4CAF50;
            color: #ffffff;
            padding: 10px 20px;
            text-align: center;
            border-radius: 4px;
            font-weight: bold;
        }}
        .cta a {{
            color: #ffffff;
            text-decoration: none;
        }}
        .button {{
            display: inline-block;
            background-color: #25D366;
            color: white;
            padding: 12px 20px;
            margin: 10px 5px;
            border-radius: 4px;
            text-align: center;
            font-weight: bold;
            text-decoration: none;
            width: auto;
        }}
        .button.instagram {{
            background-color: #E1306C;
        }}
        .button:hover {{
            opacity: 0.8;
        }}
        img {{
            max-width: 90%;
            height: auto;
            display: block;
            margin: auto auto;
        }}
        @media only screen and (max-width: 600px) {{
            h2 {{
                font-size: 22px;
            }}
            p, ul {{
                font-size: 14px;
            }}
            .container {{
                padding: 15px;
            }}
            .event-info {{
                padding: 10px;
            }}
            .button {{
                padding: 10px 15px;
                font-size: 14px;
            }}
            .footer {{
                font-size: 12px;
            }}
            img {{
                max-width: 100%;
                height: auto;
            }}
        }}
    </style>
</head>
<body>
    <div class="container">
        <h2>Dear Student,</h2>
        
        <p>We are inviting you to the Pooled Placement Drive that <strong><a href="https://www.instagram.com/va_ev_academy" target="_blank">VisionAstraa EV Academy</a></strong> would be conducting in partnership with Department of Collegiate & Technical Education and Department of Industrial Training & Employment, Government of Karnataka on July 11 (Friday) at 10am in Bengaluru for Diploma & ITI students who have graduated in 2023, 2024 or 2025.</p>

        <div class="event-info">
            <p><strong>Venue Location:</strong><br>
            Sir M Visvesvaraya Chemical Block,<br>
            Sri Jayachamarajendra Government Polytechnic,<br>
            Seshadri Road, K.R Circle, near M S Building, Bengaluru, Karnataka 560008<br>
            <a href="https://maps.app.goo.gl/2gvvL9LNyfuYcgSm7" target="_blank">View on Google Maps</a></p>

            <p><strong>Time:</strong> 9:30am-5pm - All students, please come by 9:30am. You can register yourself in any of the interview rooms at around 11am.</p>
        </div>

        <h3><strong>Rules to Follow:</strong></h3>
        <ul>
            <li><strong>Please bring at least 10 hard copies (Printout) of your resume to submit to interviewers.</strong></li>
            <li><strong>One Student can only be "Selected" by one company or dealership. Once they get an offer letter or screening letter, then they are not supposed to attend another interview.</strong></li>
            <li><strong>Any student who is Rejected or Waitlisted by any company may attend another interview until they get "Selected".</strong></li>
            <li><strong>In case they don't get selected even after multiple interviews or if we reach the end of the event at 5pm, then their resume & details would be shared with the companies who would contact them offline when there are future openings.</strong></li>
        </ul>

        <h3><strong>Things to Bring:</strong></h3>
        <ul>
            <li><strong>Updated Resume - Hard Copy (Print Out) - 10 copies</strong></li>
            <li><strong>Aadhar and PAN Card</strong></li>
            <li><strong>Educational Certificates (Diploma/ITI/HSC/SSLC)</strong></li>
        </ul>

        <p><strong>Companies Confirmed to come for Hiring:</strong><br>
        Please join the assigned room for whichever company you would like to join.</p>

        <img src="https://visionastraa.com/images/ev-pcd.png" alt="image">

        <h3>Program Agenda:</h3>
        <ul>
            <li><strong>10am-11:30am:</strong> Inauguration Ceremony at Main Auditorium (seating capacity 150 - first come basis)</li>
            <li><strong>11:30am-12noon:</strong> Walk-through of all the EV Stalls by Chief Guests - Shri. Priyakrishna, MLA, Govindarajanagar; Shri. Rizwan Arshad, MLA, Shivajinagar; Shri. Nikhil Jain C S, CEO, VisionAstraa EV Academy; Dr. Ragapriya, IAS, Commissioner, Dept. of Industrial Training & Employment; Smt. Manjushree N, IAS, Commissioner, Dept. of Collegiate and Technical Education and other guests doing a walk-through of the stalls by various companies.</li>
            <li><strong>11am - 5pm: Interview process conducted in parallel (Students can register themselves in at any of the interview rooms)</strong></li>
        </ul>

        <h3>For Any Questions:</h3>
        <p>Please connect with us on Instagram: 
        <a href="https://www.instagram.com/va_ev_academy/" target="_blank" class="button instagram">Instagram</a><br>OR<br>
        Join our WhatsApp Group: <br>
        ITI - <a href="https://chat.whatsapp.com/CcSU5lnnTIIK6EGkdsonB8" target="_blank" class="button">Join ITI Group</a><br>
        Engg/Diploma - <a href="https://chat.whatsapp.com/FJrzy3rmuit05neTfzKEy6" target="_blank" class="button">Join Engg/Diploma Group</a></p>

        <p>Thanks,<br>
        <a href="https://www.linkedin.com/in/nikhiljaincs" target="_blank">Nikhil Jain C S</a><br>
        Co-founder & CEO,<br>
        VisionAstraa EV Academy</p>

        <img src="https://visionastraa.com/images/ev-pcd-3.jpg" alt="image">

        <div class="footer">
            <p><strong>Venue Location:</strong><br>
            Sir M Visvesvaraya Chemical Block,<br>
            Sri Jayachamarajendra Government Polytechnic,<br>
            Seshadri Road, K.R Circle, near M S Building, Bengaluru, Karnataka 560008</p>
            <p><strong>Enter from Dr. Ambedkar Road (near KR Circle)</strong><br>
            <a href="https://maps.app.goo.gl/2gvvL9LNyfuYcgSm7" target="_blank">View on Google Maps</a></p>
        </div>
        <img src="{image_url}" width="1" height="1" style="display:none;">
    </div>
</body>
</html>
"""

def send_email(to_address):
    random_token = random.randint(100000, 999999)
    image_url = f"https://visionastraa.com/track/open.php?email={quote(to_address)}&campaign_id={CAMPAIGN_ID}&r={random_token}"

    body = EMAIL_BODY_TEMPLATE.format(
        email=quote(to_address),
        campaign_id=CAMPAIGN_ID,
        image_url=image_url,
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
    user='u707137586_EVA_PCD_25',
    password='Co!&*n3/$p5#',
    database='u707137586_EVA_PCD_25'
)

cursor = conn.cursor(dictionary=True)
tables = ['student_list']

for tbl in tables:
    cursor.execute(f"SELECT email FROM {tbl} WHERE emailSent=0")
    for row in cursor.fetchall():
        if send_email(row['email']):
            print(f"✅ Sent to {row['email']}")
            cursor.execute(f"UPDATE {tbl} SET emailSent=1 WHERE email=%s", (row['email'],))
            conn.commit()

cursor.close()
conn.close()
