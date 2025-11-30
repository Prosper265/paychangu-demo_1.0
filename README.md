# paychangu-demo_1.0
PayChangu API Integration in PHP





# 🚀 Complete PayChangu Integration Guide

## 📋 Table of Contents
1. [Overview](#overview)
2. [Before You Start](#before-you-start)
3. [Method 1: HTML Checkout](#method-1-html-checkout)
4. [Method 2: Inline Checkout](#method-2-inline-checkout)
5. [Setting Up Webhooks](#setting-up-webhooks)
6. [Testing Your Integration](#testing)
7. [Going Live](#going-live)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

PayChangu allows you to accept payments in Malawi using:
- 💳 **Credit/Debit Cards**
- 📱 **Mobile Money** (Airtel, TNM)
- 🏦 **Bank Transfers**

This guide provides **two integration methods**:
- **HTML Checkout**: Simplest - redirect to PayChangu page
- **Inline Checkout**: Modern - popup on your website

---

## ✅ Before You Start

### 1. Create PayChangu Account
1. Go to [PayChangu Dashboard](https://dashboard.paychangu.com)
2. Sign up and verify your account
3. Complete your business profile

### 2. Get Your API Keys
1. Login to dashboard
2. Go to **Settings** > **API & Webhooks**
3. You'll see:
   - **Public Key** (starts with `pub-test-` or `pub-live-`)
   - **Secret Key** (starts with `sec-test-` or `sec-live-`)
   - **Webhook Secret** (for webhook verification)

### 3. File Structure
Create these files on your server:
```
your-website/
├── payment.php          (Payment form - HTML or Inline method)
├── callback.php         (Handles successful payments)
├── failed.php           (Handles failed payments)
├── webhook.php          (Receives automatic notifications)
└── verify_payment.php   (Optional: Manual verification)
```

---

## 🎨 Method 1: HTML Checkout (Easiest)

### How It Works
1. Customer fills form on your site
2. Form submits to PayChangu
3. Customer completes payment on PayChangu page
4. PayChangu redirects back to your site

### Step-by-Step Setup

#### Step 1: Create Payment Form
Use the **PayChangu HTML Checkout - Payment Form** artifact I provided.

**Important things to change:**
```html
<!-- Replace with YOUR public key -->
<input type="hidden" name="public_key" value="pub-test-YOUR-KEY-HERE" />

<!-- Replace with YOUR domain -->
<input type="hidden" name="callback_url" value="https://yourdomain.com/callback.php" />
<input type="hidden" name="return_url" value="https://yourdomain.com/failed.php" />

<!-- Replace with actual customer data -->
<input type="hidden" name="email" value="customer@example.com" />
<input type="hidden" name="first_name" value="John" />

<!-- Replace with actual amount -->
<input type="hidden" name="amount" value="1000" />
```

#### Step 2: Create Callback Handler
Use the **PayChangu Callback Handler** artifact I provided.

**Important things to change:**
```php
// Your SECRET key (NOT public key!)
$secret_key = 'sec-test-YOUR-SECRET-KEY-HERE';

// Your expected amount and currency
$expected_amount = 1000;
$expected_currency = 'MWK';
```

#### Step 3: Create Failed Payment Page
Use the **PayChangu Failed Payment Handler** artifact I provided.

No changes needed - it works as-is!

---

## ⚡ Method 2: Inline Checkout (Modern)

### How It Works
1. Customer clicks "Pay Now" button
2. Payment popup appears on your page
3. Customer completes payment in popup
4. Popup closes, redirects to callback

### Step-by-Step Setup

#### Step 1: Create Payment Page
Use the **PayChangu Inline Checkout - JavaScript Method** artifact I provided.

**Important things to change:**
```javascript
PaychanguCheckout({
    // Replace with YOUR public key
    "public_key": "pub-test-YOUR-KEY-HERE",
    
    // Replace with YOUR domain
    "callback_url": "https://yourdomain.com/callback.php",
    "return_url": "https://yourdomain.com/failed.php",
    
    // Replace with actual customer data
    "customer": {
        "email": "customer@example.com",
        "first_name": "John",
        "last_name": "Doe"
    },
    
    // Replace with actual amount
    //or add input fields in the form in index page to submit.
    "amount": 1000
});
```

#### Step 2: Create Callback Handler
Same as HTML Checkout method - use the callback.php artifact.

#### Step 3: Create Failed Payment Page
Same as HTML Checkout method - use the failed.php artifact.

---

## 🔔 Setting Up Webhooks (Recommended!)

### Why Use Webhooks?
- Get notified immediately when payment happens
- Works even if customer closes browser
- More reliable than waiting for redirect
- Can process payments automatically

### Setup Steps

#### Step 1: Create Webhook Handler
Use the **PayChangu Webhook Handler** artifact I provided.

**Important things to change:**
```php
// Get this from PayChangu Dashboard > Settings > API & Webhooks
$webhook_secret = 'your_webhook_secret_key_here';
```

#### Step 2: Upload to Server
Upload `webhook.php` to your server, e.g.:
```
https://yourdomain.com/webhook.php
```

#### Step 3: Register in PayChangu Dashboard
1. Login to PayChangu Dashboard
2. Go to **Settings** > **API & Webhooks**
3. Enter your webhook URL: `https://yourdomain.com/webhook.php`
4. Check all event types
5. Click **Save**

#### Step 4: Test Webhook
Make a test payment and check if:
- File `webhook_log.txt` is created
- File `payments_log.txt` shows the payment

---

## 🧪 Testing Your Integration

### Test Credentials
```
Test Card Number: 4242 4242 4242 4242
Expiry: Any future date (e.g., 12/2030)
CVV: Any 3 digits (e.g., 123)

Airtel Money Test: 990000000
```

### Testing Checklist

#### ✅ Test Successful Payment
1. Go to your payment page
2. Enter test card details
3. Complete payment
4. Should redirect to callback.php
5. Should see success message
6. Check webhook_log.txt for notification

#### ✅ Test Failed Payment
1. Try paying with insufficient funds
2. Or cancel the payment
3. Should redirect to failed.php
4. Should see failure message

#### ✅ Test Webhook
1. Make a payment
2. Check if webhook_log.txt was created
3. Check if payments_log.txt was updated
4. Verify signature validation works

---

## 🚀 Going Live

### Checklist Before Going Live

#### 1. Switch to Live Keys
```php
// Change from test keys to live keys
$public_key = 'pub-live-YOUR-LIVE-KEY';
$secret_key = 'sec-live-YOUR-LIVE-KEY';
$webhook_secret = 'your_live_webhook_secret';
```

#### 2. Update URLs
Make sure all URLs point to your actual domain:
```php
callback_url: "https://yourdomain.com/callback.php"
return_url: "https://yourdomain.com/failed.php"
webhook_url: "https://yourdomain.com/webhook.php"
```

#### 3. Enable SSL (HTTPS)
- PayChangu **requires HTTPS** for webhooks
- Get SSL certificate (free from Let's Encrypt)
- Ensure all URLs use `https://`

#### 4. Security Checklist
- ✅ Secret keys stored securely (not in JavaScript!)
- ✅ Webhook signature verification enabled
- ✅ Database prepared for storing transactions
- ✅ Email notifications configured
- ✅ Error logging in place
- ✅ Test all payment methods (card, mobile money, bank)

#### 5. Database Setup (Recommended)
Create a table to store payments:
```sql
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tx_ref VARCHAR(255) UNIQUE NOT NULL,
    charge_id VARCHAR(255),
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    status VARCHAR(50) NOT NULL,
    customer_email VARCHAR(255),
    customer_name VARCHAR(255),
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 🔧 Troubleshooting

### Common Issues

#### Issue 1: Payment Not Redirecting
**Cause**: Wrong callback URL
**Solution**: 
- Verify URL is correct and accessible
- Check URL has no typos
- Ensure domain supports HTTPS

#### Issue 2: Webhook Not Receiving Notifications
**Cause**: 
- Wrong webhook URL
- Server blocking requests
- Invalid signature

**Solution**:
```php
// Check webhook_log.txt for errors
// Verify webhook URL in dashboard matches your file
// Test webhook URL directly in browser
// Check server error logs
```

#### Issue 3: Signature Verification Failing
**Cause**: Wrong webhook secret
**Solution**:
```php
// Get correct webhook secret from dashboard
// Make sure no extra spaces in secret key
// Check security_log.txt for details
```

#### Issue 4: Payment Verification Fails
**Cause**: Wrong secret key or network issue
**Solution**:
```php
// Verify you're using SECRET key (not public key)
// Check if curl is enabled on server
// Try verification URL directly in browser
```

---

## 📊 Testing Flow Diagram

```
CUSTOMER                    YOUR WEBSITE                 PAYCHANGU
   |                             |                           |
   |--[1. Clicks Pay Now]------->|                           |
   |                             |--[2. Form Submits]------->|
   |                             |                           |
   |<--------[3. Payment Page]--------------------------|   |
   |                             |                           |
   |--[4. Enters Details]---------------------------------->|
   |                             |                           |
   |                             |<--[5. Webhook Notification]|
   |                             |                           |
   |<------[6. Redirect to callback.php]--------------------|
   |                             |                           |
   |                             |--[7. Verify Payment]----->|
   |                             |                           |
   |                             |<--[8. Verification Result]|
   |                             |                           |
   |<---[9. Success Page]--------|                           |
```

---

## 📞 Support

### Need Help?
- **PayChangu Documentation**: https://docs.paychangu.com
- **PayChangu Support**: support@paychangu.com
- **Dashboard**: https://dashboard.paychangu.com

### Common Resources
- Test in sandbox mode first
- Use test credentials provided above
- Check webhook logs for debugging
- Always verify payments server-side

---

## 🎓 Best Practices

### Security
1. ✅ Always verify webhook signatures
2. ✅ Keep secret keys secure (never in JavaScript!)
3. ✅ Use HTTPS for all URLs
4. ✅ Verify payment before giving value
5. ✅ Log all transactions

### User Experience
1. ✅ Show clear error messages
2. ✅ Provide multiple payment methods
3. ✅ Send confirmation emails
4. ✅ Allow retry on failed payments
5. ✅ Show payment status clearly

### Reliability
1. ✅ Use webhooks (don't rely only on redirects)
2. ✅ Store all transactions in database
3. ✅ Handle network errors gracefully
4. ✅ Implement retry logic
5. ✅ Monitor webhook logs

---

## ✨ You're Ready!

You now have everything needed to accept payments with PayChangu:
- ✅ Payment forms (HTML and Inline methods)
- ✅ Callback handlers
- ✅ Webhook integration
- ✅ Error handling
- ✅ Security measures

**Start with test mode, verify everything works, then switch to live mode!**

Good luck with your integration! 🎉
