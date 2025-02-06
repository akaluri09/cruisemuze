<?php
// Enable debugging for errors (remove this after testing)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Error: Method Not Allowed");
}

// Sanitize input
$name = isset($_POST["name"]) ? htmlspecialchars($_POST["name"]) : "Anonymous";
$email = isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : "Not Provided";
$message = isset($_POST["message"]) ? htmlspecialchars($_POST["message"]) : "";
$recipient = "akshitryde@gmail.com"; // Change this to your preferred email

// Validate required fields
if (empty($message)) {
    http_response_code(400);
    die("Error: Message cannot be empty");
}

// Prepare Email Headers
$subject = "New Message from Contact Form";
$headers = "From: " . ($email !== "Not Provided" ? $email : "anonymous@roadczar.com") . "\r\n";
$headers .= "Reply-To: " . ($email !== "Not Provided" ? $email : "no-reply@roadczar.com") . "\r\n";

// Construct email body
$email_body = "Name: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Message:\n$message\n";

// Handle File Upload
$file_attached = false;
if (!empty($_FILES["attachment"]["name"])) {
    $file_name = $_FILES["attachment"]["name"];
    $file_tmp = $_FILES["attachment"]["tmp_name"];
    $file_size = $_FILES["attachment"]["size"];
    $file_type = $_FILES["attachment"]["type"];
    $file_attached = true;
}

// Send Email with or without Attachment
if ($file_attached) {
    $boundary = md5(time());
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
    $message_body = "--$boundary\r\n";
    $message_body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
    $message_body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message_body .= $email_body . "\r\n\r\n";

    // Attach File
    $file_content = chunk_split(base64_encode(file_get_contents($file_tmp)));
    $message_body .= "--$boundary\r\n";
    $message_body .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
    $message_body .= "Content-Transfer-Encoding: base64\r\n";
    $message_body .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n\r\n";
    $message_body .= $file_content . "\r\n\r\n";
    $message_body .= "--$boundary--";

    $success = mail($recipient, $subject, $message_body, $headers);
} else {
    $success = mail($recipient, $subject, $email_body, $headers);
}

// Check if email was sent successfully
if ($success) {
    header("Location: contact.html?success=1");
    exit();
} else {
    http_response_code(500);
    die("Error: Unable to send message. Please try again.");
}
?>
