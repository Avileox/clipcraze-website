<?php
// Only process POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = strip_tags(trim($_POST["message"]));

    // Validate form data
    if (empty($name) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Please complete all fields and provide a valid email address."]);
        exit;
    }

    // Resend API settings
    $api_key = getenv('RESEND_API_KEY');
    $api_url = "https://api.resend.com/emails";

    // Build HTML email content
    $html_content = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>";
    $html_content .= "<h2 style='color: #6366f1;'>New Contact Form Submission</h2>";
    $html_content .= "<div style='background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    $html_content .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
    $html_content .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    $html_content .= "<p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>";
    $html_content .= "</div>";
    $html_content .= "<div style='margin: 20px 0;'>";
    $html_content .= "<p><strong>Message:</strong></p>";
    $html_content .= "<p style='background: #f9fafb; padding: 15px; border-left: 4px solid #6366f1; border-radius: 4px;'>" . nl2br(htmlspecialchars($message)) . "</p>";
    $html_content .= "</div>";
    $html_content .= "<hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>";
    $html_content .= "<p style='color: #6b7280; font-size: 12px;'>This email was sent from the ClipCraze.net contact form.</p>";
    $html_content .= "</div>";

    // Prepare data for Resend API
    $data = [
        "from" => "ClipCraze <contact@clipcraze.net>",
        "to" => ["contact@clipcraze.net"],
        "reply_to" => $email,
        "subject" => "New Contact Form: " . $subject,
        "html" => $html_content
    ];

    // Initialize cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $api_key,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check response
    if ($http_code == 200) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Thank you! Your message has been sent successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Oops! Something went wrong and we couldn't send your message."]);
    }
} else {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "There was a problem with your submission. Please try again."]);
}
?>
