<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate phone number
function validatePhone($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if it's a valid length (10 digits for India)
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Extract and sanitize form data
    $formType = isset($input['formType']) ? sanitizeInput($input['formType']) : '';
    $course = isset($input['course']) ? sanitizeInput($input['course']) : 'General';
    $phone = isset($input['phone']) ? sanitizeInput($input['phone']) : '';
    $name = isset($input['name']) ? sanitizeInput($input['name']) : '';
    $email = isset($input['email']) ? sanitizeInput($input['email']) : '';
    $consent = isset($input['consent']) ? (bool)$input['consent'] : false;
    
    // Validation
    $errors = [];
    
    if (empty($formType) || !in_array($formType, ['apply', 'enquire', 'brochure'])) {
        $errors[] = 'Invalid form type';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    } elseif (!validatePhone($phone)) {
        $errors[] = 'Invalid phone number format';
    }
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters long';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Invalid email format';
    }
    
    if (!$consent) {
        $errors[] = 'You must agree to the privacy policy';
    }
    
    // If validation fails, return errors
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    if (!$conn) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please try again later.'
        ]);
        exit;
    }
    
    try {
        // Prepare SQL statement
        $sql = "INSERT INTO form_submissions (form_type, course, phone, name, email, submitted_at) 
                VALUES (:form_type, :course, :phone, :name, :email, NOW())";
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':form_type', $formType);
        $stmt->bindParam(':course', $course);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        
        // Execute statement
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Thank you! Your information has been submitted successfully. Our team will contact you shortly.',
                'submissionId' => $conn->lastInsertId()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to submit form. Please try again.'
            ]);
        }
        
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred. Please try again later.'
        ]);
    }
    
    $conn = null;
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
