<?php
/**
 * ============================================================================
 * PAYCHANGU WEBHOOK HANDLER
 * ============================================================================
 * This file receives automatic payment notifications from PayChangu.
 * 
 * WHY USE WEBHOOKS?
 * - You get notified immediately when payment is made (don't wait for customer)
 * - Works even if customer closes browser after payment
 * - More reliable than depending on redirects
 * - You can send emails, update databases automatically
 * 
 * HOW TO SET UP:
 * 1. Save this file as: webhook.php
 * 2. Upload to your server (e.g., https://yourdomain.com/webhook.php)
 * 3. Add the URL to PayChangu dashboard:
 *    - Login to PayChangu dashboard
 *    - Go to Settings > API & Webhooks
 *    - Enter your webhook URL
 *    - Check all event types
 *    - Save settings
 * 
 * IMPORTANT SECURITY NOTE:
 * This file is PUBLIC - anyone can access the URL. That's why we verify
 * the signature to ensure requests really come from PayChangu.
 * ============================================================================
 */

// ============================================================================
// STEP 1: GET THE WEBHOOK DATA
// ============================================================================
// PayChangu sends data as JSON in the request body

// Get the raw POST data
$payload = file_get_contents('php://input');

// Get all request headers
$headers = getallheaders();

// Log the webhook (useful for debugging)
// You can disable this in production
$log_file = 'webhook_log.txt';
$log_entry = date('Y-m-d H:i:s') . " - Webhook received\n";
$log_entry .= "Payload: " . $payload . "\n";
$log_entry .= "Headers: " . json_encode($headers) . "\n\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);

// ============================================================================
// STEP 2: YOUR WEBHOOK SECRET KEY
// ============================================================================
// CRITICAL: Get this from your PayChangu dashboard
// Dashboard > Settings > API & Webhooks > Webhook Secret
// This is DIFFERENT from your API secret key!

$webhook_secret = 'pub-test-gVNpmcDiG8409qnbNR1nAMvs6Ut52hDU'; // REPLACE THIS!

// ============================================================================
// STEP 3: VERIFY THE WEBHOOK IS FROM PAYCHANGU (SECURITY!)
// ============================================================================
// This is CRITICAL for security. Without this check, anyone could send
// fake payment notifications to your webhook URL.

// Get the signature from headers
$received_signature = isset($headers['Signature']) ? $headers['Signature'] : '';

// Calculate what the signature should be
$computed_signature = hash_hmac('sha256', $payload, $webhook_secret);

// Compare signatures
if ($computed_signature !== $received_signature) {
    // ========================================================================
    // SECURITY ALERT: Invalid signature!
    // ========================================================================
    // This request didn't come from PayChangu or was tampered with
    
    http_response_code(403); // Forbidden
    
    // Log the security issue
    $security_log = date('Y-m-d H:i:s') . " - SECURITY WARNING: Invalid webhook signature!\n";
    $security_log .= "Expected: " . $computed_signature . "\n";
    $security_log .= "Received: " . $received_signature . "\n\n";
    file_put_contents('security_log.txt', $security_log, FILE_APPEND);
    
    // Send alert to admin (optional but recommended)
    // mail('admin@yourdomain.com', 'Webhook Security Alert', $security_log);
    
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// ============================================================================
// STEP 4: SIGNATURE IS VALID - PROCESS THE WEBHOOK
// ============================================================================

// Decode the JSON payload
$webhook_data = json_decode($payload, true);

if (!$webhook_data) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Extract important information
$event_type = $webhook_data['event_type'] ?? '';
$status = $webhook_data['status'] ?? '';
$tx_ref = $webhook_data['reference'] ?? ($webhook_data['tx_ref'] ?? '');
$amount = $webhook_data['amount'] ?? 0;
$currency = $webhook_data['currency'] ?? '';
$charge_id = $webhook_data['charge_id'] ?? '';

// ============================================================================
// STEP 5: HANDLE DIFFERENT EVENT TYPES
// ============================================================================

switch ($event_type) {
    
    // ------------------------------------------------------------------------
    // SUCCESSFUL PAYMENT
    // ------------------------------------------------------------------------
    case 'api.charge.payment':
    case 'checkout.payment':
        if ($status === 'success') {
            handleSuccessfulPayment($webhook_data);
        } else {
            handleFailedPayment($webhook_data);
        }
        break;
    
    // ------------------------------------------------------------------------
    // PAYOUT (Money sent out)
    // ------------------------------------------------------------------------
    case 'api.payout':
        handlePayout($webhook_data);
        break;
    
    // ------------------------------------------------------------------------
    // OTHER EVENTS
    // ------------------------------------------------------------------------
    default:
        // Log unknown event type
        $log_entry = date('Y-m-d H:i:s') . " - Unknown event type: {$event_type}\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        break;
}

// ============================================================================
// STEP 6: RESPOND TO PAYCHANGU
// ============================================================================
// IMPORTANT: Always return HTTP 200 to acknowledge receipt
// If you don't, PayChangu will retry sending the webhook (up to 3 times)

http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Webhook processed']);
exit;

// ============================================================================
// FUNCTION: HANDLE SUCCESSFUL PAYMENT
// ============================================================================

function handleSuccessfulPayment($data) {
    // Extract payment details
    $tx_ref = $data['reference'] ?? $data['tx_ref'] ?? '';
    $amount = $data['amount'] ?? 0;
    $currency = $data['currency'] ?? '';
    $charge_id = $data['charge_id'] ?? '';
    
    // Get customer details if available
    $customer_email = $data['customer']['email'] ?? '';
    $customer_name = ($data['customer']['first_name'] ?? '') . ' ' . 
                     ($data['customer']['last_name'] ?? '');
    
    // Get payment method
    $payment_method = $data['authorization']['channel'] ?? 'Unknown';
    
    // ========================================================================
    // TODO: YOUR BUSINESS LOGIC HERE
    // ========================================================================
    
    // 1. CHECK IF TRANSACTION ALREADY PROCESSED
    //    (Prevent processing same payment twice)
    // Example:
    // $exists = $db->query("SELECT id FROM payments WHERE tx_ref = ?", [$tx_ref]);
    // if ($exists) {
    //     return; // Already processed
    // }
    
    // 2. SAVE TO DATABASE
    // Example:
    // $db->query("INSERT INTO payments (tx_ref, charge_id, amount, currency, 
    //             status, customer_email, payment_method, created_at) 
    //             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())", 
    //            [$tx_ref, $charge_id, $amount, $currency, 'completed', 
    //             $customer_email, $payment_method]);
    
    // 3. UPDATE ORDER STATUS
    // Example:
    // $order_id = $data['meta']['order_id'] ?? null;
    // if ($order_id) {
    //     $db->query("UPDATE orders SET status = 'paid', 
    //                 payment_date = NOW() WHERE order_id = ?", [$order_id]);
    // }
    
    // 4. SEND CONFIRMATION EMAIL TO CUSTOMER
    // Example:
    // $email_body = "Dear {$customer_name},\n\n";
    // $email_body .= "Your payment of {$currency} {$amount} has been received.\n";
    // $email_body .= "Transaction Reference: {$tx_ref}\n\n";
    // $email_body .= "Thank you for your business!";
    // mail($customer_email, "Payment Confirmed - {$tx_ref}", $email_body);
    
    // 5. SEND NOTIFICATION TO ADMIN
    // Example:
    // mail('admin@yourdomain.com', 
    //      "New Payment Received: {$currency} {$amount}", 
    //      "Payment from {$customer_email}\nRef: {$tx_ref}");
    
    // 6. TRIGGER OTHER ACTIONS
    // - Activate subscription
    // - Send download link
    // - Notify shipping department
    // - Award loyalty points
    // - You can do more.
    
    // Log the successful processing
    $log_entry = date('Y-m-d H:i:s') . " - PAYMENT SUCCESS\n";
    $log_entry .= "Transaction: {$tx_ref}\n";
    $log_entry .= "Amount: {$currency} {$amount}\n";
    $log_entry .= "Customer: {$customer_email}\n";
    $log_entry .= "Method: {$payment_method}\n\n";
    file_put_contents('payments_log.txt', $log_entry, FILE_APPEND);
}

// ============================================================================
// FUNCTION: HANDLE FAILED PAYMENT
// ============================================================================

function handleFailedPayment($data) {
    $tx_ref = $data['reference'] ?? $data['tx_ref'] ?? '';
    $amount = $data['amount'] ?? 0;
    $currency = $data['currency'] ?? '';
    $customer_email = $data['customer']['email'] ?? '';
    
    // ========================================================================
    // TODO: YOUR BUSINESS LOGIC HERE
    // ========================================================================
    
    // 1. LOG THE FAILED ATTEMPT
    // Example:
    // $db->query("INSERT INTO failed_payments (tx_ref, amount, currency, 
    //             customer_email, reason, created_at) 
    //             VALUES (?, ?, ?, ?, ?, NOW())", 
    //            [$tx_ref, $amount, $currency, $customer_email, 'Payment failed']);
    
    //2. NOTIFY CUSTOMER (Optional - they might want to retry)
    //Example:
    $email_body = "We noticed your payment attempt was not successful.\n\n";
    $email_body .= "Amount: {$currency} {$amount}\n";
    $email_body .= "You can try again at: https://shakingly-chalazian-marcelo.ngrok-free.dev/{$tx_ref}\n\n";
    $email_body .= "If you need help, please contact our support team.";
    mail($customer_email, "Payment Failed - Please Retry", $email_body);
    
    // 3. ALERT ADMIN (If many failures - might indicate a problem)
    // Example:
    // mail('admin@yourdomain.com', 
    //      "Failed Payment Alert", 
    //      "Customer {$customer_email} failed payment of {$currency} {$amount}");
    
    // Log the failed payment
    $log_entry = date('Y-m-d H:i:s') . " - PAYMENT FAILED\n";
    $log_entry .= "Transaction: {$tx_ref}\n";
    $log_entry .= "Amount: {$currency} {$amount}\n";
    $log_entry .= "Customer: {$customer_email}\n\n";
    file_put_contents('failed_payments_log.txt', $log_entry, FILE_APPEND);
}

// ============================================================================
// FUNCTION: HANDLE PAYOUT
// ============================================================================

function handlePayout($data) {
    // Handle money being sent out (disbursements)
    // Similar logic to successful payment but for outgoing transactions
    
    $log_entry = date('Y-m-d H:i:s') . " - PAYOUT PROCESSED\n";
    $log_entry .= json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
    file_put_contents('payouts_log.txt', $log_entry, FILE_APPEND);
}

/**
 * ============================================================================
 * TESTING YOUR WEBHOOK
 * ============================================================================
 * 
 * To test if your webhook is working:
 * 
 * 1. Make a test payment using the test card or mobile number
 * 2. Check your webhook_log.txt file - you should see entries
 * 3. Check your payments_log.txt file for successful payments
 * 
 * You can also test using cURL:
 * 
 * curl -X POST https://yourdomain.com/webhook.php \
 *   -H "Content-Type: application/json" \
 *   -H "Signature: YOUR_COMPUTED_SIGNATURE" \
 *   -d '{"event_type":"api.charge.payment","status":"success",...}'
 * 
 * ============================================================================
 */
?>