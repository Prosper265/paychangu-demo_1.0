<?php
/**
 * ============================================================================
 * PAYCHANGU FAILED PAYMENT HANDLER
 * ============================================================================
 * This file handles customers who cancel payment or have failed payments.
 * 
 * Save this file as: failed.php
 * ============================================================================
 */

// Get the transaction reference if provided
$tx_ref = isset($_GET['tx_ref']) ? $_GET['tx_ref'] : 'N/A';
$status = isset($_GET['status']) ? $_GET['status'] : 'failed';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .failed-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .failed-icon {
            text-align: center;
            color: #ff9800;
            font-size: 60px;
            margin-bottom: 20px;
        }
        h1 {
            color: #ff9800;
            text-align: center;
            margin-bottom: 10px;
        }
        p {
            text-align: center;
            color: #666;
            line-height: 1.6;
        }
        .reasons {
            background: #fff3e0;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #ff9800;
            margin: 20px 0;
        }
        .reasons h3 {
            margin-top: 0;
            color: #f57c00;
        }
        .reasons ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .reasons li {
            margin: 5px 0;
            color: #666;
        }
        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .button {
            flex: 1;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button-primary {
            background-color: #4CAF50;
            color: white;
        }
        .button-primary:hover {
            background-color: #45a049;
        }
        .button-secondary {
            background-color: #2196F3;
            color: white;
        }
        .button-secondary:hover {
            background-color: #0b7dda;
        }
        .reference {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            font-size: 14px;
        }
        .reference strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="failed-container">
        <div class="failed-icon">⚠️</div>
        <h1>Payment Not Completed</h1>
        <p>Your payment was not successful or was cancelled.</p>
        
        <div class="reasons">
            <h3>Common reasons for payment failure:</h3>
            <ul>
                <li>Insufficient funds in your account</li>
                <li>Incorrect payment details entered</li>
                <li>Payment cancelled by you</li>
                <li>Network connection issues</li>
                <li>Card declined by your bank</li>
            </ul>
        </div>
        
        <p>Don't worry! You can try again or choose a different payment method.</p>
        
        <div class="buttons">
            <a href="process-payment.php" class="button button-primary">Try Again</a>
            <a href="contact.php" class="button button-secondary">Contact Support</a>
        </div>
        
        <?php if ($tx_ref !== 'N/A'): ?>
        <div class="reference">
            <strong>Reference:</strong> <?php echo htmlspecialchars($tx_ref); ?><br>
            <small>Please quote this reference if you need to contact support.</small>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>