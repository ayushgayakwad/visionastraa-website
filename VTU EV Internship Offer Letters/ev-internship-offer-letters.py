import pandas as pd
import fitz 
from reportlab.pdfgen import canvas
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet
from reportlab.platypus import Paragraph
import os
import io

CSV_FILE_PATH = 'D:\\GitHub\\visionastraa-website\\VTU EV Internship Offer Letters\\applicants-vtu-internyet.csv'
PDF_TEMPLATE_PATH = 'D:\\GitHub\\visionastraa-website\\VTU EV Internship Offer Letters\\Template.pdf'
OUTPUT_DIRECTORY = 'D:\\GitHub\\visionastraa-website\\VTU EV Internship Offer Letters\\Generated_Offer_Letters'

def get_letter_paragraph(name, role):
    styles = getSampleStyleSheet()
    style = styles['BodyText']
    style.fontName = 'Times-Roman'
    style.fontSize = 14
    style.leading = 18 

    text = f"""
    Dear <b>{name}</b>,<br/><br/>
    We are pleased to offer you an Internship in <b>{role}</b> at <b>VisionAstraa EV Academy</b> with effect from 3rd September 2025. We are excited to welcome you onboard.<br/><br/>
    As an Intern, you will be part of a collaborative environment where you will learn and contribute to impactful projects in the electric vehicle (EV) domain. You will gain hands-on experience and have the opportunity to apply your academic knowledge to real-world applications while developing practical skills that will strengthen your career prospects.<br/><br/>
    At <b>VisionAstraa EV Academy</b>, we are committed to providing a supportive and enriching learning environment. Our aim is to empower you with relevant, hands-on experience and to support your growth in the EV industry.<br/><br/>
    We look forward to working with you and hope this internship will be a valuable step in your professional journey.
    """
    return Paragraph(text, style)

def create_offer_letter(name, role):
    try:
        packet = io.BytesIO()
        c = canvas.Canvas(packet, pagesize=letter)
        
        page_width, page_height = letter

        p = get_letter_paragraph(name, role)
        
        text_width = 520 
        w, h = p.wrapOn(c, text_width, 400)

        top_margin = page_height * 0.775
        y_position = top_margin - h

        left_margin = 45
        p.drawOn(c, left_margin, y_position)
        c.save()

        packet.seek(0)
        text_pdf = fitz.open(stream=packet, filetype="pdf")
        template_pdf = fitz.open(PDF_TEMPLATE_PATH)
        
        template_page = template_pdf[0]
        template_page.show_pdf_page(template_page.rect, text_pdf, 0)

        safe_filename = "".join([c for c in name if c.isalpha() or c.isdigit() or c==' ']).rstrip()
        output_filepath = os.path.join(OUTPUT_DIRECTORY, f"Offer_Letter_{safe_filename}.pdf")
        
        template_pdf.save(output_filepath)
        template_pdf.close()
        
        print(f"Successfully created offer letter for: {name}")

    except Exception as e:
        print(f"Error creating PDF for {name}: {e}")

def main():
    if not os.path.exists(CSV_FILE_PATH):
        print(f"Error: Input data file not found at '{CSV_FILE_PATH}'")
        return
    if not os.path.exists(PDF_TEMPLATE_PATH):
        print(f"Error: PDF template not found at '{PDF_TEMPLATE_PATH}'")
        return

    if not os.path.exists(OUTPUT_DIRECTORY):
        os.makedirs(OUTPUT_DIRECTORY)
        print(f"Created output directory: '{OUTPUT_DIRECTORY}'")

    try:
        df = pd.read_csv(CSV_FILE_PATH, header=None)
        for index, row in df.iterrows():
            name = str(row[1]).strip()
            role = str(row[3]).strip()
            
            if name and role and name.lower() != 'nan' and role.lower() != 'nan':
                create_offer_letter(name, role)
            else:
                print(f"Skipping row {index+1} due to missing name or role.")

        print("\nOffer letter generation complete.")

    except Exception as e:
        print(f"An unexpected error occurred: {e}")

if __name__ == "__main__":
    main()
