# Paystack Payment Module Development Brief - OpenCart 4

## Project Overview

### Objective
Develop a comprehensive Paystack payment gateway integration module for OpenCart 4, enabling merchants to accept online payments through Paystack's secure payment infrastructure.

### Target Platform
- **OpenCart Version**: 4.x
- **PHP Version**: 8.0+
- **Database**: MySQL 5.7+ / MariaDB 10.3+

## Technical Requirements

### 1. Module Structure (Following OpenCart 4 Standards)

#### Directory Structure
```
extension/paystack/
├── admin/
│   ├── controller/payment/paystack.php
│   ├── language/en-gb/payment/paystack.php
│   ├── model/payment/paystack.php
│   └── view/template/payment/paystack.twig
├── catalog/
│   ├── controller/payment/paystack.php
│   ├── language/en-gb/payment/paystack.php
│   ├── model/payment/paystack.php
│   └── view/template/payment/paystack.twig
└── install.json
```

#### install.json Configuration
```json
{
    "extension_id": "paystack",
    "type": "payment",
    "version": "1.0.0",
    "opencart_version": "4.0.0.0",
    "name": "Paystack Payment Gateway",
    "description": "Accept payments via Paystack",
    "author": "Your Company",
    "link": "https://paystack.com"
}
```

### 2. Core Functionality Requirements

#### A. Admin Panel Features
- **Configuration Interface**
  - API Keys management (Test/Live)
  - Environment toggle (Sandbox/Production)
  - Payment method selection (Card, Bank Transfer, USSD, QR Code)
  - Webhook URL configuration
  - Transaction fees handling
  - Currency support configuration
  - Order status mapping
  - Debug logging toggle

- **Transaction Management**
  - View transaction history
  - Refund processing
  - Transaction verification
  - Webhook logs viewer

#### B. Frontend Features
- **Payment Form Integration**
  - Paystack Inline JS integration
  - Popup payment interface
  - Mobile-responsive design
  - Multiple payment methods support
  - Real-time payment status updates

- **Security Features**
  - CSRF protection
  - API signature verification
  - Webhook signature validation
  - PCI DSS compliance considerations

### 3. Database Schema

#### Payment Transactions Table
```sql
CREATE TABLE `oc_paystack_transaction` (
    `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `reference` varchar(255) NOT NULL,
    `access_code` varchar(255) DEFAULT NULL,
    `amount` decimal(15,4) NOT NULL,
    `currency` varchar(3) NOT NULL,
    `status` varchar(50) NOT NULL,
    `gateway_response` text,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`transaction_id`),
    UNIQUE KEY `reference` (`reference`),
    KEY `order_id` (`order_id`)
);
```

#### Webhook Logs Table
```sql
CREATE TABLE `oc_paystack_webhook_log` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `event_type` varchar(100) NOT NULL,
    `reference` varchar(255) NOT NULL,
    `payload` text NOT NULL,
    `signature` varchar(255) NOT NULL,
    `verified` tinyint(1) DEFAULT 0,
    `processed` tinyint(1) DEFAULT 0,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`log_id`),
    KEY `reference` (`reference`)
);
```

### 4. API Integration Specifications

#### Paystack API Endpoints
- **Initialize Transaction**: `POST /transaction/initialize`
- **Verify Transaction**: `GET /transaction/verify/:reference`
- **List Transactions**: `GET /transaction`
- **Refund Transaction**: `POST /refund`

#### Required API Methods
```php
class PaystackAPI {
    private $secretKey;
    private $publicKey;
    private $baseUrl;
    
    public function initializeTransaction($data);
    public function verifyTransaction($reference);
    public function processRefund($transactionId, $amount);
    public function validateWebhook($payload, $signature);
}
```

### 5. Controller Implementation

#### Admin Controller (admin/controller/payment/paystack.php)
```php
class ControllerPaymentPaystack extends Controller {
    public function index();           // Configuration page
    public function save();            // Save configuration
    public function install();         // Module installation
    public function uninstall();       // Module removal
    public function getTransactions(); // Transaction history
    public function refund();          // Process refunds
}
```

#### Catalog Controller (catalog/controller/payment/paystack.php)
```php
class ControllerPaymentPaystack extends Controller {
    public function index();       // Payment form display
    public function pay();         // Initialize payment
    public function callback();    // Payment callback handling
    public function webhook();     // Webhook endpoint
    public function verify();      // Payment verification
    public function cancel();      // Payment cancellation
}
```

### 6. Configuration Parameters

#### Required Settings
- `payment_paystack_test_secret_key`: Test Secret Key
- `payment_paystack_test_public_key`: Test Public Key
- `payment_paystack_live_secret_key`: Live Secret Key
- `payment_paystack_live_public_key`: Live Public Key
- `payment_paystack_environment`: sandbox/live
- `payment_paystack_payment_methods`: Array of enabled methods
- `payment_paystack_webhook_url`: Webhook endpoint URL
- `payment_paystack_debug`: Enable/disable debug logging

#### Order Status Mapping
- `payment_paystack_pending_status_id`: Pending payment status
- `payment_paystack_processing_status_id`: Processing status
- `payment_paystack_complete_status_id`: Completed status
- `payment_paystack_failed_status_id`: Failed status
- `payment_paystack_refunded_status_id`: Refunded status

### 7. Security Implementation

#### CSRF Protection
```php
// Generate and validate CSRF tokens for all forms
$this->session->data['csrf_token'] = bin2hex(random_bytes(32));
```

#### Webhook Signature Verification
```php
private function verifyWebhookSignature($payload, $signature) {
    $computed_signature = hash_hmac('sha512', $payload, $this->secret_key);
    return hash_equals($signature, $computed_signature);
}
```

#### API Communication Security
- Use cURL with SSL verification
- Implement request timeout handling
- Log all API communications for debugging
- Sanitize all user inputs

### 8. Error Handling & Logging

#### Error Management
```php
try {
    // Payment processing logic
} catch (PaystackException $e) {
    $this->log->write('Paystack Error: ' . $e->getMessage());
    $this->session->data['error'] = 'Payment processing failed';
} catch (Exception $e) {
    $this->log->write('General Error: ' . $e->getMessage());
    $this->session->data['error'] = 'An unexpected error occurred';
}
```

#### Debug Logging
- Transaction initialization logs
- API request/response logs
- Webhook payload logs
- Error and exception logs

### 9. Frontend Integration

#### Payment Form Template
```twig
<div id="paystack-payment-form">
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <button type="button" id="pay-now-btn">Pay Now</button>
    
    <script>
        document.getElementById('pay-now-btn').onclick = function(){
            var handler = PaystackPop.setup({
                key: '{{ public_key }}',
                email: '{{ customer_email }}',
                amount: {{ amount }},
                ref: '{{ reference }}',
                callback: function(response){
                    window.location = '{{ callback_url }}?reference=' + response.reference;
                },
                onClose: function(){
                    window.location = '{{ cancel_url }}';
                }
            });
            handler.openIframe();
        }
    </script>
</div>
```

### 10. Testing Requirements

#### Unit Testing
- API integration methods
- Webhook signature verification
- Transaction validation
- Currency conversion logic

#### Integration Testing
- End-to-end payment flow
- Webhook processing
- Refund functionality
- Multi-currency support

#### Test Cases
1. Successful payment processing
2. Failed payment handling
3. Webhook signature validation
4. Refund processing
5. Currency conversion accuracy
6. Order status synchronization

### 11. Deployment Checklist

#### Pre-deployment
- [ ] Code review completed
- [ ] Security audit passed
- [ ] Unit tests passing
- [ ] Integration tests completed
- [ ] Documentation updated
- [ ] Webhook endpoints tested

#### Production Deployment
- [ ] SSL certificate configured
- [ ] Webhook URL registered with Paystack
- [ ] Live API keys configured
- [ ] Database migrations executed
- [ ] Error monitoring enabled
- [ ] Backup procedures tested

### 12. Maintenance & Support

#### Regular Maintenance Tasks
- API version compatibility checks
- Security updates
- Performance optimization
- Log file rotation
- Database cleanup

#### Monitoring
- Transaction success rates
- API response times
- Error frequency
- Webhook delivery rates

### 13. Documentation Requirements

#### Technical Documentation
- API integration guide
- Installation instructions
- Configuration manual
- Troubleshooting guide

#### User Documentation
- Admin user guide
- Payment flow documentation
- FAQ section
- Video tutorials

### 14. Compliance Requirements

#### PCI DSS Compliance
- No storage of card data
- Secure API communication
- Regular security assessments
- Access control implementation

#### Data Protection
- Customer data encryption
- GDPR compliance measures
- Data retention policies
- Privacy policy updates

## Development Timeline

### Phase 1: Core Development (2-3 weeks)
- Module structure setup
- Basic payment integration
- Admin configuration panel
- Database schema implementation

### Phase 2: Advanced Features (1-2 weeks)
- Webhook implementation
- Refund functionality
- Multi-currency support
- Error handling enhancement

### Phase 3: Testing & Quality Assurance (1 week)
- Unit testing
- Integration testing
- Security testing
- Performance optimization

### Phase 4: Documentation & Deployment (1 week)
- Documentation completion
- Deployment preparation
- Final testing
- Go-live support

## Success Criteria

1. Successful payment processing with 99%+ reliability
2. Complete webhook implementation with real-time status updates
3. Comprehensive admin interface for transaction management
4. Full compliance with OpenCart 4 development standards
5. Secure implementation following PCI DSS guidelines
6. Complete documentation and support materials

## Risk Mitigation

### Technical Risks
- API changes: Implement version checking and fallback mechanisms
- Performance issues: Implement caching and optimization strategies
- Security vulnerabilities: Regular security audits and updates

### Business Risks
- Compatibility issues: Thorough testing across OpenCart versions
- User adoption: Comprehensive documentation and support
- Maintenance burden: Automated testing and monitoring systems