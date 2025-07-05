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

CAMPAIGN_ID = "ev_webinar_2025_07_06"

EMAIL_SUBJECT = "Reminder: Job Opportunities in the EV Industry Webinar - July 06, 10:30 AM (Sun)"

EMAIL_BODY_TEMPLATE = """\
<html>
  <body style="font-family:Arial, sans-serif;line-height:1.5;color:#333;">
    <p>Dear {first_name},</p>
    <p>
      Sharing webinar link for <strong>"Job Opportunities in EV Industry"</strong> for <strong>2025 graduates</strong><br>
      (B.Tech & M.Tech) from <strong>EEE, ECE & Mechanical Engineering</strong> branches only.
    </p>
    <p><strong>Date: July 06, Sun</strong></p>
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
    dt_start = datetime(2025, 7, 6, 10, 30)
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
UID:visionastraa-ev-webinar-20250706@visionastraa.in
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
    "SHARAD INSTITUTE OF TECHNOLOGY COLLEGE OF ENGINEERING",
    "DR. D. Y. PATIL INSTITUTE OF TECHNOLOGY",
    "PROGRESSIVE EDUCATION SOCIETY'S MODERN COLLEGE OF ENGINEERING, PUNE",
    "MODERN EDUCATION SOCIETY'S WADIA COLLEGE OF ENGINEERING",
    "MIT ACADEMY OF ENGINEERING",
    "SANT GAJANAN MAHARAJ COLLEGE OF ENGINEERING",
    "DEPARTMENT OF TECHNOLOGY, SHIVAJI UNIVERSITY, KOLHAPUR",
    "TRINITY COLLEGE OF ENGINEERING & RESEARCH",
    "DR. VITHALRAO VIKHE PATIL COLLEGE OF ENGINEERING AHMEDNAGAR",
    "GOVERNMENT COLLEGE OF ENGINEERING, AURANGABAD STATION ROAD, CHHATRA",
    "MAHARASHTRA INSTITUTE OF TECHNOLOGY",
    "ALL INDIA SHRI SHIVAJI MEMORIAL SOCIETY'S COLLEGE OF ENGINEERING, PUNE-1",
    "M.G.M'S COLLEGE OF ENGINEERING ,NANDED",
    "D. Y. PATIL COLLEGE OF ENGINEERING & TECHNOLOGY",
    "SND COLLEGE OF ENGINEERING & RESEARCH CENTER, BABHULGAON",
    "SNJBS LATE SAU, KANTABAI BHAVARLALJI JAIN COLLEGE OF ENGINEERING",
    "BHARATI VIDYAPEETH COLLEGE OF ENGINEERING, NAVI MUMBAI",
    "PADMABHOOSHAN VASANTRAODADA PATIL INSTITUTE OF TECHNOLOGY",
    "DATTA MEGHE COLLEGE OF ENGINEERING",
    "SWVM'S TATYASAHEB KORE INSTITUTE OF ENGINEERING AND TECHNOLOGY",
    "SYMBIOSIS INSTITUTE OF TECHNOLOGY",
    "ZEAL COLLEGE OF ENGINEERING AND RESEARCH",
    "JAWAHAR EDUCATION SOCIETY'S A. C. PATIL COLLEGE OF ENGINEERING",
    "P.E.S. COLLEGE OF ENGINEERING",
    "SURYODAYA COLLEGE OF ENGINEERING & TECHNOLOGY",
    "MAEER'S MIT COLLEGE OF RAILWAY ENGINEERING AND RESEARCH, BARSHI",
    "DR. J.J. MAGDUM COLLEGE OF ENGINEERING",
    "NBN SINHGAD TECHNICAL INSTITUTES CAMPUS",
    "VIVEKANAND EDUCATION SOCIETY'S INSTITUTE OF TECHNOLOGY",
    "MATOSHRI COLLEGE OF ENGINEERING & RESEARCH CENTRE, NASHIK",
    "MGM'S COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "DHOLE PATIL EDUCATION SOCIETY'S, DHOLE PATIL COLLEGE OF ENGINEERING.",
    "MARATHWADA MITRA MANDAL'S INSTITUTE OF TECHNOLOGY",
    "MKSSS'S CUMMINS COLLEGE OF ENGINEERING FOR WOMEN",
    "MCT'S RAJIV GANDHI INSTITUTE OF TECHNOLOGY, MUMBAI",
    "SANJAY GHODAWAT INSTITUTE",
    "SHANTI EDUCATION SOCIETY'S, A.G. PATIL INSTITUTE OF TECHNOLOGY",
    "KEYSTONE SCHOOL OF ENGINEERING",
    "TRINITY ACADEMY OF ENGINEERING, PUNE",
    "COEP TECHNOLOGICAL UNIVERSITY",
    "GH RAISONI COLLEGE OF ENGINEERING & MANAGEMENT, NAGPUR",
    "KJ COLLEGE OF ENGINEERING & MANAGEMENT RESEARCH",
    "SMT INDIRA GANDHI COLLEGE OF ENGINEERING",
    "PADMASHRI DR. V.B. KOLTE COLLEGE OF ENGINEERING",
    "NANASAHEB MAHADIK COLLEGE OF ENGINEERING",
    "D.Y.PATIL COLLEGE OF ENGINEERING",
    "INTERNATIONAL INSTITUTE OF INFORMATION TECHNOLOGY (IAIT)",
    "RIZVI COLLEGE OF ENGINEERING",
    "SHREEYASH PRATISHTHAN'S, SHREEYASH COLLEGE OF ENGINEERING & TECHNOLOGY",
    "COLLEGE OF ENGINEERING",
    "THADOMAL SHAHANI ENGINEERING COLLEGE",
    "ALARD COLLEGE OF ENGINEERING AND MANAGEMENT",
    "FR. C. RODRIGUES INSTITUTE OF TECHNOLOGY",
    "G.H. RAISONI COLLEGE OF ENGINEERING & MANAGEMENT",
    "GHARDA FOUNDATION'S GHARDA INSTITUTE OF TECHNOLOGY",
    "JAWAHARLAL DARDA INSTITUTE OF ENGINEERING & TECHNOLOGY",
    "SGB AMRAVATI UNIVERSITY",
    "HINDI SEVA MANDAL'S, SHRI SANT GADGE BABA COLLEGE OF ENGINEERING & TECH",
    "KARMAVEER BHAURAD PATIL COLLEGE OF ENGINEERING, SATARA",
    "NAVSAHYADRI EDUCATION SOCIETY'S GROUP OF INSTITUTIONS",
    "RAJIV GANDHI COLLEGE OF ENGINEERING",
    "VISHWANIKETAN'S INSTITUTE OF MANAGEMENT ENTREPRENEURSHIP AND ENGINEERING TECHNOLOGY (I MEET)",
    "BRAHMA VALLEY COLLEGE OF ENGINEERING AND REASERACH INSTITUTE",
    "DR. V K PATIL COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "N. B. NAVALE SINHGAD COLLEGE OF ENGINEERING",
    "NEW HORIZON INSTITUTE OF TECHNOLOGY & MANAGEMENT",
    "SINHGAD COLLEGE OF ENGINEERING",
    "SIPNA COLLEGE OF ENGINEERING & TECHNOLOGY",
    "YASHODA TECHNICAL CAMPUS SATARA",
    "DR.D.Y.PATIL PRATISHTHAN'S CO",
    "FR. CONCEICAO RODRIGUES COLLEGE OF ENGINEERING",
    "S.V.P.M.'S COLLEGE OF ENGINEERING",
    "DR. BABASAHEB AMBEDKAR TECHNOLOGICAL UNIVERSITY, LONERE",
    "GENBA SOPANRAO MOZE TRUST'S PARVATIBAI GENBA MOZE COLLEGE OF ENGINEER",
    "MET'S INSTITUTE OF TECHNOLOGY",
    "VIVA INSTITUTE OF TECHNOLOGY",
    "AJEENKYA DY PATIL SCHOOL OF ENGINEERING",
    "ANNASAHEB DANGE COLLEGE OF ENGINEERING AND TECHNOLOGY, ASHTA",
    "D.K.T.E. SOCIETY'S TEXTILE & ENGNEERING INSTITUTE",
    "DARSH INSTITUTE OF TECHNOLOGY & RESEARCH CENTRE",
    "KOLHAPUR INSTITUTE OF TECHNOLOGYÁS COLLEGE OF ENGINEERING AUTONOMOU",
    "MIT VISHWAPRAYAG UNIVERSITY",
    "RAMRAO ADIK INSTITUTE OF TECHNOLOGY",
    "SANDIP INSTITUE OF ENGINEERING & MANAGEMENT",
    "SANDIPANI TECHNICAL AND MEDICAL EDUCATION INSTITUTE'S SANDIPANI TECHNIC",
    "SHIVAJIRAO S. JONDHLE COLLEGE OF ENGINEERING &TECHNOLOGY",
    "SHREE L.R. TIWARI COLLEGE OF ENGINEERING",
    "USHA MITTAL INSTITUTE OF TECHNOLOGY",
    "YESHWANTRAO CHAVAN COLLEGE OF ENGINEERING",
    "AMRUTVAHINI COLLEGE OF ENGINEERING, SANGAMNER",
    "ANANTRAO PAWAR COLLEGE OF ENGINEERING & RESEARCH",
    "ANJUMAN COLLEGE OF ENGINEERING & TECHNOLOGY",
    "ANJUMAN-I-ISLAM'S M. H. SABOO SIDDIK COLLEGE OF ENGINEERING",
    "AURANGABAD COLLEGE OF ENGINEERING",
    "BHARAT COLLEGE OF ENGINEERING",
    "BHARATI VIDYAPEETH DEEMED UNIVERSITY COLLEGE OF ENGINEERING",
    "BHARATI VIDYAPEETH'S COLLEGE OF ENGINEERING, LAVALE, PUNE",
    "COLLEGE OF ENGINEERING AND TECHNOLOGY",
    "D. Y. PATIL EDUCATION SOCIETY DEEMED TO BE UNIVERSITY",
    "DR DY PATIL VIDYAPEETH PUNE",
    "G. H. RAISONI COLLEGE OF ENGINEERING, NAGPUR",
    "GANGAMAI COLLEGE OF ENGINEERING",
    "GENBA SOPANRAO MOZE COLLEGE OF ENGINEERING",
    "GH RAISONI COLLEGE OF ENGINEERING AND MANAGEMENT, JALGAON",
    "GOVERNMENT COLLEGE OF ENGINEERING NAGPUR",
    "GOVERNMENT COLLEGE OF ENGINEERING, RATNAGIRI",
    "GURU GOBIND SINGH COLLEGE OF ENGINEERING AND RESEARCH CENTRE, NASHIK",
    "INDIRA COLLEGE OF ENGINEERING & MANAGEMENT",
    "JAYWANT COLLEGE OF ENGINEERING AND POLYTECHNIC",
    "K. K. WAGH INSTITUTE OF ENGINEERING EDUCATION & RESEARCH",
    "KARMAYOGI INSTITUTE OF TECHNOLOGY",
    "KOTI VIDYA CHARITABLE TRUST'S ALAMURI RATNAMALA INSTITUTE OF ENGINEERIN",
    "M.S BIDVE ENGINEERING COLLEGE",
    "P. R. POTE PATIL COLLEGE OF ENGINEERING AND MANAGEMENT, AMRAVATI",
    "PADMABHOOSHAN VASANTDADA PATIL INSTITUTE OF TECHNOLOGY, PUNE", "PILLAI COLLEGE OF ENGINEERING",
    "PRAVARA RURAL ENGINEERING COLLEGE",
    "PROF. RAM MEGHE INSTITUTE OF TECHNOLOGY AND RESEARCH",
    "PVG'S COLLEGE OF ENGINEERING AND TECHNOLOGY AND G.K. PATE (WANI) INSTITUTE OF MANAGEMENT",
    "SHRI VILE PARLE KELAVANI MANDAL'S INSTITUTE OF TECHNOLOGY, DHULE",
    "SMT. KASHIBAI NAVALE COLLEGE OF ENGINEERING",
    "SOMAYYA INSTITUTE OF TECHNOLOGY",
    "ST. JOHN COLLEGE OF ENGINEERING AND MANAGEMENT",
    "THAKUR COLLEGE OF ENGINEERING & TECHNOLOGY",
    "THEEM COLLEGE OF ENGINEERING",
    "UNIVERSAL COLLEGE OF ENGINEERING & RESEARCH",
    "VIDYA VIKAS PRATISHTHAN INSTITUTE OF ENGINEERING & TECHNOLOGY, SOLAPUR",
    "VISHWATMAK JANGLI MAHARAJ ASHRAM TRUST(KOKAMTHAN) ATMA MALIK INSTITUTE OF TECHNOLOGY"
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
