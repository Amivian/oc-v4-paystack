<?php
/**
 * Paystack Payment Gateway Catalog Language File
 * 
 * @package    Paystack Payment Gateway
 * @author     Vivian Akpoke
 * @version    1.0.0
 * @license    MIT
 */

// Heading
$_['heading_title'] = 'Paystack Payment Gateway';

// Text
$_['text_title'] = 'Credit Card / Debit Card / Bank Transfer (Paystack)';
$_['text_payment_method'] = 'Payment Method';
$_['text_loading'] = 'Loading...';
$_['text_please_wait'] = 'Please wait while we process your payment...';
$_['text_payment_initiated'] = 'Payment has been initiated. Please complete the payment process.';
$_['text_payment_success'] = 'Payment completed successfully! Thank you for your order.';
$_['text_payment_failed'] = 'Payment failed. Please try again or use a different payment method.';
$_['text_payment_cancelled'] = 'Payment was cancelled. You can try again or choose a different payment method.';
$_['text_payment_pending'] = 'Payment is being processed. You will receive an update shortly.';
$_['text_secure_payment'] = 'Secure Payment by Paystack';
$_['text_accepted_cards'] = 'We accept Visa, Mastercard, Verve and other major cards';
$_['text_pay_now'] = 'Pay Now';
$_['text_pay_with_card'] = 'Pay with Card';
$_['text_pay_with_bank'] = 'Pay with Bank';
$_['text_pay_with_ussd'] = 'Pay with USSD';
$_['text_pay_with_transfer'] = 'Pay with Bank Transfer';
$_['text_pay_with_qr'] = 'Pay with QR Code';
$_['text_processing'] = 'Processing Payment...';
$_['text_redirecting'] = 'Redirecting to payment gateway...';
$_['text_amount'] = 'Amount';
$_['text_reference'] = 'Reference';
$_['text_transaction_fee'] = 'Transaction Fee';
$_['text_total_amount'] = 'Total Amount';

// Button
$_['button_confirm'] = 'Confirm Payment';
$_['button_continue'] = 'Continue';
$_['button_back'] = 'Back';
$_['button_try_again'] = 'Try Again';

// Entry
$_['entry_email'] = 'Email Address';
$_['entry_phone'] = 'Phone Number';
$_['entry_amount'] = 'Amount';

// Error
$_['error_order_not_found'] = 'Order not found. Please try again.';
$_['error_invalid_reference'] = 'Invalid payment reference.';
$_['error_payment_failed'] = 'Payment failed. Please try again.';
$_['error_payment_cancelled'] = 'Payment was cancelled by user.';
$_['error_payment_pending'] = 'Payment is still pending verification.';
$_['error_verification_failed'] = 'Payment verification failed.';
$_['error_transaction_not_found'] = 'Transaction not found.';
$_['error_invalid_amount'] = 'Invalid payment amount.';
$_['error_currency_not_supported'] = 'Currency not supported by Paystack.';
$_['error_connection_failed'] = 'Unable to connect to payment gateway. Please try again.';
$_['error_invalid_configuration'] = 'Payment gateway is not properly configured.';
$_['error_minimum_amount'] = 'Order total must be at least %s to use this payment method.';

// Help
$_['help_secure_payment'] = 'Your payment information is encrypted and secure.';
$_['help_payment_methods'] = 'Choose your preferred payment method below.';
$_['help_transaction_fee'] = 'A small transaction fee may apply.';

// Success Messages
$_['success_payment_completed'] = 'Your payment has been completed successfully!';
$_['success_order_placed'] = 'Your order has been placed successfully.';

// Info Messages
$_['info_payment_processing'] = 'Your payment is being processed. Please do not close this window.';
$_['info_redirect_notice'] = 'You will be redirected to complete your payment.';
$_['info_webhook_processing'] = 'Payment confirmation is being processed. Please wait...';

// Payment Method Descriptions
$_['desc_card_payment'] = 'Pay securely with your debit or credit card';
$_['desc_bank_payment'] = 'Pay directly from your bank account';
$_['desc_ussd_payment'] = 'Pay using USSD code on your mobile phone';
$_['desc_qr_payment'] = 'Scan QR code to complete payment';
$_['desc_transfer_payment'] = 'Make a bank transfer to complete payment';
$_['desc_mobile_money'] = 'Pay using mobile money services';

// Payment Steps
$_['step_1'] = 'Step 1: Review Order';
$_['step_2'] = 'Step 2: Choose Payment Method';
$_['step_3'] = 'Step 3: Complete Payment';
$_['step_4'] = 'Step 4: Confirmation';

// Status Messages
$_['status_pending'] = 'Pending';
$_['status_processing'] = 'Processing';
$_['status_completed'] = 'Completed';
$_['status_failed'] = 'Failed';
$_['status_cancelled'] = 'Cancelled';
$_['status_refunded'] = 'Refunded';

// Labels
$_['label_order_id'] = 'Order ID:';
$_['label_reference'] = 'Reference:';
$_['label_amount'] = 'Amount:';
$_['label_currency'] = 'Currency:';
$_['label_payment_method'] = 'Payment Method:';
$_['label_status'] = 'Status:';
$_['label_date'] = 'Date:';
$_['label_customer'] = 'Customer:';
$_['label_email'] = 'Email:';
$_['label_phone'] = 'Phone:';

// Validation Messages
$_['validation_required'] = 'This field is required';
$_['validation_email'] = 'Please enter a valid email address';
$_['validation_phone'] = 'Please enter a valid phone number';
$_['validation_amount'] = 'Please enter a valid amount';

// Payment Gateway Messages
$_['gateway_initializing'] = 'Initializing payment gateway...';
$_['gateway_ready'] = 'Payment gateway is ready';
$_['gateway_error'] = 'Payment gateway error occurred';
$_['gateway_timeout'] = 'Payment gateway timeout. Please try again.';

// Security Messages
$_['security_ssl'] = 'This page is secured with SSL encryption';
$_['security_pci'] = 'PCI DSS compliant payment processing';
$_['security_safe'] = 'Your payment information is safe and secure';

// Footer Text
$_['footer_powered_by'] = 'Powered by Paystack';
$_['footer_secure_payment'] = 'Secure Payment Processing';

// Mobile Messages
$_['mobile_optimized'] = 'Mobile optimized payment experience';
$_['mobile_touch_id'] = 'Use Touch ID or Face ID where available';

// Accessibility
$_['aria_payment_form'] = 'Payment form';
$_['aria_payment_method'] = 'Payment method selection';
$_['aria_amount_display'] = 'Payment amount display';
$_['aria_secure_form'] = 'Secure payment form';

// Time-related
$_['time_expires_in'] = 'Payment session expires in';
$_['time_expired'] = 'Payment session has expired';
$_['time_minutes'] = 'minutes';
$_['time_seconds'] = 'seconds';

// Currency Formatting
$_['currency_symbol_ngn'] = '₦';
$_['currency_symbol_usd'] = '$';
$_['currency_symbol_ghs'] = 'GH₵';
$_['currency_symbol_zar'] = 'R';
$_['currency_symbol_kes'] = 'KSh';

// Countries
$_['country_nigeria'] = 'Nigeria';
$_['country_ghana'] = 'Ghana';
$_['country_south_africa'] = 'South Africa';
$_['country_kenya'] = 'Kenya';

// Bank Names (for bank transfer)
$_['bank_access'] = 'Access Bank';
$_['bank_gtb'] = 'GTBank';
$_['bank_zenith'] = 'Zenith Bank';
$_['bank_uba'] = 'UBA';
$_['bank_first'] = 'First Bank';
$_['bank_fidelity'] = 'Fidelity Bank';
$_['bank_fcmb'] = 'FCMB';
$_['bank_union'] = 'Union Bank';
$_['bank_sterling'] = 'Sterling Bank';
$_['bank_stanbic'] = 'Stanbic IBTC';

// USSD Codes
$_['ussd_access'] = '*901#';
$_['ussd_gtb'] = '*737#';
$_['ussd_zenith'] = '*966#';
$_['ussd_uba'] = '*919#';
$_['ussd_first'] = '*894#';

// Instructions
$_['instruction_card'] = 'Enter your card details to complete payment';
$_['instruction_bank'] = 'Select your bank and follow the prompts';
$_['instruction_ussd'] = 'Dial the USSD code and follow instructions';
$_['instruction_qr'] = 'Scan the QR code with your banking app';
$_['instruction_transfer'] = 'Transfer to the account details provided';

// Terms and Conditions
$_['terms_agreement'] = 'By proceeding, you agree to our terms and conditions';
$_['terms_privacy'] = 'Your privacy is protected according to our privacy policy';
$_['terms_refund'] = 'Refund policy applies as per merchant terms';
