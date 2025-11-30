<?php
/**
 * ============================================================================
 * PAYCHANGU CALLBACK HANDLER
 * ============================================================================
 * This file handles the customer return after successful payment.
 * 
 * IMPORTANT: This is where you verify the payment and give value to customer.
 * 
 * Save this file as: callback.php
 * ============================================================================
 */

// Start session to store payment info
session_start();

// ============================================================================
// STEP 1: GET THE TRANSACTION REFERENCE FROM URL
// ============================================================================
// PayChangu sends back the transaction reference (tx_ref) and status
// in the URL as query parameters

if (!isset($_GET['tx_ref'])) {
    die('Error: No transaction reference provided.');
}

$tx_ref = $_GET['tx_ref'];
$status = isset($_GET['status']) ? $_GET['status'] : '';

// ============================================================================
// STEP 2: YOUR API SECRET KEY
// ============================================================================
// IMPORTANT: Replace with YOUR actual secret key from PayChangu dashboard
// For testing: Use your test secret key
// For live: Use your live secret key
// 
// WARNING: NEVER share this key or put it in JavaScript!

$secret_key = 'sec-test-Gv8qFhByQlrNHpZFUyltMTxTdkeEvnww';

// ============================================================================
// STEP 3: VERIFY THE PAYMENT WITH PAYCHANGU
// ============================================================================
// This is CRITICAL! Always verify payment on your server.
// Never trust the status from the URL alone - anyone could fake it.

function verifyPayment($tx_ref, $secret_key) {
    // Build the verification URL
    $url = "https://api.paychangu.com/verify-payment/" . $tx_ref;
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $secret_key
    ]);
    
    // Execute the request
    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => 'Connection error: ' . $error];
    }
    
    curl_close($ch);
    
    // Decode the JSON response
    $result = json_decode($response, true);
    
    return $result;
}

// Call the verification function
$verification = verifyPayment($tx_ref, $secret_key);

// ============================================================================
// STEP 4: CHECK VERIFICATION RESULT
// ============================================================================

// Check if verification was successful
if (isset($verification['error'])) {
    // There was an error connecting to PayChangu
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Payment Verification Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>Verification Error</h2>
            <p><?php echo htmlspecialchars($verification['error']); ?></p>
            <p>Please contact support with reference: <?php echo htmlspecialchars($tx_ref); ?></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================================
// STEP 5: VALIDATE THE PAYMENT DETAILS
// ============================================================================

$payment_data = $verification['data'] ?? null;

if (!$payment_data) {
    die('Error: Invalid verification response.');
}

// Extract important payment information
$payment_status = $payment_data['status'] ?? '';
$amount_paid = $payment_data['amount'] ?? 0;
$currency = $payment_data['currency'] ?? '';
$customer_email = $payment_data['customer']['email'] ?? '';
$payment_method = $payment_data['authorization']['channel'] ?? '';

// ============================================================================
// STEP 6: VERIFY PAYMENT MEETS YOUR REQUIREMENTS
// ============================================================================
// Check these important things:
// 1. Payment status is "success"
// 2. Amount is correct (or more - you can refund excess)
// 3. Currency is correct
// 4. Transaction hasn't been processed before (check your database)

$expected_amount = 1000; // The amount you expected
$expected_currency = 'MWK';

$is_valid = true;
$errors = [];

// Check if payment was successful
if ($payment_status !== 'success') {
    $is_valid = false;
    $errors[] = 'Payment status is not successful: ' . $payment_status;
}

// Check if amount is correct
if ($amount_paid < $expected_amount) {
    $is_valid = false;
    $errors[] = "Amount paid (MWK {$amount_paid}) is less than expected (MWK {$expected_amount})";
}

// Check if currency is correct
if ($currency !== $expected_currency) {
    $is_valid = false;
    $errors[] = "Currency ({$currency}) does not match expected ({$expected_currency})";
}

// ============================================================================
// STEP 7: PROCESS THE PAYMENT
// ============================================================================

if ($is_valid) {
    // ========================================================================
    // PAYMENT IS VALID - DO YOUR BUSINESS LOGIC HERE
    // ========================================================================
    
    // TODO: Add to your database
    // Example:
    // $db->query("INSERT INTO orders (tx_ref, amount, status, email) VALUES (?, ?, ?, ?)", 
    //            [$tx_ref, $amount_paid, 'completed', $customer_email]);
    
    // TODO: Send confirmation email to customer
    // Example:
    // mail($customer_email, "Payment Confirmed", "Your payment of MWK {$amount_paid} was successful...");
    
    // TODO: Deliver the product/service
    // Example:
    // - Update order status to "paid"
    // - Send download link
    // - Activate subscription
    // - Ship physical product
    
    // Store in session for display
    $_SESSION['payment_success'] = true;
    $_SESSION['payment_data'] = $payment_data;
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Payment Successful</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .success-container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .success-icon {
                text-align: center;
                color: #0092a5ff;
                font-size: 60px;
                margin-bottom: 20px;
            }
            h1 {
                color: #00899bff;
                text-align: center;
            }
            .details {
                background: #f9f9f9;
                padding: 20px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #e0e0e0;
            }
            .detail-row:last-child {
                border-bottom: none;
            }
            .label {
                font-weight: bold;
                color: #666;
            }
            .value {
                color: #333;
            }
            .button {
                display: block;
                background-color: #008c96ff;
                color: white;
                padding: 15px;
                text-align: center;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .button:hover {
                background-color: #006363ff;
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <div class="success-icon">✓</div>
            <h1>Payment Successful!</h1>
            <p style="text-align: center;">Thank you for your payment. Your transaction has been completed successfully.</p>
            
            <div class="details">
                <div class="detail-row">
                    <span class="label">Transaction Reference:</span>
                    <span class="value"><?php echo htmlspecialchars($tx_ref); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Amount Paid:</span>
                    <span class="value"><?php echo htmlspecialchars($currency . ' ' . number_format($amount_paid, 2)); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Payment Method:</span>
                    <span class="value"><?php echo htmlspecialchars($payment_method); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($customer_email); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Date:</span>
                    <span class="value"><?php echo date('F j, Y, g:i a'); ?></span>
                </div>
            </div>
            
            <p style="text-align: center; color: #666;">
                A confirmation email has been sent to <?php echo htmlspecialchars($customer_email); ?>
            </p>
            
            <a href="payment.php" class="button">Return to Home</a>
        </div>
    </body>
    </html>
    <?php
    
} else {
    // ========================================================================
    // PAYMENT IS INVALID - SHOW ERROR
    // ========================================================================
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Payment Verification Failed</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .error-container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .error-icon {
                text-align: center;
                color: #f44336;
                font-size: 60px;
                margin-bottom: 20px;
            }
            h1 {
                color: #f44336;
                text-align: center;
            }
            .errors {
                background: #ffebee;
                padding: 20px;
                border-radius: 5px;
                border-left: 4px solid #f44336;
                margin: 20px 0;
            }
            .errors ul {
                margin: 10px 0;
                padding-left: 20px;
            }
            .button {
                display: block;
                background-color: #2196F3;
                color: white;
                padding: 15px;
                text-align: center;
                text-decoration: none;
                border-radius: 5px;
                margin-top: 20px;
            }
            .button:hover {
                background-color: #0b7dda;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">✗</div>
            <h1>Payment Verification Failed</h1>
            
            <div class="errors">
                <strong>The following issues were found:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <p style="text-align: center;">
                Transaction Reference: <strong><?php echo htmlspecialchars($tx_ref); ?></strong>
            </p>
            
            <p style="text-align: center; color: #666;">
                If you believe this is an error, please contact our support team with the transaction reference above.
            </p>
            
            <a href="index.php" class="button">Return to Home</a>
        </div>
    </body>
    </html>
    <?php
}
?>