# paychangu-demo_1.0
PayChangu API Integration in PHP




# PayChangu Integration Kit 🚀

A complete PHP integration kit for accepting payments in Malawi using **PayChangu** payment gateway. This project provides ready-to-use files for seamless payment processing with support for credit/debit cards, mobile money (Airtel & TNM), and bank transfers.

## Features ✨

- 💳 **Credit/Debit Card** payments
- 📱 **Mobile Money** integration (Airtel, TNM)
- 🏦 **Bank Transfer** support
- 🔄 **Webhook** support for real-time payment notifications
- ✅ **Payment Verification** - Server-side validation
- 🔒 **Secure** - Secret key authentication
- 📝 **Comprehensive** documentation and examples

## Project Structure 📁

```
paychangu_1.0/
├── README.md                 # This file
├── GUIDE.md                  # Complete integration guide
├── payment.php               # Payment form page
├── process-payment.php       # Payment processing
├── callback.php              # Successful payment handler
├── failed.php                # Failed payment handler
└── changu-webhook.php        # Webhook receiver for notifications
```

## File Descriptions 📄

### `payment.php`
Simple HTML payment form for testing the integration. Submits payment request to the payment processor.

**What it does:**
- Displays a payment button
- Submits form to `process-payment.php`
- Minimal styling for easy customization

### `process-payment.php`
Handles the payment request and initiates the PayChangu transaction.

**What it does:**
- Receives payment form data
- Generates unique transaction reference (`tx_ref`)
- Calls PayChangu API to create payment link
- Redirects customer to PayChangu checkout

**Key Configuration:**
```php
$secretKey = 'sec-test-YOUR-SECRET-KEY';
$publicKey = 'pub-test-YOUR-PUBLIC-KEY';
$amount = 1000; // in MWK
$currency = 'MWK';
```

### `callback.php`
Handles the return from successful PayChangu payment.

**What it does:**
- Receives transaction reference from PayChangu redirect
- Verifies payment with PayChangu API
- Updates database/records with successful payment
- Displays success message to customer
- Creates session for payment tracking

**Important:** Always verify payment on server-side - never trust URL parameters alone!

### `failed.php`
Handles failed or cancelled payments.

**What it does:**
- Displays user-friendly failure message
- Shows transaction reference for support
- Provides retry option
- Logs failed payment attempts

### `changu-webhook.php`
Receives automatic payment notifications from PayChangu (recommended for production).

**What it does:**
- Listens for POST requests from PayChangu
- Verifies webhook signature using secret key
- Processes payment events automatically
- Works even if customer closes browser after payment
- More reliable than redirect-based callbacks

**Webhook Setup:**
1. Save this file on your server
2. Go to PayChangu Dashboard → Settings → API & Webhooks
3. Add your webhook URL (e.g., `https://yourdomain.com/changu-webhook.php`)
4. Enable all event types
5. Save settings

## Getting Started 🎯

### Prerequisites
- PHP 7.0+
- cURL extension enabled
- PayChangu account with API keys
- HTTPS enabled on your server (for production)

### Step 1: Create PayChangu Account
1. Go to [PayChangu Dashboard](https://dashboard.paychangu.com)
2. Sign up and verify your account
3. Complete your business profile

### Step 2: Get API Keys
1. Login to PayChangu Dashboard
2. Navigate to **Settings** → **API & Webhooks**
3. Copy your:
   - **Public Key** (format: `pub-test-...` or `pub-live-...`)
   - **Secret Key** (format: `sec-test-...` or `sec-live-...`)
   - **Webhook Secret** (for webhook verification)

### Step 3: Configure the Files
Update API keys in these files:

**process-payment.php:**
```php
$secretKey = 'sec-test-YOUR-SECRET-KEY-HERE';
$publicKey = 'pub-test-YOUR-PUBLIC-KEY-HERE';
```

**callback.php:**
```php
$secret_key = 'sec-test-YOUR-SECRET-KEY-HERE';
```

**changu-webhook.php:**
```php
$webhook_secret = 'YOUR-WEBHOOK-SECRET-HERE';
```

### Step 4: Upload Files
Upload all files to your web server:
```bash
your-domain.com/
├── payment.php
├── process-payment.php
├── callback.php
├── failed.php
└── changu-webhook.php
```

### Step 5: Configure Callback URLs
Update the callback URLs in `process-payment.php`:

```php
'callback_url' => 'https://yourdomain.com/callback.php',  // Success page
'return_url' => 'https://yourdomain.com/failed.php',      // Failure page
```

## Usage 🔧

### Basic Payment Flow

1. **Customer initiates payment:**
   - User clicks "Pay" button on `payment.php`
   - Form submits to `process-payment.php`

2. **Payment processing:**
   - `process-payment.php` creates transaction
   - Customer redirected to PayChangu checkout

3. **Customer completes payment:**
   - PayChangu handles payment processing
   - Customer returns to your site

4. **Handle result:**
   - Success → `callback.php` (verify & process)
   - Failure → `failed.php` (show error)

5. **Receive webhook (optional but recommended):**
   - `changu-webhook.php` receives payment notification
   - Update records automatically

### Test the Integration

1. Access `payment.php` in your browser
2. Click the payment button
3. You'll be redirected to PayChangu test environment
4. Use test credentials to complete payment
5. Verify callback and success page

## Payment Data Structure 📊

### Basic Payment Information
```php
$paymentData = [
    'public_key' => 'pub-test-YOUR-KEY',
    'tx_ref' => 'TXN-' . time(),           // Unique transaction reference
    'amount' => 1000,                       // Amount in MWK
    'currency' => 'MWK',                   // Currency code
    'email' => 'customer@example.com',     // Customer email
    'first_name' => 'John',                // Customer first name
    'last_name' => 'Doe',                  // Customer last name
    'title' => 'Product Purchase',         // Payment title
    'description' => 'Item description',   // Payment description
    'callback_url' => 'https://...',       // Success redirect
    'return_url' => 'https://...',         // Failure redirect
    'meta' => [                            // Custom metadata
        'order_id' => '12345',
        'customer_id' => '67890'
    ]
];
```

## API Endpoints 🔌

### Create Payment
```
POST https://api.paychangu.com/payment
```
Initiates a new payment transaction.

### Verify Payment
```
GET https://api.paychangu.com/verify-payment/{tx_ref}
```
Verifies a payment was successfully processed.

### Webhook Events
PayChangu sends these events to your webhook:
- `payment.successful` - Payment completed
- `payment.failed` - Payment failed
- `payment.cancelled` - Customer cancelled

## Security Best Practices 🔒

1. **Never expose secret keys:**
   - Keep `sec-test-*` and `sec-live-*` keys private
   - Use environment variables in production
   - Never commit keys to version control

2. **Always verify payments server-side:**
   ```php
   // Good - Server verification
   $response = verifyPayment($tx_ref, $secret_key);
   
   // Bad - Trust URL parameter
   if ($_GET['status'] == 'success') { ... }
   ```

3. **Use HTTPS in production:**
   - Encrypt all communications
   - Protect sensitive payment data

4. **Validate webhook signatures:**
   - Verify webhook came from PayChangu
   - Use webhook secret key for validation

5. **Implement rate limiting:**
   - Prevent abuse of payment endpoints
   - Log suspicious activity

## Troubleshooting 🐛

### Payment Not Redirecting
- Check if PayChangu API is accessible
- Verify public key is correct
- Check callback/return URLs are valid
- Ensure JSON is properly formatted

### Webhook Not Receiving
- Verify webhook URL is public and HTTPS
- Check firewall/security settings
- Verify webhook secret is correct
- Check server logs for errors

### Payment Verification Failed
- Ensure secret key matches the one in dashboard
- Verify transaction reference is correct
- Check API endpoint is reachable
- Confirm payment was actually completed on PayChangu

## Environment Variables 🌍

For production, use environment variables:

```php
// process-payment.php
$secretKey = $_ENV['PAYCHANGU_SECRET_KEY'] ?? getenv('PAYCHANGU_SECRET_KEY');
$publicKey = $_ENV['PAYCHANGU_PUBLIC_KEY'] ?? getenv('PAYCHANGU_PUBLIC_KEY');
```

Set via `.env` file:
```
PAYCHANGU_SECRET_KEY=sec-live-YOUR-SECRET
PAYCHANGU_PUBLIC_KEY=pub-live-YOUR-PUBLIC
```

## Testing 🧪

### Test Mode
- Use test API keys (prefix: `test-`)
- PayChangu provides test cards
- No real money charged
- Safe for development

### Production Mode
- Switch to live API keys (prefix: `live-`)
- Real transactions processed
- Customer data required
- PCI compliance recommended

## Support & Documentation 📚

- **PayChangu Documentation:** [docs.paychangu.com](https://docs.paychangu.com)
- **PayChangu Dashboard:** [dashboard.paychangu.com](https://dashboard.paychangu.com)

## Integration Methods 📋

This kit supports multiple integration approaches:

1. **Redirect Checkout:** Customer redirected to PayChangu page
2. **Webhook Integration:** Real-time notifications
3. **API Integration:** Programmatic payment creation

## Common Use Cases 💼

- 🛒 **E-commerce:** Product purchases
- 💵 **Invoicing:** Service payments
- 📚 **Education:** Course fees, tuition
- 🎫 **Ticketing:** Event registrations
- 💰 **Donations:** Fundraising campaigns
- 🏥 **Healthcare:** Medical service payments

## Version & License 📝

- **Version:** 1.0
- **PHP Requirements:** 7.0+
- **Status:** Production Ready

## Contributing 🤝

To improve this integration kit:
1. Test thoroughly
2. Document changes
3. Report issues
4. Share improvements

## Changelog 📝

### v1.0 (Initial Release)
- Complete payment processing
- Callback handler with verification
- Failed payment handler
- Webhook support
- Comprehensive documentation

---

## Need Help? 💬

- Check PayChangu API documentation
- Review code comments in each file
- Test in development mode first

**Happy Integrating! 🎉**
