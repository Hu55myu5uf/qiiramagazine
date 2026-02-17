<?php
// paystack_config.php - Paystack API Configuration
// IMPORTANT: Replace these with your actual Paystack API keys from dashboard.paystack.com

// Set to false for live mode
define('PAYSTACK_TEST_MODE', true);

// API Keys - Get these from https://dashboard.paystack.com/#/settings/developers
define('PAYSTACK_SECRET_KEY', PAYSTACK_TEST_MODE 
    ? 'sk_test_12c8beeb1fb4e2b95b451cad6bcae19ef8345157'  // Test Secret Key
    : 'sk_live_YOUR_LIVE_SECRET_KEY_HERE'  // Live Secret Key
);

define('PAYSTACK_PUBLIC_KEY', PAYSTACK_TEST_MODE 
    ? 'pk_test_1faac3573eb9f40f3a6054fea2614603e2215688'  // Test Public Key
    : 'pk_live_YOUR_LIVE_PUBLIC_KEY_HERE'  // Live Public Key
);

// Paystack API Base URL
define('PAYSTACK_API_URL', 'https://api.paystack.co');

// Your site's base URL (update this for your domain)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_BASE_URL', $protocol . '://' . $host);

/**
 * Make a request to Paystack API
 */
function paystack_request($endpoint, $method = 'GET', $data = null) {
    $url = PAYSTACK_API_URL . $endpoint;
    
    $headers = [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json',
        'Cache-Control: no-cache'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['status' => false, 'message' => 'cURL Error: ' . $error];
    }
    
    return json_decode($response, true);
}
?>
