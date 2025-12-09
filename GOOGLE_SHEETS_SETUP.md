# Google Sheets Integration Setup Guide

This guide will help you connect your Amity Online University application form to Google Sheets using Google Apps Script.

## Prerequisites
- Google Account
- Google Sheets access
- Google Apps Script access

## Step 1: Create a Google Sheet

1. Go to [Google Sheets](https://sheets.google.com)
2. Create a new spreadsheet
3. Rename it to "Amity University Applications" (or any name you prefer)
4. Copy the Sheet ID from the URL:
   ```
   https://docs.google.com/spreadsheets/d/SHEET_ID_HERE/edit
   ```
   The SHEET_ID is the long string between `/d/` and `/edit`

## Step 2: Set up Google Apps Script

1. Go to [Google Apps Script](https://script.google.com)
2. Click "New Project"
3. Replace the default code with the contents of `google-apps-script.js`
4. Replace `YOUR_GOOGLE_SHEET_ID` with your actual Sheet ID (from Step 1)
5. Save the project with a name like "Amity Form Handler"

## Step 3: Deploy as Web App

1. In Google Apps Script, click "Deploy" → "New Deployment"
2. Choose "Web app" as the type
3. Set the configuration:
   - **Execute as**: Me (your email)
   - **Who has access**: Anyone
4. Click "Deploy"
5. **Important**: Copy the Web App URL that's generated
6. Click "Done"

## Step 4: Update Your HTML Form

1. Open `application-form.html`
2. Find this line in the JavaScript:
   ```javascript
   const scriptURL = 'https://script.google.com/macros/s/AKfycbyjVi1uxRmLVe0aJnRble4guJhAKw_IiUGJ-c8565zus06-hQiNCn-si31ylEZklT2k_Q/exec';
   ```
3. Replace `YOUR_SCRIPT_ID` with your actual Web App URL from Step 3

## Step 5: Test the Integration

1. Open your `application-form.html` in a browser
2. Fill out and submit the form
3. Check your Google Sheet - you should see the data appear

## Optional Enhancements

### Email Notifications
To receive email notifications when new applications are submitted:

1. In the Google Apps Script, find the `sendEmailNotification` function
2. Replace `your-email@domain.com` with your actual email
3. Uncomment the line `// sendEmailNotification(data);` in the `doPost` function

### Security Improvements

For production use, consider:

1. **Restrict Access**: Change "Who has access" to "Only myself" and handle authentication
2. **Input Validation**: Add server-side validation in the Google Apps Script
3. **Rate Limiting**: Implement rate limiting to prevent spam
4. **CAPTCHA**: Add CAPTCHA to your form

## Troubleshooting

### Common Issues:

1. **403 Forbidden Error**:
   - Make sure the Web App is deployed with "Anyone" access
   - Check if the correct Web App URL is being used

2. **CORS Errors**:
   - These are normal due to `mode: 'no-cors'` - the form should still work
   - Check your Google Sheet for submitted data

3. **Data Not Appearing**:
   - Verify the Sheet ID is correct
   - Check the Google Apps Script execution log for errors
   - Test the script manually using the `testFunction`

4. **Permission Errors**:
   - Make sure you've authorized the script to access Google Sheets
   - Re-deploy if you made changes to permissions

## Data Structure

The following data will be saved to your Google Sheet:

| Column | Description |
|--------|-------------|
| Timestamp | When the form was submitted |
| Application ID | Unique identifier (AMT + timestamp + random) |
| First Name | Applicant's first name |
| Last Name | Applicant's last name |
| Email | Applicant's email address |
| Country Code | Selected country code |
| Phone Number | 10-digit phone number |
| Full Phone | Complete phone number with country code |
| Course | Selected course/program |
| State | Selected state/region |

## Security Notes

- Never share your Web App URL publicly if it contains sensitive functionality
- Consider implementing authentication for admin functions
- Regularly review and clean your data
- Backup your Google Sheet regularly

## Support

If you encounter issues:

1. Check the Google Apps Script execution log
2. Verify all IDs and URLs are correct
3. Test with the provided `testFunction`
4. Check Google Sheet permissions

---

**Last Updated**: November 2024
**Version**: 1.0