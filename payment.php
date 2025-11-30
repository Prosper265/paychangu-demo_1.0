<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PayChangu Payment</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; justify-content: center; align-items: center; height: 100vh;}
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { background: #006797; color: white; padding: 15px; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 16px; }
        .btn:hover { background: #00465c; }
        .btn:disabled { background: #cccccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Test PayChangu Integration</h2>
        <p>Click the button below to test the payment process:</p>
        
        <!-- everything is going to be submited to "process-payment.php" -->
        <form action="process-payment.php" method="POST">
            <input type="hidden" name="test" value="1">
            <button type="submit" class="btn">Pay</button>
        </form>
    </div>
</body>
</html>