<?php
/**
 * Paystack Payment Gateway Admin Language File
 * 
 * @package    Paystack Payment Gateway
 * @author     Vivian Akpoke
 * @version    1.0.0
 * @license    MIT
 */

// Heading
$_['heading_title'] = 'Paystack Payment Gateway';
$_['heading_transactions'] = 'Paystack Transactions';
$_['heading_transaction_details'] = 'Transaction Details';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Success: You have modified Paystack payment module!';
$_['text_edit'] = 'Edit Paystack Payment';
$_['text_paystack'] = '<img src="view/image/payment/paystack.png" alt="Paystack" title="Paystack" style="border: 1px solid #EEEEEE;" />';
$_['text_test'] = 'Test';
$_['text_live'] = 'Live';
$_['text_yes'] = 'Yes';
$_['text_no'] = 'No';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_all_zones'] = 'All Zones';
$_['text_card'] = 'Card Payment';
$_['text_bank'] = 'Bank Payment';
$_['text_ussd'] = 'USSD';
$_['text_qr'] = 'QR Code';
$_['text_mobile_money'] = 'Mobile Money';
$_['text_bank_transfer'] = 'Bank Transfer';
$_['text_merchant'] = 'Merchant';
$_['text_customer'] = 'Customer';
$_['text_connection_success'] = 'Connection successful! API credentials are valid.';
$_['text_refund_success'] = 'Refund processed successfully!';
$_['text_pending'] = 'Pending';
$_['text_success_status'] = 'Success';
$_['text_failed'] = 'Failed';
$_['text_cancelled'] = 'Cancelled';
$_['text_refunded'] = 'Refunded';

// Tab
$_['tab_general'] = 'General';
$_['tab_api_settings'] = 'API Settings';
$_['tab_payment_options'] = 'Payment Options';
$_['tab_order_status'] = 'Order Status';
$_['tab_advanced'] = 'Advanced';
$_['tab_transactions'] = 'Transactions';
$_['tab_webhooks'] = 'Webhooks';

// Entry
$_['entry_test_mode'] = 'Test Mode';
$_['entry_test_secret_key'] = 'Test Secret Key';
$_['entry_test_public_key'] = 'Test Public Key';
$_['entry_live_secret_key'] = 'Live Secret Key';
$_['entry_live_public_key'] = 'Live Public Key';
$_['entry_webhook_url'] = 'Webhook URL';
$_['entry_payment_methods'] = 'Payment Methods';
$_['entry_transaction_fee'] = 'Transaction Fee (%)';
$_['entry_fee_bearer'] = 'Fee Bearer';
$_['entry_custom_fields'] = 'Custom Fields';
$_['entry_success_message'] = 'Success Message';
$_['entry_failed_order_status'] = 'Failed Order Status';
$_['entry_pending_order_status'] = 'Pending Order Status';
$_['entry_completed_order_status'] = 'Completed Order Status';
$_['entry_refunded_order_status'] = 'Refunded Order Status';
$_['entry_total'] = 'Total';
$_['entry_geo_zone'] = 'Geo Zone';
$_['entry_status'] = 'Status';
$_['entry_sort_order'] = 'Sort Order';
$_['entry_debug_mode'] = 'Debug Mode';

// Column
$_['column_transaction_id'] = 'Transaction ID';
$_['column_order_id'] = 'Order ID';
$_['column_reference'] = 'Reference';
$_['column_amount'] = 'Amount';
$_['column_status'] = 'Status';
$_['column_customer'] = 'Customer';
$_['column_payment_method'] = 'Payment Method';
$_['column_date_added'] = 'Date Added';
$_['column_action'] = 'Action';

// Button
$_['button_test_connection'] = 'Test Connection';
$_['button_view'] = 'View';
$_['button_refund'] = 'Refund';
$_['button_filter'] = 'Filter';
$_['button_clear'] = 'Clear';

// Help
$_['help_test_mode'] = 'Use test mode to test payments without processing real transactions.';
$_['help_test_secret_key'] = 'Your Paystack test secret key (starts with sk_test_).';
$_['help_test_public_key'] = 'Your Paystack test public key (starts with pk_test_).';
$_['help_live_secret_key'] = 'Your Paystack live secret key (starts with sk_live_).';
$_['help_live_public_key'] = 'Your Paystack live public key (starts with pk_live_).';
$_['help_webhook_url'] = 'Copy this URL to your Paystack dashboard webhook settings.';
$_['help_payment_methods'] = 'Select which payment methods to enable for customers.';
$_['help_transaction_fee'] = 'Additional fee percentage to charge customers (0 for no fee).';
$_['help_fee_bearer'] = 'Who should bear the Paystack transaction fees.';
$_['help_custom_fields'] = 'JSON array of custom fields to collect during payment.';
$_['help_total'] = 'The checkout total the order must reach before this payment method becomes active.';
$_['help_debug_mode'] = 'Enable debug mode to log detailed transaction information.';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify Paystack payment!';
$_['error_test_secret_key'] = 'Test Secret Key required when test mode is enabled!';
$_['error_test_public_key'] = 'Test Public Key required when test mode is enabled!';
$_['error_live_secret_key'] = 'Live Secret Key required when live mode is enabled!';
$_['error_live_public_key'] = 'Live Public Key required when live mode is enabled!';
$_['error_secret_key_required'] = 'Secret key is required for API connection test!';
$_['error_connection_failed'] = 'Failed to connect to Paystack API. Please check your credentials.';
$_['error_invalid_request'] = 'Invalid request method.';
$_['error_transaction_not_found'] = 'Transaction not found.';

// Success
$_['success_install'] = 'Paystack payment module installed successfully!';
$_['success_uninstall'] = 'Paystack payment module uninstalled successfully!';

// Info
$_['info_version'] = 'Version 1.0.0';
$_['info_author'] = 'Vivian Akpoke';
$_['info_support'] = 'For support, contact: vivian.akpoke@example.com';

// Transaction Details
$_['text_transaction_details'] = 'Transaction Details';
$_['text_order_details'] = 'Order Details';
$_['text_customer_details'] = 'Customer Details';
$_['text_payment_details'] = 'Payment Details';
$_['text_gateway_response'] = 'Gateway Response';
$_['text_refund_history'] = 'Refund History';

// Labels
$_['label_reference'] = 'Reference:';
$_['label_access_code'] = 'Access Code:';
$_['label_amount'] = 'Amount:';
$_['label_currency'] = 'Currency:';
$_['label_status'] = 'Status:';
$_['label_customer_email'] = 'Customer Email:';
$_['label_customer_name'] = 'Customer Name:';
$_['label_payment_method'] = 'Payment Method:';
$_['label_authorization_code'] = 'Authorization Code:';
$_['label_created_at'] = 'Created At:';
$_['label_updated_at'] = 'Updated At:';

// Refund
$_['text_refund'] = 'Process Refund';
$_['entry_refund_amount'] = 'Refund Amount';
$_['entry_refund_reason'] = 'Reason';
$_['help_refund_amount'] = 'Leave empty to refund full amount.';
$_['button_process_refund'] = 'Process Refund';

// Webhook
$_['text_webhook_logs'] = 'Webhook Logs';
$_['column_event_type'] = 'Event Type';
$_['column_verified'] = 'Verified';
$_['column_processed'] = 'Processed';
$_['column_error'] = 'Error';
$_['text_verified'] = 'Verified';
$_['text_not_verified'] = 'Not Verified';
$_['text_processed'] = 'Processed';
$_['text_not_processed'] = 'Not Processed';

// Statistics
$_['text_statistics'] = 'Payment Statistics';
$_['text_total_transactions'] = 'Total Transactions';
$_['text_successful_transactions'] = 'Successful Transactions';
$_['text_failed_transactions'] = 'Failed Transactions';
$_['text_pending_transactions'] = 'Pending Transactions';
$_['text_total_amount'] = 'Total Amount';
$_['text_success_rate'] = 'Success Rate';

// Filters
$_['entry_filter_reference'] = 'Reference';
$_['entry_filter_status'] = 'Status';
$_['entry_filter_order_id'] = 'Order ID';
$_['entry_filter_date_start'] = 'Date Start';
$_['entry_filter_date_end'] = 'Date End';

// Validation Messages
$_['text_field_required'] = 'This field is required';
$_['text_invalid_email'] = 'Invalid email address';
$_['text_invalid_amount'] = 'Invalid amount';
$_['text_invalid_key_format'] = 'Invalid key format';

// Payment Method Descriptions
$_['desc_card'] = 'Accept payments via debit/credit cards';
$_['desc_bank'] = 'Accept payments via bank accounts';
$_['desc_ussd'] = 'Accept payments via USSD codes';
$_['desc_qr'] = 'Accept payments via QR codes';
$_['desc_mobile_money'] = 'Accept payments via mobile money';
$_['desc_bank_transfer'] = 'Accept payments via bank transfers';
