<?php
/**
 * Paystack Payment Gateway Catalog Controller
 * 
 * @package    Paystack Payment Gateway
 * @author     Vivian Akpoke
 * @version    1.0.0
 * @license    MIT
 */

namespace Opencart\Catalog\Controller\Extension\Paystack\Payment;

class Paystack extends \Opencart\System\Engine\Controller {
    
    /**
     * Main payment method display
     */
    public function index(): string {
        $this->load->language('extension/paystack/payment/paystack');
        
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');
        
        // Get payment configuration
        $data['test_mode'] = $this->config->get('payment_paystack_test_mode');
        $data['public_key'] = $data['test_mode'] 
            ? $this->config->get('payment_paystack_test_public_key')
            : $this->config->get('payment_paystack_live_public_key');
        
        $data['payment_methods'] = $this->config->get('payment_paystack_payment_methods') ?: ['card'];
        
        // Get order information
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        
        if ($order_info) {
            // Calculate total amount including fees
            $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
            $transaction_fee = (float)$this->config->get('payment_paystack_transaction_fee');
            
            if ($transaction_fee > 0) {
                $fee_amount = ($amount * $transaction_fee) / 100;
                $amount += $fee_amount;
            }
            
            $data['amount'] = $amount;
            $data['currency'] = $order_info['currency_code'];
            $data['email'] = $order_info['email'];
            $data['first_name'] = $order_info['firstname'];
            $data['last_name'] = $order_info['lastname'];
            $data['phone'] = $order_info['telephone'];
            
            // Generate transaction reference
            $this->load->library('paystack');
            $data['reference'] = \Paystack::generateReference('OC');
            
            // Store transaction reference in session
            $this->session->data['paystack_reference'] = $data['reference'];
            
            // Callback URLs
            $data['callback_url'] = $this->url->link('extension/paystack/payment/paystack|callback', '', true);
            $data['webhook_url'] = $this->url->link('extension/paystack/payment/paystack|webhook', '', true);
            
            // Custom fields
            $custom_fields = $this->config->get('payment_paystack_custom_fields');
            if ($custom_fields && is_string($custom_fields)) {
                $data['custom_fields'] = json_decode($custom_fields, true) ?: [];
            } else {
                $data['custom_fields'] = [];
            }
            
            // Initialize transaction with Paystack
            try {
                $secret_key = $data['test_mode'] 
                    ? $this->config->get('payment_paystack_test_secret_key')
                    : $this->config->get('payment_paystack_live_secret_key');
                
                $paystack = new \Paystack($secret_key, $data['public_key'], $data['test_mode']);
                
                $transaction_data = [
                    'email' => $data['email'],
                    'amount' => $amount,
                    'currency' => $data['currency'],
                    'reference' => $data['reference'],
                    'callback_url' => $data['callback_url'],
                    'metadata' => [
                        'order_id' => $this->session->data['order_id'],
                        'customer_name' => $data['first_name'] . ' ' . $data['last_name'],
                        'customer_phone' => $data['phone']
                    ]
                ];
                
                // Add custom fields if any
                if (!empty($data['custom_fields'])) {
                    $transaction_data['metadata']['custom_fields'] = $data['custom_fields'];
                }
                
                $result = $paystack->initializeTransaction($transaction_data);
                
                if (isset($result['status']) && $result['status']) {
                    $data['access_code'] = $result['data']['access_code'];
                    $data['authorization_url'] = $result['data']['authorization_url'];
                    
                    // Store transaction in database
                    $this->load->model('extension/paystack/payment/paystack');
                    $this->model_extension_paystack_payment_paystack->addTransaction([
                        'order_id' => $this->session->data['order_id'],
                        'reference' => $data['reference'],
                        'access_code' => $data['access_code'],
                        'amount' => $amount,
                        'currency' => $data['currency'],
                        'status' => 'pending',
                        'customer_email' => $data['email'],
                        'customer_name' => $data['first_name'] . ' ' . $data['last_name'],
                        'gateway_response' => json_encode($result)
                    ]);
                } else {
                    $data['error'] = $result['message'] ?? 'Failed to initialize payment';
                }
            } catch (Exception $e) {
                $data['error'] = $e->getMessage();
                $this->log->write('Paystack Error: ' . $e->getMessage());
            }
        } else {
            $data['error'] = $this->language->get('error_order_not_found');
        }
        
        return $this->load->view('extension/paystack/payment/paystack', $data);
    }
    
    /**
     * Payment confirmation
     */
    public function confirm(): void {
        $this->load->language('extension/paystack/payment/paystack');
        
        $json = [];
        
        if (!isset($this->session->data['order_id'])) {
            $json['error'] = $this->language->get('error_order_not_found');
        } else {
            $this->load->model('checkout/order');
            $this->load->model('extension/paystack/payment/paystack');
            
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            
            if ($order_info) {
                // Update order status to pending
                $pending_status_id = $this->config->get('payment_paystack_pending_order_status_id');
                
                if ($pending_status_id) {
                    $this->model_checkout_order->addHistory(
                        $this->session->data['order_id'],
                        $pending_status_id,
                        'Payment initiated via Paystack',
                        false
                    );
                }
                
                $json['success'] = $this->language->get('text_payment_initiated');
            } else {
                $json['error'] = $this->language->get('error_order_not_found');
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Payment callback handler
     */
    public function callback(): void {
        $this->load->language('extension/paystack/payment/paystack');
        $this->load->model('extension/paystack/payment/paystack');
        $this->load->model('checkout/order');
        
        $reference = isset($this->request->get['reference']) ? $this->request->get['reference'] : '';
        
        if (empty($reference)) {
            $this->session->data['error'] = $this->language->get('error_invalid_reference');
            $this->response->redirect($this->url->link('checkout/failure'));
            return;
        }
        
        try {
            // Verify transaction with Paystack
            $test_mode = $this->config->get('payment_paystack_test_mode');
            $secret_key = $test_mode 
                ? $this->config->get('payment_paystack_test_secret_key')
                : $this->config->get('payment_paystack_live_secret_key');
            
            $this->load->library('paystack');
            $paystack = new \Paystack($secret_key, '', $test_mode);
            
            $result = $paystack->verifyTransaction($reference);
            
            if (isset($result['status']) && $result['status'] && isset($result['data'])) {
                $transaction_data = $result['data'];
                $payment_status = $transaction_data['status'];
                
                // Get transaction from database
                $transaction = $this->model_extension_paystack_payment_paystack->getTransactionByReference($reference);
                
                if ($transaction) {
                    $order_id = $transaction['order_id'];
                    $order_info = $this->model_checkout_order->getOrder($order_id);
                    
                    if ($order_info) {
                        // Update transaction status
                        $this->model_extension_paystack_payment_paystack->updateTransaction($transaction['transaction_id'], [
                            'status' => $payment_status,
                            'gateway_response' => json_encode($result),
                            'payment_method' => $transaction_data['channel'] ?? '',
                            'authorization_code' => $transaction_data['authorization']['authorization_code'] ?? ''
                        ]);
                        
                        if ($payment_status === 'success') {
                            // Payment successful
                            $completed_status_id = $this->config->get('payment_paystack_completed_order_status_id');
                            
                            if ($completed_status_id) {
                                $this->model_checkout_order->addHistory(
                                    $order_id,
                                    $completed_status_id,
                                    'Payment completed via Paystack. Reference: ' . $reference,
                                    true
                                );
                            }
                            
                            $success_message = $this->config->get('payment_paystack_success_message') 
                                ?: $this->language->get('text_payment_success');
                            
                            $this->session->data['success'] = $success_message;
                            $this->response->redirect($this->url->link('checkout/success'));
                        } else {
                            // Payment failed or pending
                            $status_id = ($payment_status === 'failed') 
                                ? $this->config->get('payment_paystack_failed_order_status_id')
                                : $this->config->get('payment_paystack_pending_order_status_id');
                            
                            if ($status_id) {
                                $this->model_checkout_order->addHistory(
                                    $order_id,
                                    $status_id,
                                    'Payment ' . $payment_status . ' via Paystack. Reference: ' . $reference,
                                    false
                                );
                            }
                            
                            $this->session->data['error'] = $this->language->get('error_payment_' . $payment_status);
                            $this->response->redirect($this->url->link('checkout/failure'));
                        }
                    } else {
                        $this->session->data['error'] = $this->language->get('error_order_not_found');
                        $this->response->redirect($this->url->link('checkout/failure'));
                    }
                } else {
                    $this->session->data['error'] = $this->language->get('error_transaction_not_found');
                    $this->response->redirect($this->url->link('checkout/failure'));
                }
            } else {
                $this->session->data['error'] = $this->language->get('error_verification_failed');
                $this->response->redirect($this->url->link('checkout/failure'));
            }
        } catch (Exception $e) {
            $this->log->write('Paystack Callback Error: ' . $e->getMessage());
            $this->session->data['error'] = $this->language->get('error_payment_failed');
            $this->response->redirect($this->url->link('checkout/failure'));
        }
    }
    
    /**
     * Webhook handler
     */
    public function webhook(): void {
        $this->load->model('extension/paystack/payment/paystack');
        $this->load->model('checkout/order');
        
        // Get raw POST data
        $payload = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] : '';
        
        if (empty($payload) || empty($signature)) {
            http_response_code(400);
            exit('Invalid webhook data');
        }
        
        try {
            // Validate webhook signature
            $secret_key = $this->config->get('payment_paystack_test_mode')
                ? $this->config->get('payment_paystack_test_secret_key')
                : $this->config->get('payment_paystack_live_secret_key');
            
            $this->load->library('paystack');
            $paystack = new \Paystack($secret_key);
            
            $is_valid = $paystack->validateWebhook($payload, $signature);
            
            $webhook_data = json_decode($payload, true);
            $event_type = $webhook_data['event'] ?? '';
            $reference = $webhook_data['data']['reference'] ?? '';
            
            // Log webhook
            $log_id = $this->model_extension_paystack_payment_paystack->logWebhook([
                'event_type' => $event_type,
                'reference' => $reference,
                'payload' => $payload,
                'signature' => $signature,
                'verified' => $is_valid ? 1 : 0
            ]);
            
            if (!$is_valid) {
                $this->model_extension_paystack_payment_paystack->updateWebhookLog($log_id, [
                    'error_message' => 'Invalid webhook signature'
                ]);
                http_response_code(400);
                exit('Invalid signature');
            }
            
            // Process webhook based on event type
            switch ($event_type) {
                case 'charge.success':
                    $this->processSuccessfulCharge($webhook_data['data'], $log_id);
                    break;
                    
                case 'charge.failed':
                    $this->processFailedCharge($webhook_data['data'], $log_id);
                    break;
                    
                case 'transfer.success':
                case 'transfer.failed':
                    $this->processTransferEvent($webhook_data['data'], $event_type, $log_id);
                    break;
                    
                default:
                    $this->model_extension_paystack_payment_paystack->updateWebhookLog($log_id, [
                        'processed' => 1,
                        'error_message' => 'Unhandled event type: ' . $event_type
                    ]);
                    break;
            }
            
            http_response_code(200);
            exit('OK');
            
        } catch (Exception $e) {
            $this->log->write('Paystack Webhook Error: ' . $e->getMessage());
            
            if (isset($log_id)) {
                $this->model_extension_paystack_payment_paystack->updateWebhookLog($log_id, [
                    'error_message' => $e->getMessage()
                ]);
            }
            
            http_response_code(500);
            exit('Internal server error');
        }
    }
    
    /**
     * Process successful charge webhook
     */
    private function processSuccessfulCharge(array $data, int $log_id): void {
        $reference = $data['reference'];
        $transaction = $this->model_extension_paystack_payment_paystack->getTransactionByReference($reference);
        
        if ($transaction && $transaction['status'] !== 'success') {
            // Update transaction status
            $this->model_extension_paystack_payment_paystack->updateTransaction($transaction['transaction_id'], [
                'status' => 'success',
                'gateway_response' => json_encode($data),
                'payment_method' => $data['channel'] ?? '',
                'authorization_code' => $data['authorization']['authorization_code'] ?? ''
            ]);
            
            // Update order status
            $completed_status_id = $this->config->get('payment_paystack_completed_order_status_id');
            
            if ($completed_status_id) {
                $this->model_checkout_order->addHistory(
                    $transaction['order_id'],
                    $completed_status_id,
                    'Payment completed via webhook. Reference: ' . $reference,
                    true
                );
            }
            
            $this->model_extension_paystack_payment_paystack->updateWebhookLog($log_id, [
                'processed' => 1
            ]);
        } else {
            $this->model_extension_paystack_payment_paystack->updateWebhookLog($log_id, [
                'processed' => 1,
                'error_message' => 'Transaction not found or already processed'
            ]);
        }
    }
    
    /**
     * Process failed charge webhook
     */
    private function processFailedCharge(array $data, int $log_id): void {
        $reference = $data['reference'];
        $transaction = $this->model_extension_paystack_payment_paystack->getTransactionByReference($reference);
        
        if ($transaction && $transaction['status'] !== 'failed') {
            // Update transaction status
            $this->model_extension_paystack_payment_paystack->updateTransaction($transaction['transaction_id'], [
                'status' => 'failed',
                'gateway_response' => json_encode($data)
            ]);
            
            // Update order status
            $failed_status_id = $this->config->get('payment_paystack_failed_order_status_id');
            
            if ($failed_status_id) {
                $this->model_checkout_order->addHistory(
                    $transaction['order_id'],
                    $failed_status_id,
                    'Payment failed via webhook. Reference: ' . $reference,
                    false
                );
            }
            
            $this->model_extension_paystack_payment_paystack->updateWebhookLog($log_id, [
                'processed' => 1
            ]);
        } else {
            $this->model_extension_paystack_payment_paystack->updateWebhookLog($log_id, [
                'processed' => 1,
                'error_message' => 'Transaction not found or already processed'
            ]);
        }
    }
    
    /**
     * Process transfer events
     */
    private function processTransferEvent(array $data, string $event_type, int $log_id): void {
        // Handle transfer events (for refunds, etc.)
        $this->model_extension_paystack_payment_paystack->updateWebhookLog($log_id, [
            'processed' => 1,
            'error_message' => 'Transfer event logged: ' . $event_type
        ]);
    }
}
