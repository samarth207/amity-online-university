// Google Apps Script code to handle form submissions and save to Google Sheets
// This code should be deployed as a Web App in Google Apps Script

function doPost(e) {
  try {
    // Log all received data for debugging
    console.log('Full request object:', e);
    if (e.postData) {
      console.log('POST data:', e.postData);
    }
    console.log('Parameters:', e.parameter);
    
    // Get the data from the request parameters (FormData comes through e.parameter)
    const data = e.parameter || {};
    
    // Log the extracted data
    console.log('Extracted data:', data);
    
    // Validate that we have some data
    if (!data.firstName && !data.lastName && !data.email) {
      console.log('No form data found in request');
      throw new Error('No valid form data received. Data keys: ' + Object.keys(data).join(', '));
    }
    
    // Open the Google Sheet by ID
    const SHEET_ID = '1ZS4jsxL37Lm8En8Nj-LoLvyjJEX2ioqn8eDzitoxNoo'; // Your actual Google Sheet ID
    const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
    const sheet = spreadsheet.getActiveSheet();
    
    // Check if this is the first row (add headers if needed)
    if (sheet.getLastRow() === 0) {
      const headers = [
        'Timestamp',
        'Application ID',
        'First Name',
        'Last Name',
        'Email',
        'Country Code',
        'Phone Number',
        'Full Phone',
        'Course',
        'State'
      ];
      
      // Add headers
      sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
      
      // Format headers
      const headerRange = sheet.getRange(1, 1, 1, headers.length);
      headerRange.setFontWeight('bold');
      headerRange.setBackground('#4285f4');
      headerRange.setFontColor('white');
      
      console.log('Headers added to sheet');
    }
    
    // Prepare the row data with fallbacks
    const timestamp = data.timestamp || new Date().toLocaleString('en-US', {
      timeZone: 'Asia/Kolkata',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    });
    
    const rowData = [
      timestamp,
      data.applicationId || 'N/A',
      data.firstName || '',
      data.lastName || '',
      data.email || '',
      data.countryCode || '',
      data.phoneNumber || '',
      data.fullPhone || '',
      data.course || '',
      data.state || ''
    ];
    
    // Log the row data that will be added
    console.log('Row data to be added:', rowData);
    
    // Add the data to the next available row
    sheet.appendRow(rowData);
    
    // Auto-resize columns for better readability
    sheet.autoResizeColumns(1, rowData.length);
    
    console.log('Data successfully added to sheet');
    
    // Return success response
    return ContentService
      .createTextOutput(JSON.stringify({
        status: 'success',
        message: 'Data saved successfully',
        applicationId: data.applicationId,
        timestamp: timestamp
      }))
      .setMimeType(ContentService.MimeType.JSON);
      
  } catch (error) {
    console.error('Error in doPost:', error);
    
    // Return error response
    return ContentService
      .createTextOutput(JSON.stringify({
        status: 'error',
        message: 'Failed to save data: ' + error.toString(),
        details: error.stack
      }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

// Optional: Function to send email notification when a new application is submitted
function sendEmailNotification(data) {
  try {
    const email = 'your-email@domain.com'; // Replace with your email
    const subject = `New Application Received - ${data.applicationId}`;
    
    const body = `
New application received:

Application ID: ${data.applicationId}
Name: ${data.firstName} ${data.lastName}
Email: ${data.email}
Phone: ${data.fullPhone}
Course: ${data.course}
State: ${data.state}
Submitted: ${data.timestamp}

Please review and contact the applicant within 24 hours.
`;
    
    GmailApp.sendEmail(email, subject, body);
  } catch (error) {
    console.error('Email notification failed:', error);
  }
}

// Optional: Function to get all submissions (for admin dashboard)
function getAllSubmissions() {
  try {
    const SHEET_ID = '1ZS4jsxL37Lm8En8Nj-LoLvyjJEX2ioqn8eDzitoxNoo'; // Your actual Google Sheet ID
    const sheet = SpreadsheetApp.openById(SHEET_ID).getActiveSheet();
    
    const data = sheet.getDataRange().getValues();
    
    return ContentService
      .createTextOutput(JSON.stringify({
        status: 'success',
        data: data
      }))
      .setMimeType(ContentService.MimeType.JSON);
      
  } catch (error) {
    return ContentService
      .createTextOutput(JSON.stringify({
        status: 'error',
        message: error.toString()
      }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

// Test function to verify the setup
function testFunction() {
  console.log('Test function started');
  
  const testData = {
    timestamp: new Date().toLocaleString(),
    applicationId: 'TEST001',
    firstName: 'Test',
    lastName: 'User',
    email: 'test@example.com',
    countryCode: '+91',
    phoneNumber: '9876543210',
    fullPhone: '+919876543210',
    course: 'mba',
    state: 'delhi'
  };
  
  const mockEvent = {
    parameter: testData
  };
  
  console.log('Test data:', testData);
  
  try {
    const result = doPost(mockEvent);
    console.log('Test result:', result.getContent());
    return 'Test completed successfully';
  } catch (error) {
    console.error('Test failed:', error);
    return 'Test failed: ' + error.toString();
  }
}