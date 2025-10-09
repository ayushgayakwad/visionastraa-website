import mysql.connector
from docx import Document
from num2words import num2words
import os
from datetime import datetime
from decimal import Decimal

def generate_receipts():
    script_dir = os.path.dirname(os.path.abspath(__file__))
    template_path = os.path.join(script_dir, "EVA_Fee_Receipt_Template.docx")
    
    if not os.path.exists(template_path):
        print(f"Error: '{os.path.basename(template_path)}' not found.")
        print(f"Please make sure it's in the same directory as the script: {script_dir}")
        return

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
                        inline = para.runs
                        for j in range(len(inline)):
                            if key in inline[j].text:
                                text = inline[j].text.replace(key, value)
                                inline[j].text = text
                                print(f"  - Replaced '{key}' in paragraph.")

            for table in doc.tables:
                for row in table.rows:
                    for cell in row.cells:
                        for key, value in replacements.items():
                            if key in cell.text:
                                cell.text = cell.text.replace(key, value)
                                print(f"  - Replaced '{key}' in table cell.")

            output_filename = os.path.join(output_dir, f"Fee_Receipt_{student['full_name']}_{invoice_number}.docx")
            doc.save(output_filename)
            print(f"  -> Successfully generated receipt: {output_filename}")

            update_query = "UPDATE students SET payment_status = 'paid', invoice_number = %s WHERE id = %s"
            cursor.execute(update_query, (invoice_number, student['id']))
            connection.commit()
            print(f"  - Updated payment status for {student['full_name']} in the database.")


    except mysql.connector.Error as err:
        print(f"Error connecting to database: {err}")
    finally:
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()
            print("\nDatabase connection closed.")

if __name__ == "__main__":
    generate_receipts()

