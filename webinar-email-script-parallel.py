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
CAMPAIGN_ID = "ev_webinar_2025_11_16"
EMAIL_SUBJECT = "Reminder: Job Opportunities in the EV Industry Webinar - November 16, 05:00 PM (Sun)"
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

    <p style="font-size:16px;">
      <strong>⚡ Secure Your High-Paying EV Job (2025 Grads): FREE Live Webinar & 100% Placement Guarantee 🚀</strong>
    </p>

    <p>
      Are you a 2025 B.Tech/M.Tech graduate (EEE, ECE, Mechanical) still searching for a career path that guarantees 
      massive growth and a premium salary?<br>
      The Electric Vehicle (EV) industry is booming, but only for those with specialized skills.
    </p>

    <p>
      Join this exclusive, free webinar to discover the proven roadmap that has helped VisionAstraa EV Academy place 
      students in top EV roles.
    </p>

    <p><strong>Webinar Focus: VisionAstraa's EV Placement SECRET</strong></p>

    <ul>
      <li>We will reveal exactly how we achieve the industry's boldest promise:</li>
      <ul>
        <li>✅ <strong>100% Placement GUARANTEED</strong> in the EV Industry <strong>or Your ENTIRE Program Fee Back!</strong></li>
        <li>✅ The REAL job opportunities and salary expectations in EV for freshers.</li>
        <li>✅ Why waiting to upskill means missing the industry's biggest hiring wave.</li>
      </ul>
    </ul>

    <p><strong>🗓 Webinar Details</strong></p>

    <table style="border-collapse:collapse;">
      <tr>
        <td style="padding:4px 8px;"><strong>Date</strong></td>
        <td style="padding:4px 8px;">Sunday, November 16</td>
      </tr>
      <tr>
        <td style="padding:4px 8px;"><strong>Time</strong></td>
        <td style="padding:4px 8px;">05:00 PM IST</td>
      </tr>
      <tr>
        <td style="padding:4px 8px;"><strong>Speaker</strong></td>
        <td style="padding:4px 8px;">Yedu Jathavedan, Co-founder & Chairman, VisionAstraa Group</td>
      </tr>
    </table>

    <p style="margin-top:15px;">
      <strong>
        🔥 <a 
          href="https://visionastraa.com/track/click.php?email={email}&target={meet_url}&campaign_id={campaign_id}" 
          target="_blank" 
          style="color:#1a73e8;">CLICK HERE TO SECURE YOUR FREE SPOT NOW (Limited Seats!)</a>
      </strong>
    </p>

    <p>
      <strong>P.S.</strong> This session is for serious 2025 B.Tech/M.Tech (EEE, ECE, Mechanical) graduates ready 
      to leapfrog into a high-demand EV career.
    </p>

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
    dt_start = datetime(2025, 11, 16, 17, 0)
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
UID:visionastraa-ev-webinar-20251116@visionastraa.in
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
      "AC COLLEGE OF ENGINEERING",
      "ADI SHANKARA INSTITUTE OF ENGINEERING AND TECHNOLOGY",
      "AHALIA SCHOOL OF ENGINEERING & TECHNOLOGY",
      "AL AZHAR COLLEGE OF ENGINEERING AND POLYTECHNIC",
      "ALBERTIAN INSTITUTE OF SCIENCE AND TECHNOLOGY (AISAT)-TECHNICAL CAMPUS",
      "AMAL JYOTHI COLLEGE OF ENGINEERING",
      "AMRITA VISHWA VIDYAPEETHAM AMRITAPURI CAMPUS",
      "AWH ENGINEERING COLLEGE",
      "BASELIOS MATHEWS II COLLEGE OF ENGINEERING",
      "BISHOP JEROME INSTITUTE",
      "CARMEL COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "CHINMAYA VISHWAVIDYAPEETH",
      "CHRIST KNOWLEDGE CITY",
      "COCHIN UNIVERSITY COLLEGE OF ENGINEERING KUTTANADU & COCHIN UNIVERSITY",
      "COCHIN UNIVERSITY OF SCIENCE AND TECHNOLOGY",
      "COLLEGE OF DENGINEERING KIDANGOOR",
      "COLLEGE OF ENGINEERING ADOOR",
      "COLLEGE OF ENGINEERING AND MANAGEMENT PUNNAPRA",
      "COLLEGE OF ENGINEERING KALLOOPPARA",
      "COLLEGE OF ENGINEERING KARUNAGAPPALLY",
      "COLLEGE OF ENGINEERING MUNNAR",
      "COLLEGE OF ENGINEERING MUTTATHARA",
      "COLLEGE OF ENGINEERING PERUMON",
      "COLLEGE OF ENGINEERING TRIKARIPUR",
      "COLLEGE OF ENGINEERING TRIVANDRUM",
      "COLLEGE OF ENGINEERING, ARANMULA",
      "COLLEGE OF ENGINEERING, CHERTHALA",
      "COLLEGE OF ENGINEERING, KOTTARAKKARA",
      "COLLEGE OF ENGINEERING, THALASSERY",
      "COLLEGE OF ENGINEERING, VADAKARA",
      "DEPARTMENT OF POLYMER SCIENCE AND RUBBER TECHNOLOGY, COCHIN UNIVERSI",
      "DEPARTMENT OF SHIP TECHNOLOGY, COCHIN UNIVERSITY OF SCIENCE AND TECHNO",
      "ERANAD KNOWLEDGECITY TECHNICAL CAMPUS",
      "FEDERAL INSTITUTE OF SCIENCE AND TECHNOLOGY (FISAT)",
      "GOVERNMENT COLLEGE OF ENGINEERING KANNUR",
      "GOVERNMENT COLLEGE OF ENGINEERING, WAYANAD",
      "GOVERNMENT ENGINEERING COLLEGE IDUKKI",
      "GOVERNMENT ENGINEERING COLLEGE SREEKRISHNAPURAM",
      "GOVERNMENTENGINEERINGCOLLEGETHRISSUR",
      "GOVT. ENGINEERING COLLEGE, KOZHIKODE",
      "GREGORIAN INSTITUTE OF TECHNOLOGY",
      "HEERA COLLEGE OF ENGINEERING AND TECHNOLOGY (HCET)",
      "HOLY GRACE ACADEMY OF ENGINEERING",
      "ICCS COLLEGE OF ENGINEERING AND MANAGEMENT",
      "IES COLLEGE OF ENGINEERING",
      "ILAHIA COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "INDIRA GANDHI INSTITUTE OF ENGINEERING AND TECHNOLOGY",
      "JAIBHARATH COLLEGE OF MANAGEMENT & ENGINEERING TECHNOLOGY",
      "JAIN DEEMED TO BE UNIVERSITY OFF CAMPUS KOCHI",
      "JAWAHARLAL COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "JOHN COX MEMORIAL CSI INSTITUTE OF TECHNOLOGY",
      "JYOTHI ENGINEERING COLLEGE",
      "KELAPPAJI COLLEGE OF AGRICULTURAL ENGINEERING & TECHNOLOGY, TAVANUR",
      "KM COLLEGE OF ENGINEERING",
      "KMCT COLLEGE OF ENGINEERING FOR WOMEN",
      "KMCT INSTITUTE OF EMERGING TECHNOLOGY AND MANAGEMENT",
      "KMEA ENGINEERING COLLEGE",
      "KOTTAYAM INSTITUTE OF TECHNOLOGY AND SCIENCE",
      "KVM COLLEGE OF ENGINEERING & IT",
      "LAYOLA INSTITUTE OF SCIENCE AND TECHNOLOGY",
      "LBS COLLEGE OF ENGINEERING",
      "LBS INSTITUTE OF TECHNOLOGY FOR WOMEN",
      "LOURDES MATHA COLLEGE OF SCIENCE AND TECHNOLOGY",
      "M. DASAN INSTITUTE OF TECHNOLOGY",
      "MAHAGURU INSTITUTE OF TECHNOLOGY",
      "MALABAR COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "MALABAR INSTITUTE OF TECHNOLOGY",
      "MANGALAM COLLEGE OF ENGINEERING",
      "MAR ATHANASIUS COLLEGE OF ENGINEERING, KOTHAMANGALAM",
      "MAR BASELIOS CHRISTIAN COLLEGE OF ENGINEERING & TECHNOLOGY",
      "MAR BASELIOS COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "MARIAN ENGINEERING COLLEGE",
      "MEA ENGINEERING COLLEGE, PERINTHALMANNA",
      "MENTOR ACADEMY FOR DESIGN ENTREPRENEURSHIP INNOVATION AND TECHNOLOGY",
      "MES COLLEGE OF ENGINEERING & TECHNOLOGY",
      "MES INSTITUTE OF TECHNOLOGY & MANAGEMENT",
      "MET'S SCHOOL OF ENGINEERING, MALA",
      "MG COLLEGE OF ENGINEERING",
      "MGM TECHNOLOGICAL CAMPUS",
      "MODEL ENGINEERING COLLEGE",
      "MOHANDAS COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "MOUNT ZION COLLEGE OF ENGINEERING",
      "MOUNT ZION INSTITUTE OF SCIENCE AND TECHNOLOGY",
      "MUSALIAR COLLEGE OF ENGINEERING AND TECHNOLOGY PATHANAMTHITTA",
      "MUSALIAR COLLEGE OF ENGINEERING",
      "MUSLIM ASSOCIATION COLLEGE OF ENGINEERING",
      "MUTHOOT INSTITUTE OF TECHNOLOGY AND SCIENCE",
      "NEHRU COLLEGE OF ENGINEERING AND RESEARCH CENTRE",
      "NSS COLLEGE OF ENGINEERING",
      "P.A.AZIZ COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "PROVIDENCE COLLEGE OF ENGINEERING",
      "RAJADHANI INSTITUTE OF ENGINEERING AND TECHNOLOGY",
      "RAJADHANI INSTITUTE OF SCIENCE AND TECHNOLOGY",
      "RAJAGIRI SCHOOL OF ENGINEERING & TECHNOLOGY",
      "RAJIV GANDHI INSTITUTE OF TECHNOLOGY, KOTTAYAM",
      "ROYAL COLLEGE OF ENGINEERING & TECHNOLOGY",
      "SAHRDAYA COLLEGE OF ENGINEERING & TECHNOLOGY",
      "SAINTS COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "SARABHAI INSTITUTE OF SCIENCE AND TECHNOLOGY",
      "SCHOOL OF OCEAN ENGINEERING AND UNDERWATER TECHNOLOGY",
      "SCMS SCHOOL OF ENGINEERING & TECHNOLOGY",
      "SNGIST GROUP OF INSTITUTIONS",
      "SNM INSTITUTE OF MANAGEMENT AND TECHNOLOGY",
      "SREE BUDDHA COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "SREE CHITRA THIRUNAL COLLEGE OF ENGINEERING",
      "SREE NARAYANA GURU COLLEGE OF ENGINEERING & TECHNOLOGY",
      "SREE NARAYANA GURUKULAM COLLEGE OF ENGINEERING",
      "SREE NARAYANA INSTITUTE OF TECHNOLOGY",
      "SREEPATHY INSTITUTE OF MANAGEMENT AND TECHNOLOGY",
      "ST THOMAS INSTITUTE FOR SCIENCE AND TECHNOLOGY",
      "ST. JOSEPH'S COLLEGE OF ENGINEERING & TECHNOLOGY, PALAI",
      "ST. THOMAS COLLEGE OF ENGINEERING & TECHNOLOGY",
      "ST.THOMAS COLLEGE OF ENGINEERING & TECHNOLOGY",
      "T.K.M INSTITUTE OF TECHNOLOGY",
      "THEJUS ENGINEERING COLLEGE",
      "TKM COLLEGE OF ENGINEERING",
      "TOCH INSTITUTE OF SCIENCE & TECHNOLOGY",
      "TOMS COLLEGE OF ENGINEERING",
      "TRAVANCORE ENGINEERING COLLEGE, OYOOR",
      "TRINITY COLLEGE OF ENGINEERING",
      "UNIVERSAL ENGINEERING COLLEGE",
      "UNIVERSITY COLLEGE OF ENGINEERING",
      "UNIVERSITY COLLEGE OF ENGINEERING.",
      "VALIA KOONAMBAIKULATHAMMA COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "VEDAVYASA INSTITUTE OF TECHNOLOGY",
      "VERGHESE KURIEN INSTITUTE OF DAIRY AND FOOD TECHNOLOGY",
      "VIDYA ACADEMY OF SCIENCE AND TECHNOLOGY TECHNICAL CAMPUS",
      "VIMAL JYOTHI ENGINEERING COLLEGE",
      "VISAT ENGINEERING COLLEGE",
      "YOUNUS COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "VELLORE INSTITUTE OF TECHNOLOGY",
      "SATHYABAMA INSTITUTE OF SCIENCE AND TECHNOLOGY",
      "M.KUMARASAMY COLLEGE OF ENGINEERING",
      "ANNA UNIVERSITY",
      "SAVEETHA INSTITUTE OF MEDICAL AND TECHNICAL SCIENCES",
      "VELLORE INSTITUTE OF TECHNOLOGY CHENNAI OFF CAMPUS",
      "SRI KRISHNA COLLEGE OF TECHNOLOGY",
      "SRM VALLIAMMAI ENGINEERING COLLEGE",
      "SRI KRISHNA COLLEGE OF ENGINEERING AND TECHNOLOGY",
      "SRI VENKATESWARA COLLEGE OF ENGINEERING",
      "PSNA COLLEGE OF ENGINEERING AND TECHNOLOGY , DINDIGUL",
      "VELAMMAL INSTITUTE OF TECHNOLOGY",
      "UNIVERSITY COLLEGE OF ENGINEERING, BITCAMPUS TIRUCHIRAPPALLI",
      "SRM TRP ENGINEERING COLLEGE",
      "S.R. M INSTITUTE OF SCIENCE AND TECHNOLOGY"
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