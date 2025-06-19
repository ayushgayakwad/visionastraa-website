# CURRENTLY SENDS 2 EMAILS EVERY 3 SECONDS

import mysql.connector
import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.application import MIMEApplication
from datetime import datetime, timedelta, timezone
from queue import Queue
from threading import Thread

SMTP_SERVER = 'smtp.hostinger.com'
SMTP_PORT = 587
SMTP_USERNAME = 'careers@visionastraa.in'
SMTP_PASSWORD = '4@upm7$K'

EMAIL_SUBJECT = "Job Opportunities in EV Industry - June 20, 10:30am (Fri)"
MAX_WORKERS = 2

EMAIL_BODY_TEMPLATE = """\
<html>
  <body style="font-family: Arial, sans-serif; line-height: 1.5; color: #333;">
    <p>Dear {first_name},</p>
    <p>
      Sharing a Webinar Link for <strong>"Job Opportunities in EV Industry"</strong> for <strong>2025 graduates</strong><br>
      (B.Tech & M.Tech) from <strong>EEE, ECE & Mechanical Engineering</strong> branches only.
    </p>
    <p>
      <strong>Date:</strong> June 20, Fri<br>
      <strong>Time:</strong> 10:30am IST
    </p>
    <p>
      Webinar Link: <a href="https://meet.google.com/prn-gckz-eug" target="_blank" style="color: #1a73e8;">https://meet.google.com/prn-gckz-eug</a>
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
  </body>
</html>
"""

def create_ics():
    dt_start = datetime(2025, 6, 20, 10, 30)
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
UID:visionastraa-ev-webinar-20250620@visionastraa.in
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

def create_message(to_address, first_name):
    msg = MIMEMultipart('mixed')
    msg['Subject'] = EMAIL_SUBJECT
    msg['From'] = SMTP_USERNAME
    msg['To'] = to_address
    body = EMAIL_BODY_TEMPLATE.format(first_name=first_name)
    msg.attach(MIMEText(body, 'html'))

    ics_content = create_ics()
    ical_part = MIMEApplication(ics_content, _subtype='ics')
    ical_part.add_header('Content-Disposition', 'attachment', filename='invite.ics')
    msg.attach(ical_part)

    return msg

def worker(email_queue, successful_emails):
    try:
        server = smtplib.SMTP(SMTP_SERVER, SMTP_PORT)
        server.starttls()
        server.login(SMTP_USERNAME, SMTP_PASSWORD)
    except Exception as e:
        print(f"❌ Worker login failed: {e}")
        return

    while True:
        try:
            to_address, first_name = email_queue.get(timeout=5)
        except:
            break

        try:
            msg = create_message(to_address, first_name)
            server.sendmail(SMTP_USERNAME, to_address, msg.as_string())
            print(f"✅ Email sent to: {to_address}")
            successful_emails.append(to_address)
        except Exception as e:
            print(f"❌ Failed to send to {to_address}: {e}")
        finally:
            email_queue.task_done()

    server.quit()

def main():
    conn = mysql.connector.connect(
        host='srv1640.hstgr.io',
        user='u707137586_Campus_Hiring',
        password='6q+SFd~o[go',
        database='u707137586_Campus_Hiring'
    )
    cursor = conn.cursor(dictionary=True)

    # tables = ['test']
    tables = ['crdf25', 'crdf25_north', 'crdf25_south']

    all_users = []
    table_map = {}

    for table in tables:
        cursor.execute(f"SELECT * FROM {table} WHERE state = 'Kerala' AND emailSent = 0")
        rows = cursor.fetchall()
        for row in rows:
            all_users.append((row['email'], row['first_name']))
            table_map[row['email']] = table

    email_queue = Queue()
    for user in all_users:
        email_queue.put(user)

    successful_emails = []
    threads = []

    for _ in range(min(MAX_WORKERS, email_queue.qsize())):
        t = Thread(target=worker, args=(email_queue, successful_emails))
        t.daemon = True
        t.start()
        threads.append(t)

    email_queue.join()

    for email in successful_emails:
        table = table_map.get(email)
        if table:
            cursor.execute(f"UPDATE {table} SET emailSent = 1 WHERE email = %s", (email,))

    conn.commit()
    cursor.close()
    conn.close()

if __name__ == "__main__":
    main()
