import pandas as pd

# Define the column names as you provided
column_names = ["name", "email", "phone", "college", "branch", "internship", "status"]

# Specify the name of your CSV file
file_name = 'D:\\GitHub\\visionastraa-website\\VTU EV Internship Offer Letters\\scrappers and tools\\offer_released_applicants.csv'
# Define the new file to store duplicates
output_file_name = 'D:\\GitHub\\visionastraa-website\\VTU EV Internship Offer Letters\\scrappers and tools\\duplicate_applicants.csv'

try:
    # Read the CSV file, specifying it has no header
    # and assigning the column names
    df = pd.read_csv(file_name, header=None, names=column_names)

    # Find all rows that have a duplicate email.
    # keep=False ensures that all occurrences of a duplicate are marked.
    duplicates_df = df[df.duplicated(subset=['email'], keep=False)]

    if duplicates_df.empty:
        print("No duplicate records found based on the 'email' column.")
    else:
        # Sort by email to group duplicates together for easier reading
        duplicates_df_sorted = duplicates_df.sort_values(by='email')
        
        # Save the sorted duplicates to a new CSV file
        # index=False prevents pandas from writing the DataFrame index as a column
        duplicates_df_sorted.to_csv(output_file_name, index=False)
        
        print(f"Successfully saved {len(duplicates_df_sorted)} duplicate records to '{output_file_name}'.")

except FileNotFoundError:
    print(f"Error: The file '{file_name}' was not found.")
except Exception as e:
    print(f"An error occurred: {e}")