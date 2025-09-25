<?php
/**
 * Paystack Payment Gateway Admin Controller
 * 
 * @package    Paystack Payment Gateway
 * @author     Vivian Akpoke
 * @version    1.0.0
 * @license    MIT
 */

namespace Opencart\Admin\Controller\Extension\Paystack\Payment;

class Paystack extends \Opencart\System\Engine\Controller {
    
    private $error = [];
    
    /**
     * Main configuration page
     */
    public function index(): void {
        $this->load->language('extension/paystack/payment/paystack');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');
        $this->load->model('extension/paystack/payment/paystack');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_paystack', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment'));
        }
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['test_secret_key'])) {
            $data['error_test_secret_key'] = $this->error['test_secret_key'];
        } else {
            $data['error_test_secret_key'] = '';
        }
        
        if (isset($this->error['test_public_key'])) {
            $data['error_test_public_key'] = $this->error['test_public_key'];
        } else {
            $data['error_test_public_key'] = '';
        }
        
        if (isset($this->error['live_secret_key'])) {
            $data['error_live_secret_key'] = $this->error['live_secret_key'];
        } else {
            $data['error_live_secret_key'] = '';
        }
        
        if (isset($this->error['live_public_key'])) {
            $data['error_live_public_key'] = $this->error['live_public_key'];
        } else {
            $data['error_live_public_key'] = '';
        }
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/paystack/payment/paystack', 'user_token=' . $this->session->data['user_token'])
        ];
        
        $data['action'] = $this->url->link('extension/paystack/payment/paystack', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');
        
        // Configuration fields
        $config_fields = [
            'payment_paystack_test_mode',
            'payment_paystack_test_secret_key',
            'payment_paystack_test_public_key',
            'payment_paystack_live_secret_key',
            'payment_paystack_live_public_key',
            'payment_paystack_webhook_url',
            'payment_paystack_payment_methods',
            'payment_paystack_transaction_fee',
            'payment_paystack_fee_bearer',
            'payment_paystack_custom_fields',
            'payment_paystack_success_message',
            'payment_paystack_failed_order_status_id',
            'payment_paystack_pending_order_status_id',
            'payment_paystack_completed_order_status_id',
            'payment_paystack_refunded_order_status_id',
            'payment_paystack_total',
            'payment_paystack_geo_zone_id',
            'payment_paystack_status',
            'payment_paystack_sort_order',
            'payment_paystack_debug_mode'
        ];
        
        foreach ($config_fields as $field) {
            if (isset($this->request->post[$field])) {
                $data[$field] = $this->request->post[$field];
            } else {
                $data[$field] = $this->config->get($field);
            }
        }
        
        // Load order statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        
        // Load geo zones
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        
        // Payment methods
        $data['payment_methods'] = [
            'card' => $this->language->get('text_card'),
            'bank' => $this->language->get('text_bank'),
            'ussd' => $this->language->get('text_ussd'),
            'qr' => $this->language->get('text_qr'),
            'mobile_money' => $this->language->get('text_mobile_money'),
            'bank_transfer' => $this->language->get('text_bank_transfer')
        ];
        
        // Fee bearer options
        $data['fee_bearer_options'] = [
            'account' => $this->language->get('text_merchant'),
            'subaccount' => $this->language->get('text_customer')
        ];
        
        // Generate webhook URL
        $data['webhook_url_generated'] = $this->url->link('extension/paystack/payment/paystack|webhook', '', true);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/paystack/payment/paystack', $data));
    }
    
    /**
     * Install method
     */
    public function install(): void {
        $this->load->model('extension/paystack/payment/paystack');
        $this->model_extension_paystack_payment_paystack->install();
    }
    
    /**
     * Uninstall method
     */
    public function uninstall(): void {
        $this->load->model('extension/paystack/payment/paystack');
        $this->model_extension_paystack_payment_paystack->uninstall();
    }
    
    /**
     * Transaction management page
     */
    public function transactions(): void {
        $this->load->language('extension/paystack/payment/paystack');
        
        $this->document->setTitle($this->language->get('heading_transactions'));
        
        $this->load->model('extension/paystack/payment/paystack');
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_transactions'),
            'href' => $this->url->link('extension/paystack/payment/paystack|transactions', 'user_token=' . $this->session->data['user_token'])
        ];
        
        // Pagination
        $page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
        $limit = 20;
        $start = ($page - 1) * $limit;
        
        // Filters
        $filter_data = [
            'start' => $start,
            'limit' => $limit
        ];
        
        if (isset($this->request->get['filter_reference'])) {
            $filter_data['filter_reference'] = $this->request->get['filter_reference'];
            $data['filter_reference'] = $this->request->get['filter_reference'];
        } else {
            $data['filter_reference'] = '';
        }
        
        if (isset($this->request->get['filter_status'])) {
            $filter_data['filter_status'] = $this->request->get['filter_status'];
            $data['filter_status'] = $this->request->get['filter_status'];
        } else {
            $data['filter_status'] = '';
        }
        
        $transactions = $this->model_extension_paystack_payment_paystack->getTransactions($filter_data);
        $total_transactions = $this->model_extension_paystack_payment_paystack->getTotalTransactions($filter_data);
        
        $data['transactions'] = [];
        
        foreach ($transactions as $transaction) {
            $data['transactions'][] = [
                'transaction_id' => $transaction['transaction_id'],
                'order_id' => $transaction['order_id'],
                'reference' => $transaction['reference'],
                'amount' => $this->currency->format($transaction['amount'], $transaction['currency']),
                'status' => $transaction['status'],
                'customer_email' => $transaction['customer_email'],
                'payment_method' => $transaction['payment_method'],
                'created_at' => date($this->language->get('datetime_format'), strtotime($transaction['created_at'])),
                'view' => $this->url->link('extension/paystack/payment/paystack|viewTransaction', 'user_token=' . $this->session->data['user_token'] . '&transaction_id=' . $transaction['transaction_id']),
                'refund' => $this->url->link('extension/paystack/payment/paystack|refund', 'user_token=' . $this->session->data['user_token'] . '&transaction_id=' . $transaction['transaction_id'])
            ];
        }
        
        // Pagination
        $pagination = new \Opencart\System\Library\Pagination();
        $pagination->total = $total_transactions;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/paystack/payment/paystack|transactions', 'user_token=' . $this->session->data['user_token'] . '&page={page}');
        
        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_transactions) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total_transactions - $limit)) ? $total_transactions : ((($page - 1) * $limit) + $limit), $total_transactions, ceil($total_transactions / $limit));
        
        $data['filter_url'] = $this->url->link('extension/paystack/payment/paystack|transactions', 'user_token=' . $this->session->data['user_token']);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/paystack/payment/paystack_transactions', $data));
    }
    
    /**
     * View transaction details
     */
    public function viewTransaction(): void {
        $this->load->language('extension/paystack/payment/paystack');
        
        $this->document->setTitle($this->language->get('heading_transaction_details'));
        
        $this->load->model('extension/paystack/payment/paystack');
        
        $transaction_id = isset($this->request->get['transaction_id']) ? (int)$this->request->get['transaction_id'] : 0;
        
        $transaction = $this->model_extension_paystack_payment_paystack->getTransaction($transaction_id);
        
        if (!$transaction) {
            $this->session->data['error'] = $this->language->get('error_transaction_not_found');
            $this->response->redirect($this->url->link('extension/paystack/payment/paystack|transactions', 'user_token=' . $this->session->data['user_token']));
        }
        
        $data['transaction'] = $transaction;
        $data['gateway_response'] = json_decode($transaction['gateway_response'], true);
        
        $data['back'] = $this->url->link('extension/paystack/payment/paystack|transactions', 'user_token=' . $this->session->data['user_token']);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/paystack/payment/paystack_transaction_details', $data));
    }
    
    /**
     * Process refund
     */
    public function refund(): void {
        $this->load->language('extension/paystack/payment/paystack');
        $this->load->model('extension/paystack/payment/paystack');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $transaction_id = isset($this->request->post['transaction_id']) ? (int)$this->request->post['transaction_id'] : 0;
            $amount = isset($this->request->post['amount']) ? (float)$this->request->post['amount'] : 0;
            $reason = isset($this->request->post['reason']) ? $this->request->post['reason'] : '';
            
            try {
                $result = $this->model_extension_paystack_payment_paystack->processRefund($transaction_id, $amount, $reason);
                
                if ($result['status']) {
                    $json['success'] = $this->language->get('text_refund_success');
                } else {
                    $json['error'] = $result['message'];
                }
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        } else {
            $json['error'] = $this->language->get('error_invalid_request');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Test API connection
     */
    public function testConnection(): void {
        $this->load->language('extension/paystack/payment/paystack');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $secret_key = isset($this->request->post['secret_key']) ? $this->request->post['secret_key'] : '';
            $test_mode = isset($this->request->post['test_mode']) ? (bool)$this->request->post['test_mode'] : true;
            
            if (empty($secret_key)) {
                $json['error'] = $this->language->get('error_secret_key_required');
            } else {
                try {
                    $this->load->library('paystack');
                    $paystack = new \Paystack($secret_key, '', $test_mode);
                    
                    // Test connection by fetching banks
                    $result = $paystack->getBanks('NG', false, 1);
                    
                    if (isset($result['status']) && $result['status']) {
                        $json['success'] = $this->language->get('text_connection_success');
                    } else {
                        $json['error'] = $this->language->get('error_connection_failed');
                    }
                } catch (Exception $e) {
                    $json['error'] = $e->getMessage();
                }
            }
        } else {
            $json['error'] = $this->language->get('error_invalid_request');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Validate form data
     */
    protected function validate(): bool {
        if (!$this->user->hasPermission('modify', 'extension/paystack/payment/paystack')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        // Test mode validation
        if ($this->request->post['payment_paystack_test_mode']) {
            if (empty($this->request->post['payment_paystack_test_secret_key'])) {
                $this->error['test_secret_key'] = $this->language->get('error_test_secret_key');
            }
            
            if (empty($this->request->post['payment_paystack_test_public_key'])) {
                $this->error['test_public_key'] = $this->language->get('error_test_public_key');
            }
        } else {
            // Live mode validation
            if (empty($this->request->post['payment_paystack_live_secret_key'])) {
                $this->error['live_secret_key'] = $this->language->get('error_live_secret_key');
            }
            
            if (empty($this->request->post['payment_paystack_live_public_key'])) {
                $this->error['live_public_key'] = $this->language->get('error_live_public_key');
            }
        }
        
        return !$this->error;
    }
}
