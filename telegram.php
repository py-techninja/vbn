<?php
// telegram.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// ONLY RESPOND TO POST REQUESTS
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// ---------------------------------------------------------
// CONFIGURATION
// Replace these with your actual Telegram Bot Token & Chat ID
// ---------------------------------------------------------
$botToken = "8899396530:AAGzlingCEyI-5xHUhwIWGShbd1MQ5Zz-o8";
$chatId   = "6629066125";

// Read incoming JSON payload from the frontend
$jsonStr = file_get_contents('php://input');
$data = json_decode($jsonStr, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON payload"]);
    exit;
}

// Build the message text to send to Telegram
$message = "🔔 <b>Notification:</b> " . htmlspecialchars($data['event'] ?? 'Unknown Event') . "\n";

// Append any extra data fields passed from the frontend
if (!empty($data['provider'])) {
    $message .= "<b>Provider:</b> " . htmlspecialchars($data['provider']) . "\n";
}
if (!empty($data['email'])) {
    $message .= "<b>Email:</b> " . htmlspecialchars($data['email']) . "\n";
}
if (!empty($data['url'])) {
    $message .= "<b>URL:</b> " . htmlspecialchars($data['url']) . "\n";
}
if (!empty($data['userAgent'])) {
    $message .= "\n<b>User Agent:</b> " . htmlspecialchars($data['userAgent']);
}

// Prepare Telegram API URL
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";

// Prepare POST data
$postData = [
    'chat_id' => $chatId,
    'text' => $message,
    'parse_mode' => 'HTML'
];

// Initialize cURL session
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout

// Execute cURL request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return response to frontend
if ($httpCode === 200) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to send message to Telegram", "details" => $response]);
}
?>
