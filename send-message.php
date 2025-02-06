<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);
    $recipient = "akshitryde@gmail.com"; // Change this to your preferred email
    
    // Handle File Upload
    $file_attached = false;
    if (!empty($_FILES["attachment"]["name"])) {
        $file_name = $_FILES["attachment"]["name"];
        $file_tmp = $_FILES["attachment"]["tmp_name"];
        $file_size = $_FILES["attachment"]["size"];
        $file_type = $_FILES["attachment"]["type"];
        $file_attached = true;
    }

    // Prepare Email
    $subject = "New Message from Contact Form";
    $headers = "From: " . ($email ?: "anonymous@roadczar.com") . "\r\n";
    $headers .= "Reply-To: " . ($email ?: "no-reply@roadczar.com") . "\r\n";

    $email_body = "Name: " . ($name ?: "Anonymous") . "\n";
    $email_body .= "Email: " . ($email ?: "Not Provided") . "\n";
    $email_body .= "Message:\n" . $message . "\n";

    // Send Email
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

        mail($recipient, $subject, $message_body, $headers);
    } else {
        mail($recipient, $subject, $email_body, $headers);
    }

    // Redirect after submission
    header("Location: contact.html?success=1");
    exit();
}
?>
