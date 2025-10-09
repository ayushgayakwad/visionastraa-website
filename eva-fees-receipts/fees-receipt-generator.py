import mysql.connector
from docx import Document
from num2words import num2words
import os
from datetime import datetime
from decimal import Decimal
from docx2pdf import convert

def generate_receipts():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    template_path = os.path.join(script_dir, "EVA_Fee_Receipt_Template.docx")
    
    if not os.path.exists(template_path):
        print(f"Error: '{os.path.basename(template_path)}' not found.")
        print(f"Please make sure it's in the same directory as the script: {script_dir}")
        return

    connection = None
    try:
        connection = mysql.connector.connect(
            host="srv1640.hstgr.io",
            user="u707137586_EV_Fees",
            password="@1Qk?gYQ>Ioj",
            database="u707137586_EV_Fees"
        )

        if connection.is_connected():
            print("Successfully connected to the database.")

        cursor = connection.cursor(dictionary=True)

        cursor.execute("SELECT id, full_name, email, phone_number, program_category, base_rate FROM students WHERE payment_status = 'pending'")
        students = cursor.fetchall()

        if not students:
            print("No students found with 'pending' payment status. Exiting.")
            return

        output_dir = os.path.join(script_dir, "generated_receipts")
        if not os.path.exists(output_dir):
            os.makedirs(output_dir)
            print(f"Created directory: {output_dir}")

        for i, student in enumerate(students):
            doc = Document(template_path)

            invoice_number = f"FEE-25B01-{str(i+1).zfill(3)}"
            invoice_date = datetime.now().strftime("%d/%m/%Y")
            rate = student['base_rate']
            gst = rate * Decimal('0.18') 
            total = rate + gst
            total_in_words = f"{num2words(total, lang='en_IN').title()} Paid"

            replacements = {
                "FEE-25B01-00X": invoice_number,
                "0X/0X/2025": invoice_date,
                "[STUDENT FULL NAME]": student['full_name'],
                "[EMAIL]": student['email'],
                "[PHONE]": student['phone_number'],
                "[CATEGORY]": student['program_category'],
                "10,000": f"{rate:,.2f}",
                "1,800": f"{gst:,.2f}",
                "11,800": f"{total:,.2f}",
                "One Lakh and Three Hundred [MENTION “PAID”]": total_in_words,
            }
            
            print(f"\nProcessing {student['full_name']}...")

            for para in doc.paragraphs:
                for key, value in replacements.items():
                    if key in para.text:
                        para.text = para.text.replace(key, str(value))

            for table in doc.tables:
                for row in table.rows:
                    for cell in row.cells:
                        for key, value in replacements.items():
                            if key in cell.text:
                                cell.text = cell.text.replace(key, str(value))
            
            print("  - Replaced placeholders.")

            docx_filename = os.path.join(output_dir, f"Fee_Receipt_{student['full_name']}_{invoice_number}.docx")
            doc.save(docx_filename)
            print(f"  -> Successfully generated DOCX: {os.path.basename(docx_filename)}")

            pdf_filename = os.path.join(output_dir, f"Fee_Receipt_{student['full_name']}_{invoice_number}.pdf")
            try:
                print(f"  - Converting to PDF...")
                convert(docx_filename, pdf_filename)
                print(f"  -> Successfully converted to PDF: {os.path.basename(pdf_filename)}")
            except Exception as e:
                print(f"  - ERROR: Failed to convert to PDF. Please ensure Microsoft Word or LibreOffice is installed. Error: {e}")
                continue

            pdf_data = None
            try:
                with open(pdf_filename, 'rb') as f:
                    pdf_data = f.read()
                print("  - Read PDF file content for database storage.")
            except IOError as e:
                print(f"  - ERROR: Could not read PDF file. Error: {e}")
                continue

            if pdf_data:
                try:
                    update_query = "UPDATE students SET payment_status = 'paid', invoice_number = %s, receipt_pdf = %s WHERE id = %s"
                    cursor.execute(update_query, (invoice_number, pdf_data, student['id']))
                    connection.commit()
                    print(f"  - Updated payment status and saved receipt for {student['full_name']} in the database.")
                except mysql.connector.Error as err:
                    print(f"  - ERROR: Failed to update database with PDF. Error: {err}")
            
            try:
                os.remove(docx_filename)
                os.remove(pdf_filename)
                print("  - Cleaned up local files.")
            except OSError as e:
                print(f"  - WARNING: Could not clean up local files. Error: {e}")

    except mysql.connector.Error as err:
        print(f"Error connecting to database: {err}")
    finally:
        if connection and connection.is_connected():
            cursor.close()
            connection.close()
            print("\nDatabase connection closed.")

if __name__ == "__main__":
    generate_receipts()

