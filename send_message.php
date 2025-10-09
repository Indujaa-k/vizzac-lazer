<?php
header('Content-Type: application/json; charset=utf-8');

// Helper: prevent header injection
function sanitize_header($value)
{
  return trim(preg_replace('/[\r\n]+/', ' ', $value));
}

// Get POST values (use null-coalescing in case fields are missing)
$name    = isset($_POST['name']) ? trim($_POST['name']) : '';
$email   = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$product = isset($_POST['project']) ? trim($_POST['project']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'New contact form message';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Basic validation
$errors = [];
if ($name === '') $errors[] = 'Name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if ($message === '') $errors[] = 'Message is required.';

if (!empty($errors)) {
  http_response_code(422);
  echo json_encode(['success' => false, 'errors' => $errors]);
  exit;
}

// Prevent header injection
$name_s    = sanitize_header($name);
$email_s   = sanitize_header($email);
$phone_s   = sanitize_header($phone);
$product_s = sanitize_header($product);
$subject_s = sanitize_header($subject);

// Build email
$to = 'athithyanadhi00@gmail.com'; // <-- change to the recipient address you want
$subject_line = "Website Inquiry: " . $subject_s;

$body_html = "
<html>
  <body>
    <h2>New contact form submission</h2>
    <table cellpadding='6' cellspacing='0' border='0'>
      <tr><td><strong>Name:</strong></td><td>{$name_s}</td></tr>
      <tr><td><strong>Email:</strong></td><td>{$email_s}</td></tr>
      <tr><td><strong>Phone:</strong></td><td>{$phone_s}</td></tr>
      <tr><td><strong>Product:</strong></td><td>{$product_s}</td></tr>
      <tr><td><strong>Subject:</strong></td><td>{$subject_s}</td></tr>
      <tr><td><strong>Message:</strong></td><td>" . nl2br(htmlspecialchars($message)) . "</td></tr>
    </table>
  </body>
</html>
";

// Headers
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: {$name_s} <{$email_s}>\r\n";
$headers .= "Reply-To: {$email_s}\r\n";

// Send
$sent = mail($to, $subject_line, $body_html, $headers);

if ($sent) {
  echo json_encode(['success' => true, 'message' => 'Message sent.']);
} else {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
}
