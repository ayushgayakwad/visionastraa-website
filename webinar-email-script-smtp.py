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

CAMPAIGN_ID = "ev_webinar_2025_07_04"

EMAIL_SUBJECT = "Reminder: Job Opportunities in the EV Industry Webinar - July 04, 10:30 AM (Fri)"

EMAIL_BODY_TEMPLATE = """\
<html>
  <body style="font-family:Arial, sans-serif;line-height:1.5;color:#333;">
    <p>Dear {first_name},</p>
    <p>
      Sharing webinar link for <strong>"Job Opportunities in EV Industry"</strong> for <strong>2025 graduates</strong><br>
      (B.Tech & M.Tech) from <strong>EEE, ECE & Mechanical Engineering</strong> branches only.
    </p>
    <p><strong>Date: July 04, Fri</strong></p>
    <p><strong>Time: 10:30 AM, IST</strong></p>
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
    dt_start = datetime(2025, 7, 4, 10, 30)
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
UID:visionastraa-ev-webinar-20250704@visionastraa.in
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
    "VELLORE INSTITUTE OF TECHNOLOGY",
    "SATHYABAMA INSTITUTE OF SCIENCE AND TECHNOLOGY",
    "M.KUMARASAMY COLLEGE OF ENGINEERING",
    "ANNA UNIVERSITY",
    "SAVEETHA INSTITUTE OF MEDICAL AND TECHNICAL SCIENCES",
    "VELLORE INSTITUTE OF TECHNOLOGY CHENNAI OFF CAMPUS",
    "SRI KRISHNA COLLEGE OF TECHNOLOGY",
    "SRM VALLIAMMAI ENGINEERING COLLEGE"
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
