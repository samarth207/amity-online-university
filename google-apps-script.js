function doPost(e) {
  // Ultra-safe error handling
  try {
    console.log('=== FORM SUBMISSION START ===');
    console.log('Full event object:');
    console.log(JSON.stringify(e, null, 2));
    
    // Get data safely - FormData comes through e.parameter
    const data = e.parameter || {};
    
    console.log('Extracted form data:');
    console.log(JSON.stringify(data, null, 2));
    
    // Validate we have actual form data
    const hasFormData = data.firstName || data.email || Object.keys(data).length > 0;
    
    if (!hasFormData) {
      throw new Error('No form data received. Keys found: ' + Object.keys(data).join(', '));
    }
    
    // Your Google Sheet ID
    const SHEET_ID = '1ZS4jsxL37Lm8En8Nj-LoLvyjJEX2ioqn8eDzitoxNoo';
    
    console.log('Opening Google Sheet with ID:', SHEET_ID);
    
    // Open the sheet
    const spreadsheet = SpreadsheetApp.openById(SHEET_ID);
    const sheet = spreadsheet.getActiveSheet();
    
    console.log('Sheet opened successfully. Current rows:', sheet.getLastRow());
    
    // Add headers if this is the first submission
    if (sheet.getLastRow() === 0) {
      console.log('Adding headers to empty sheet...');
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
      
      sheet.getRange(1, 1, 1, headers.length).setValues([headers]);
      
      // Style the headers
      const headerRange = sheet.getRange(1, 1, 1, headers.length);
      headerRange.setFontWeight('bold');
      headerRange.setBackground('#4285f4');
      headerRange.setFontColor('white');
      
      console.log('Headers added successfully');
    }
    
    // Prepare the data row
    const timestamp = new Date().toLocaleString('en-US', { timeZone: 'Asia/Kolkata' });
    
    const rowData = [
      timestamp,
      data.applicationId || 'N/A',
      data.firstName || '',
      data.lastName || '',
      data.email || '',
      data.countryCode || '',
      data.phoneNumber || '',
      data.fullPhone || (data.countryCode + data.phoneNumber),
      data.course || '',
      data.state || ''
    ];
    
    console.log('Prepared row data:');
    console.log(JSON.stringify(rowData, null, 2));
    
    // Add the data to the sheet
    sheet.appendRow(rowData);
    
    console.log('Data added to sheet successfully!');
    
    // Auto-resize columns
    sheet.autoResizeColumns(1, rowData.length);
    
    console.log('=== FORM SUBMISSION SUCCESS ===');
    
    // Return success response
    const response = {
      status: 'success',
      message: 'Application submitted successfully!',
      applicationId: data.applicationId,
      timestamp: timestamp
    };
    
    return ContentService
      .createTextOutput(JSON.stringify(response))
      .setMimeType(ContentService.MimeType.JSON);
    
  } catch (error) {
    console.error('=== FORM SUBMISSION ERROR ===');
    console.error('Error message:', error.message);
    console.error('Error stack:', error.stack);
    console.error('================================');
    
    // Return error response with details
    const errorResponse = {
      status: 'error',
      message: error.message,
      details: error.stack,
      timestamp: new Date().toLocaleString()
    };
    
    return ContentService
      .createTextOutput(JSON.stringify(errorResponse))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

// Test function - run this first to verify everything works
function testFormSubmission() {
  console.log('Starting test...');
  
  const testData = {
    applicationId: 'TEST_' + Date.now(),
    timestamp: new Date().toLocaleString(),
    firstName: 'John',
    lastName: 'Doe', 
    email: 'john.doe@test.com',
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
  
  const result = doPost(mockEvent);
  const resultText = result.getContent();
  
  console.log('Test result:', resultText);
  
  return resultText;
}