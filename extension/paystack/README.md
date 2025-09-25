# Paystack Payment Gateway for OpenCart 4

A comprehensive and production-ready Paystack payment gateway extension for OpenCart 4.x that enables merchants to accept secure online payments through Paystack's robust payment infrastructure.

## Features

### 🚀 **Core Payment Features**
- **Multiple Payment Methods**: Cards, Bank Transfer, USSD, QR Code, Mobile Money
- **Real-time Payment Processing**: Instant payment verification and order updates
- **Webhook Integration**: Automatic payment status synchronization
- **Transaction Management**: Complete transaction history and management
- **Refund Processing**: Easy refund management from admin panel

### 🔒 **Security & Compliance**
- **PCI DSS Compliant**: Secure payment processing
- **Webhook Signature Verification**: Ensures webhook authenticity
- **SSL/TLS Encryption**: All communications encrypted
- **CSRF Protection**: Built-in security measures
- **Input Validation**: Comprehensive data validation

### 🎛️ **Admin Features**
- **Comprehensive Configuration**: Easy setup and management
- **Transaction Dashboard**: View and manage all transactions
- **Payment Analytics**: Detailed payment statistics
- **Webhook Logs**: Monitor webhook activities
- **Test Mode**: Safe testing environment
- **API Connection Testing**: Verify API credentials

### 🌍 **Multi-Currency Support**
- Nigerian Naira (NGN)
- US Dollar (USD)
- Ghanaian Cedi (GHS)
- South African Rand (ZAR)
- Kenyan Shilling (KES)

### 📱 **User Experience**
- **Mobile Optimized**: Responsive design for all devices
- **Multiple Payment Channels**: Choose preferred payment method
- **Real-time Updates**: Live payment status updates
- **User-friendly Interface**: Intuitive payment flow

## Requirements

- **OpenCart**: 4.0.0.0 or higher
- **PHP**: 8.0 or higher
- **MySQL**: 5.7+ or MariaDB 10.3+
- **PHP Extensions**: curl, json, openssl
- **SSL Certificate**: Required for production use

## Installation

### 1. Download and Extract
```bash
# Extract the extension to your OpenCart root directory
unzip paystack-opencart4.zip -d /path/to/opencart/
```

### 2. Upload Files
Upload the entire `extension/paystack/` folder to your OpenCart installation directory.

### 3. Install Extension
1. Login to your OpenCart admin panel
2. Navigate to **Extensions > Extensions**
3. Filter by **Payment** type
4. Find **Paystack Payment Gateway** and click **Install**
5. Click **Edit** to configure the extension

### 4. Configure Paystack
1. Get your API keys from [Paystack Dashboard](https://dashboard.paystack.com/#/settings/developer)
2. Configure the extension with your API keys
3. Set up webhook URL in your Paystack dashboard
4. Test the configuration

## Configuration

### API Keys Setup
1. **Test Mode**: Use test API keys for development
   - Test Secret Key: `sk_test_...`
   - Test Public Key: `pk_test_...`

2. **Live Mode**: Use live API keys for production
   - Live Secret Key: `sk_live_...`
   - Live Public Key: `pk_live_...`

### Webhook Configuration
1. Copy the webhook URL from the admin configuration
2. Add it to your Paystack dashboard:
   - Go to **Settings > Webhooks**
   - Add webhook URL: `https://yourstore.com/index.php?route=extension/paystack/payment/paystack|webhook`
   - Select events: `charge.success`, `charge.failed`

### Payment Methods
Enable your preferred payment methods:
- ✅ **Card Payment**: Visa, Mastercard, Verve
- ✅ **Bank Payment**: Direct bank account payment
- ✅ **USSD**: Mobile USSD codes
- ✅ **QR Code**: Scan to pay
- ✅ **Bank Transfer**: Manual bank transfer
- ✅ **Mobile Money**: Mobile wallet payments

### Order Status Mapping
Configure order statuses for different payment states:
- **Pending**: Order placed, payment initiated
- **Completed**: Payment successful
- **Failed**: Payment failed
- **Refunded**: Payment refunded

## Usage

### For Customers
1. **Select Paystack** at checkout
2. **Choose payment method** (Card, Bank, USSD, etc.)
3. **Complete payment** through Paystack's secure interface
4. **Receive confirmation** and order updates

### For Merchants
1. **Monitor transactions** in admin dashboard
2. **Process refunds** directly from admin panel
3. **View payment analytics** and reports
4. **Manage webhook logs** for troubleshooting

## API Reference

### Transaction Management
```php
// Add transaction
$transaction_id = $this->model_extension_paystack_payment_paystack->addTransaction([
    'order_id' => $order_id,
    'reference' => $reference,
    'amount' => $amount,
    'currency' => $currency,
    'status' => 'pending'
]);

// Update transaction
$this->model_extension_paystack_payment_paystack->updateTransaction($transaction_id, [
    'status' => 'success',
    'gateway_response' => json_encode($response)
]);
```

### Webhook Handling
```php
// Validate webhook
$is_valid = $paystack->validateWebhook($payload, $signature);

// Process webhook
if ($is_valid) {
    $this->processWebhookEvent($webhook_data);
}
```

## Database Schema

### Transactions Table
```sql
CREATE TABLE `oc_paystack_transaction` (
    `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `reference` varchar(255) NOT NULL,
    `amount` decimal(15,4) NOT NULL,
    `currency` varchar(3) NOT NULL,
    `status` varchar(50) NOT NULL,
    `gateway_response` text,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`transaction_id`)
);
```

### Webhook Logs Table
```sql
CREATE TABLE `oc_paystack_webhook_log` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `event_type` varchar(100) NOT NULL,
    `reference` varchar(255) NOT NULL,
    `payload` text NOT NULL,
    `verified` tinyint(1) DEFAULT 0,
    `processed` tinyint(1) DEFAULT 0,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`log_id`)
);
```

## Troubleshooting

### Common Issues

#### 1. Payment Not Processing
- ✅ Check API keys are correct
- ✅ Verify SSL certificate is valid
- ✅ Ensure webhook URL is accessible
- ✅ Check PHP error logs

#### 2. Webhook Not Working
- ✅ Verify webhook URL in Paystack dashboard
- ✅ Check webhook signature validation
- ✅ Review webhook logs in admin panel
- ✅ Ensure server can receive POST requests

#### 3. Transaction Status Not Updating
- ✅ Check webhook configuration
- ✅ Verify order status mapping
- ✅ Review transaction logs
- ✅ Test webhook manually

### Debug Mode
Enable debug mode in the extension settings to log detailed information:
1. Go to **Advanced** tab in configuration
2. Enable **Debug Mode**
3. Check system logs for detailed information

### Log Files
- **System Logs**: `system/storage/logs/error.log`
- **Paystack Logs**: Check admin webhook logs
- **Server Logs**: Check your server's error logs

## Security Best Practices

### 1. API Key Security
- ✅ Never expose secret keys in frontend code
- ✅ Use environment variables for API keys
- ✅ Regularly rotate API keys
- ✅ Use test keys for development only

### 2. Webhook Security
- ✅ Always verify webhook signatures
- ✅ Use HTTPS for webhook URLs
- ✅ Implement rate limiting
- ✅ Log all webhook activities

### 3. General Security
- ✅ Keep OpenCart updated
- ✅ Use strong admin passwords
- ✅ Enable SSL/TLS encryption
- ✅ Regular security audits

## Support

### Developer
- **Name**: Vivian Akpoke
- **Email**: amiviann@gmail.com
- **GitHub**: https://github.com/Amivian
- **Repository**: https://github.com/Amivian/oc-v4-paystack.git     
- **Issues**: https://github.com/Amivian/oc-v4-paystack.git/issues

### Documentation
- [Paystack Documentation](https://paystack.com/docs)
- [OpenCart Documentation](https://docs.opencart.com)
- [Extension Documentation](https://github.com/Amivian/oc-v4-paystack.git   )

### Reporting Issues
When reporting issues, please create an issue on GitHub: https://github.com/Amivian/oc-v4-paystack.git/issues

Please include:
1. OpenCart version
2. PHP version
3. Extension version
4. Error messages
5. Steps to reproduce

## Contributing

We welcome contributions! Please follow these guidelines:

1. **Fork** the repository at https://github.com/Amivian/oc-v4-paystack.git     
2. **Create** a feature branch
3. **Make** your changes
4. **Test** thoroughly
5. **Submit** a pull request

### Development Setup
```bash
# Clone repository
git clone https://github.com/Amivian/oc-v4-paystack.git     .git

# Install dependencies
composer install

# Run tests
phpunit tests/
```

## Changelog

### Version 1.0.0
- ✅ Initial release
- ✅ Multiple payment methods support
- ✅ Webhook integration
- ✅ Transaction management
- ✅ Refund processing
- ✅ Admin dashboard
- ✅ Multi-currency support

## License

This extension is licensed under the [MIT License](LICENSE).
Copyright (c) 2024 Vivian Akpoke

## Disclaimer

This extension is provided "as is" without warranty of any kind. Use at your own risk. Always test thoroughly in a development environment before deploying to production.

---

**Developed by Vivian Akpoke**

- GitHub: https://github.com/Amivian
- Email: amiviann@gmail.com
- Repository: https://github.com/Amivian/oc-v4-paystack.git     

For Paystack API documentation, visit [paystack.com/docs](https://paystack.com/docs)
