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
SMTP_USERNAME = 'careers@visionastraa.in'
SMTP_PASSWORD = '1?Q#v!$adw:M'

CAMPAIGN_ID = "ev_webinar_2025_07_05"

EMAIL_SUBJECT = "Job Opportunities in the EV Industry Webinar - July 05, 11:00 AM (Sat)"

EMAIL_BODY_TEMPLATE = """\
<html>
  <body style="font-family:Arial, sans-serif;line-height:1.5;color:#333;">
    <p>Dear {first_name},</p>
    <p>
      Sharing webinar link for <strong>"Job Opportunities in EV Industry"</strong> for <strong>2025 graduates</strong><br>
      (B.Tech & M.Tech) from <strong>EEE, ECE & Mechanical Engineering</strong> branches only.
    </p>
    <p><strong>Date: July 05, Sat</strong></p>
    <p><strong>Time: 11:00 AM, IST</strong></p>
    <p>
      Webinar Link: <a href="https://visionastraa.com/track/click.php?email={email}&target={meet_url}&campaign_id={campaign_id}" 
      target="_blank" style="color:#1a73e8;">Join Webinar</a>
    </p>
    <p><strong>Webinar Details (Virtual):</strong></p>
    <ul>
      <li>Fireside Chat with Special Guest:<br>
          Rahul Plavullathil, Head of Driveline, FPT Industrial, Turin, Italy (20 min)
      </li>
      <li>Moderated by:<br>
          Yedu Jathavedan, Co-founder & Chairman, VisionAstraa Group
      </li>
      <li>Conversation will cover:
        <ul>
          <li>Why EV?</li>
          <li>Industry Trends</li>
          <li>Job Opportunities in the EV Industry, etc.</li>
        </ul>
      </li>
    </ul>
    <p><strong>To add the webinar to your calendar and receive a reminder, please click "YES" on the calendar invite.</strong></p>
    <p>Best Regards,<br>VisionAstraa Group</p>
    <br>
    <p style="font-size:12px;color:#888;">
      If you no longer wish to receive emails from us, you can 
      <a href="https://visionastraa.com/track/unsubscribe.php?email={email}&campaign_id={campaign_id}" style="color:#1a73e8;">unsubscribe here</a>.
    </p>
    <img src="{image_url}" width="1" height="1" style="display:none;" />
  </body>
</html>
"""

def create_ics():
    dt_start = datetime(2025, 7, 5, 11, 00)
    dt_end = dt_start + timedelta(minutes=60)
    dtstamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    dtstart = dt_start.strftime("%Y%m%dT%H%M%S")
    dtend = dt_end.strftime("%Y%m%dT%H%M%S")
    return f"""BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//VisionAstraa//EV Webinar//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTAMP:{dtstamp}
DTSTART;TZID=Asia/Kolkata:{dtstart}
DTEND;TZID=Asia/Kolkata:{dtend}
SUMMARY:Job Opportunities in EV Industry Webinar
UID:visionastraa-ev-webinar-20250705@visionastraa.in
ORGANIZER;CN=VisionAstraa Group:mailto:{SMTP_USERNAME}
DESCRIPTION:Join the webinar on Job Opportunities in EV Industry.\\nhttps://meet.google.com/prn-gckz-eug
LOCATION:Online (Google Meet)
STATUS:CONFIRMED
SEQUENCE:0
BEGIN:VALARM
TRIGGER:-PT15M
ACTION:DISPLAY
DESCRIPTION:Reminder
END:VALARM
END:VEVENT
END:VCALENDAR
"""

def send_email(to_address, first_name):
    meet_url = quote("https://meet.google.com/prn-gckz-eug", safe='')
    random_token = random.randint(100000, 999999)
    image_url = f"https://visionastraa.com/track/open.php?email={quote(to_address)}&campaign_id={CAMPAIGN_ID}&r={random_token}"

    body = EMAIL_BODY_TEMPLATE.format(
        first_name=first_name,
        email=quote(to_address),
        meet_url=meet_url,
        campaign_id=CAMPAIGN_ID,
        image_url=image_url
    )

    ics_content = create_ics()

    msg = MIMEMultipart('mixed')
    msg['Subject'] = EMAIL_SUBJECT
    msg['From'] = SMTP_USERNAME
    msg['To'] = to_address
    msg.attach(MIMEText(body, 'html'))

    ical = MIMEApplication(ics_content, _subtype='ics')
    ical.add_header('Content-Disposition', 'attachment; filename="invite.ics"')
    ical.add_header('Content-Class', 'urn:content-classes:calendarmessage')
    ical.add_header('Content-ID', 'calendar_message')
    msg.attach(ical)

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

target_colleges = [
    "ST. JOSEPH'S COLLEGE OF ENGINEERING",
    "HINDUSTHAN COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "KUMARAGURU COLLEGE OF TECHNOLOGY",
    "GOVERNMENT COLLEGE OF ENGINEERING, TIRUNELVELI",
    "BANNARI AMMAN INSTITUTE OF TECHNOLOGY",
    "KONGU ENGINEERING COLLEGE",
    "EASWARI ENGINEERING COLLEGE",
    "ST.JOSEPH'S INSTITUTE OF TECHNOLOGY",
    "GOVERNMENT COLLEGE OF TECHNOLOGY",
    "HINDUSTHAN INSTITUTE OF TECHNOLOGY",
    "GOVERNMENT COLLEGE OF ENGINEERING, BARGUR",
    "DR N.G.P. INSTITUTE OF TECHNOLOGY",
    "K. RAMAKRISHNAN COLLEGE OF ENGINEERING",
    "K.L.N. COLLEGE OF ENGINEERING",
    "GOVERNMENT COLLEGE OF ENGINEERING, SRIRANGAM",
    "SRM TRP ENGINEERING COLLEGE",
    "FRANCIS XAVIER ENGINEERING COLLEGE",
    "JANSONS INSTITUTE OF TECHNOLOGY",
    "VELAMMAL COLLEGE OF ENGINEERING & TECHNOLOGY",
    "ALAGAPPA CHETTIAR GOVERNMENT COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "DHANALAKSHMI SRINIVASAN ENGINEERING COLLEGE",
    "SRI RAMAKRISHNA INSTITUTE OF TECHNOLOGY",
    "KIT-KALAIGNARKARUNANIDHI INSTITUTE OF TECHNOLOGY",
    "ANJALAI AMMAL MAHALINGAM ENGINEERING COLLEGE",
    "COIMBATORE INSTITUTE OF TECHNOLOGY",
    "KARUNYA INSTITUTE OF TECHNOLOGY AND SCIENCES",
    "MAR EPHRAEM COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "ST.MOTHER THERESA ENGINEERING COLLEGE",
    "ARUNACHALA COLLEGE OF ENGINEERING FOR WOMEN",
    "PAAVAI COLLEGE OF ENGINEERING",
    "GOVERNMENT COLLEGE OF ENGINEERING",
    "SRM INSTITUTE OF SCIENCE AND TECHNOLOGY RAMAPURAM CAMPUS",
    "N.P.R COLLEGE OF ENGINEERING & TECHNOLOGY",
    "S.R. M INSTITUTE OF SCIENCE AND TECHNOLOGY",
    "V V COLLEGE OF ENGINEERING",
    "J.J. COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "JERUSALEM COLLEGE OF ENGINEERING",
    "SRI SIVASUBRMANIYA NADAR COLLEGE OF ENGINEERING",
    "DMI ENGINEERING COLLEGE",
    "KARPAGA VINAYAGA COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "UNIVERSITY COLLEGE OF ENGINEERING ARNI",
    "ADITHYA INSTITUTE OF TECHNOLOGY",
    "NATIONAL ENGINEERING COLLEGE",
    "KCG COLLEGE OF TECHNOLOGY",
    "MOOKAMBIGAI COLLEGE OF ENGINEERING",
    "ST.JOSEPH COLLEGE OF ENGINEERING",
    "VAIGAI COLLEGE OF ENGINEERING",
    "POLLACHI INSTITUTE OF ENGINEERING AND TECHNOLOGY",
    "ST. JOSEPH'S COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "KRISHNASAMY COLLEGE OF ENGINEERING & TECHNOLOGY",
    "AMRITA VISHWA VIDYAPEETHAM COIMBATORE CAMPUS",
    "MOHAMED SATHAK A.J COLLEGE OF ENGINEERING",
    "KATHIR COLLEGE OF ENGINEERING",
    "MEPCO SCHLENK ENGINEERING COLLEGE",
    "SRI CHANDRA SEKHARENDRA SARASWATHI VISWAMAHA VIDYALAYA",
    "SRM INSTITUTE OF SCIENCE AND TECHNOLOGY RAMAPURAM PART CAMPUS",
    "ADHIPARASAKTHI ENGINEERING COLLEGE",
    "DHANALAKSHMI SRINIVASAN UNIVERSITY PERAMBALUR CAMPUS",
    "DR. SIVANTHI ADITANAR COLLEGE OF ENGINEERING",
    "RAJALAKSHMI ENGINEERING COLLEGE (ENGINEERING & TECHNOLOGY)",
    "SREE KRISHNA COLLEGE OF ENGINEERING",
    "SRI SAI RAM ENGINEERING COLLEGE",
    "SRM INSTITUTE OF SCIENCE AND TECHNOLOGY TIRUCHIRAPPALLI",
    "THANTHAI PERIYAR GOVERNMENT INSTITUTE OF TECHNOLOGY",
    "UNIVERSITY COLLEGE OF ENGINEERING KANCHEEPURAM",
    "VELAMMAL ENGINEERING COLLEGE (ENGG. & TECH)",
    "ADHIYAMAAN COLLEGE OF ENGINEERING (ENGINEERING & TECHNOLOGY)",
    "BHARATH INSTITUTE OF SCIENCE AND TECHNOLOGY",
    "DHANALAKSHMI SRINIVASAN COLLEGE OF ENGINEERING",
    "GRACE COLLEGE OF ENGINEERING",
    "HINDUSTAN INSTITUTE OF TECHNOLOGY AND SCIENCE",
    "KARPAGAM COLLEGE OF ENGINEERING",
    "KNOWLEDGE INSTITUTE OF TECHNOLOGY",
    "NEHRU INSTITUTE OF ENGINEERING AND TECHNOLOGY",
    "PANIMALAR ENGINEERING COLLEGE",
    "SRI SAI RAM INSTITUTE OF TECHNOLOGY",
    "UNIVERSITY COLLEGE OF ENGINEERING PANRUTI",
    "VARUVAN VADIVELAN INSTITUTE OF TECHNOLOGY",
    "ADHI COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "AMRITA VISHWA VIDYAPEETHAM CHENNAI CAMPUS",
    "ARASU ENGINEERING COLLEGE",
    "DHAANISH AHMED INSTITUTE OF TECHNOLOGY",
    "DHANALAKSHMI SRINIVASAN COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "DHIRAJLAL GANDHI COLLEGE OF TECHNOLOGY",
    "EINSTEIN COLLEGE OF ENGINEERING",
    "GNANAMANI COLLEGE OF TECHNOLOGY",
    "INDIRA INSTITUTE OF ENGINEERING AND TECHNOLOGY",
    "JEPPIAAR ENGINEERING COLLEGE (E&T)",
    "K S R COLLEGE OF ENGINEERING",
    "KSK COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "MEENAKSHI RAMASWAMY ENGINEERING COLLEGE",
    "NANDHA ENGINEERING COLLEGE",
    "P.A. COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "P.T.R. COLLEGE OF ENGINEERING & TECHNOLOGY",
    "PALLAVAN COLLEGE OF ENGINEERING",
    "PARK COLLEGE OF TECHNOLOGY",
    "PONJESLY COLLEGE OF ENGINEERING",
    "PSN COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "PSN INSTITUTE OF TECHNOLOGY & SCIENCE",
    "R P SARATHY INSTITUTE OF TECHNOLOGY",
    "R.M.D. ENGINEERING COLLEGE",
    "RAJALAKSHMI INSTITUTE OF TECHNOLOGY",
    "ROHINI COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "SASTRA DEEMED UNIVERSITY",
    "SBM COLLEGE OF ENGINEERING & TECHNOLOGY",
    "SENGUNTHAR ENGINEERING COLLEGE",
    "SHANMUGANATHAN ENGINEERING COLLEGE",
    "SRI KRISHNA INSTITUTE OF TECHNOLOGY",
    "SRI RAMAKRISHNA COLLEGE OF ENGINEERING",
    "UNIVERSITY COLLEGE OF ENGINEERING",
    "V.R.S. COLLEGE OF ENGINEERING AND TECHNOLOGY"
]

college_placeholders = ', '.join(['%s'] * len(target_colleges))

for tbl in tables:
    query = f"""
        SELECT email, first_name FROM {tbl} 
WHERE college IN ({college_placeholders}) 
AND emailSent = 0 
AND email NOT IN (SELECT email FROM unsubscribed_emails)
    """

    cursor.execute(query, target_colleges)

    for row in cursor.fetchall():
        if send_email(row['email'], row['first_name']):
            print(f"✅ Sent to {row['email']}")
            cursor.execute(f"UPDATE {tbl} SET emailSent=1 WHERE email=%s", (row['email'],))
            conn.commit()

cursor.close()
conn.close()
