# SENDS EMAILS IN PARALLEL - MUCH FASTER

import mysql.connector
import smtplib
import time
import random
import threading
from concurrent.futures import ThreadPoolExecutor
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.application import MIMEApplication
from datetime import datetime, timedelta, timezone
from urllib.parse import quote

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 587
CAMPAIGN_ID = "ev_webinar_2025_10_22"
EMAIL_SUBJECT = "Job Opportunities in the EV Industry Webinar - October 22, 10:00 AM (Wed)"
MAX_WORKERS = 4

SMTP_CREDENTIALS = [
    {'username': 'careers@visionastraa.in', 'password': 'Z1SIOO0A9b~'},
    {'username': 'visionastraa@evcourse.in', 'password': '>p>W|jv?Kg1'}
]

DB_CONFIG = {
    'host': 'srv1640.hstgr.io',
    'user': 'u707137586_Campus_Hiring',
    'password': '6q+SFd~o[go',
    'database': 'u707137586_Campus_Hiring',
    'connect_timeout': 60
}

EMAIL_BODY_TEMPLATE = """\
<html>
  <body style="font-family:Arial, sans-serif;line-height:1.5;color:#333;">
    <p>Dear {first_name},</p>
    <p>
      Sharing webinar link for <strong>"Job Opportunities in EV Industry"</strong> for <strong>2025 graduates</strong><br>
      (B.Tech & M.Tech) from <strong>EEE, ECE & Mechanical Engineering</strong> branches only.
    </p>
    <p><strong>Date: October 22, Wed</strong></p>
    <p><strong>Time: 10:00 AM, IST</strong></p>
    <p>
      Webinar Link: <a href="https://visionastraa.com/track/click.php?email={email}&target={meet_url}&campaign_id={campaign_id}" 
      target="_blank" style="color:#1a73e8;">Join Webinar</a>
    </p>
    <p><strong>Webinar Details (Virtual):</strong></p>
    <ul>
      <li>
      <strong>"Why VisionAstraa is finding Success in Placing Students in EV Industry?"</strong> - Session Chaired by Yedu Jathavedan, Co-founder & Chairman, VisionAstraa Group
      </li>
      <li>Conversation will cover:
        <ul>
          <li>Why EV?</li>
          <li>Industry Trends</li>
          <li>Job Opportunities in the EV Industry, etc.</li>
          <li>Why we guarantee you 100% placement in EV Industry or Money Back?</li>
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

db_lock = threading.Lock()

def create_ics():
    dt_start = datetime(2025, 10, 22, 10, 0)
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
UID:visionastraa-ev-webinar-20251022@visionastraa.in
ORGANIZER;CN=VisionAstraa Group:mailto:{SMTP_CREDENTIALS[0]['username']}
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

def send_and_update(recipient, smtp_config, db_connection):
    to_address = recipient['email']
    first_name = recipient['first_name']
    table_name = recipient['table']
    
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
    msg['From'] = smtp_config['username']
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
            server.login(smtp_config['username'], smtp_config['password'])
            server.sendmail(smtp_config['username'], to_address, msg.as_string())
        print(f"✅ Sent to {to_address} using {smtp_config['username']}")
    except Exception as e:
        print(f"❌ Mail Error for {to_address}: {e}")
        return 
    
    with db_lock:
        try:
            db_connection.ping(reconnect=True, attempts=3, delay=5)
            cursor = db_connection.cursor()
            cursor.execute(f"UPDATE {table_name} SET emailSent_2=1 WHERE email=%s", (to_address,))
            db_connection.commit()
            cursor.close()
        except mysql.connector.Error as err:
            print(f"❌ DB Update Error for {to_address}: {err}")
            db_connection.rollback()

def main():
    all_recipients = []
    
    print("Connecting to database to fetch recipients...")
    conn = mysql.connector.connect(**DB_CONFIG, autocommit=False)
    cursor = conn.cursor(dictionary=True)
    
    # tables = ['test']
    tables = ['crdf25', 'crdf25_north', 'crdf25_south']
    target_colleges = [
        "SAVEETHA INSTITUTE OF MEDICAL AND TECHNICAL SCIENCES",
        "VELLORE INSTITUTE OF TECHNOLOGY CHENNAI OFF CAMPUS",
        "SRI KRISHNA COLLEGE OF TECHNOLOGY",
        "SRM VALLIAMMAI ENGINEERING COLLEGE",
        "SRI KRISHNA COLLEGE OF ENGINEERING AND TECHNOLOGY",
        "SRI VENKATESWARA COLLEGE OF ENGINEERING",
        "PSNA COLLEGE OF ENGINEERING AND TECHNOLOGY , DINDIGUL",
        "VELAMMAL INSTITUTE OF TECHNOLOGY",
        "UNIVERSITY COLLEGE OF ENGINEERING, BITCAMPUS TIRUCHIRAPPALLI",
        "SAVEETHA ENGINEERING COLLEGE",
        "SONA COLLEGE OF TECHNOLOGY",
        "KPR INSTITUTE OF ENGINEERING AND TECHNOLOGY",
        "ANNA UNIVERSITY REGIONAL CAMPUS COIMBATORE",
        "DR.MAHALINGAM COLLEGE OF ENGINEERING AND TECHNOLOGY",
        "K RAMAKRISHNAN COLLEGE OF TECHNOLOGY",
        "GOVERNMENT COLLEGE OF ENGINEERING, SALEM",
        "PAAVAI ENGINEERING COLLEGE",
        "PERI INSTITUTE OF TECHNOLOGY",
        "SRI RAMAKRISHNA ENGINEERING COLLEGE",
        "ST. JOSEPH'S COLLEGE OF ENGINEERING",
        "KUMARAGURU COLLEGE OF TECHNOLOGY",
        "HINDUSTHAN COLLEGE OF ENGINEERING AND TECHNOLOGY",
        "GOVERNMENT COLLEGE OF ENGINEERING, TIRUNELVELI",
        "KONGU ENGINEERING COLLEGE",
        "BANNARI AMMAN INSTITUTE OF TECHNOLOGY",
        "EASWARI ENGINEERING COLLEGE",
        "GOVERNMENT COLLEGE OF TECHNOLOGY",
        "ST. JOSEPH'S INSTITUTE OF TECHNOLOGY",
        "HINDUSTHAN INSTITUTE OF TECHNOLOGY",
        "GOVERNMENT COLLEGE OF ENGINEERING, BARGUR",
        "K. RAMAKRISHNAN COLLEGE OF ENGINEERING",
        "DR N.G.P. INSTITUTE OF TECHNOLOGY",
        "K.L.N. COLLEGE OF ENGINEERING",
        "GOVERNMENT COLLEGE OF ENGINEERING, SRIRANGAM",
        "SRM TRP ENGINEERING COLLEGE",
        "FRANCIS XAVIER ENGINEERING COLLEGE",
        "VELAMMAL COLLEGE OF ENGINEERING & TECHNOLOGY",
        "JANSONS INSTITUTE OF TECHNOLOGY"
    ]
    college_placeholders = ', '.join(['%s'] * len(target_colleges))
    
    for tbl in tables:
        query = f"""
            SELECT email, first_name, '{tbl}' as `table`
            FROM {tbl} 
            WHERE college IN ({college_placeholders}) 
            AND emailSent_2 = 0 
            AND email NOT IN (SELECT email FROM unsubscribed_emails)
            LIMIT 3000 
        """
        cursor.execute(query, tuple(target_colleges))
        all_recipients.extend(cursor.fetchall())
        
    cursor.close()
    
    if not all_recipients:
        print("No new recipients to email. Exiting.")
        conn.close()
        return

    print(f"Found {len(all_recipients)} total recipients to email.")
    
    num_smtp = len(SMTP_CREDENTIALS)
    recipients_per_smtp = [[] for _ in range(num_smtp)]
    for i, recipient in enumerate(all_recipients):
        recipients_per_smtp[i % num_smtp].append(recipient)

    with ThreadPoolExecutor(max_workers=MAX_WORKERS * num_smtp) as executor:
        for i, smtp_config in enumerate(SMTP_CREDENTIALS):
            email_list = recipients_per_smtp[i]
            if email_list:
                print(f"Submitting {len(email_list)} emails for {smtp_config['username']}...")
                for recipient in email_list:
                    executor.submit(send_and_update, recipient, smtp_config, conn)

    print("All email tasks submitted. Waiting for completion...")
    
    conn.close()
    print("✅ All emails processed. Script finished.")

if __name__ == "__main__":
    main()