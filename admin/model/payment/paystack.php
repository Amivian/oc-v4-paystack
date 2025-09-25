<?php
/**
 * Paystack Payment Gateway Admin Model
 * 
 * @package    Paystack Payment Gateway
 * @author     Vivian Akpoke
 * @version    1.0.0
 * @license    MIT
 */

namespace Opencart\Admin\Model\Extension\Paystack\Payment;

class Paystack extends \Opencart\System\Engine\Model {
    
    /**
     * Install the extension
     */
    public function install(): void {
        // Create database tables
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paystack_transaction` (
                `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
                `order_id` int(11) NOT NULL,
                `reference` varchar(255) NOT NULL,
                `access_code` varchar(255) DEFAULT NULL,
                `amount` decimal(15,4) NOT NULL,
                `currency` varchar(3) NOT NULL,
                `status` varchar(50) NOT NULL DEFAULT 'pending',
                `gateway_response` text,
                `customer_email` varchar(255) DEFAULT NULL,
                `customer_name` varchar(255) DEFAULT NULL,
                `payment_method` varchar(50) DEFAULT NULL,
                `authorization_code` varchar(255) DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`transaction_id`),
                UNIQUE KEY `reference` (`reference`),
                KEY `order_id` (`order_id`),
                KEY `status` (`status`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paystack_webhook_log` (
                `log_id` int(11) NOT NULL AUTO_INCREMENT,
                `event_type` varchar(100) NOT NULL,
                `reference` varchar(255) NOT NULL,
                `payload` text NOT NULL,
                `signature` varchar(255) NOT NULL,
                `verified` tinyint(1) DEFAULT 0,
                `processed` tinyint(1) DEFAULT 0,
                `error_message` text DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`log_id`),
                KEY `reference` (`reference`),
                KEY `event_type` (`event_type`),
                KEY `verified` (`verified`),
                KEY `processed` (`processed`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "paystack_refund` (
                `refund_id` int(11) NOT NULL AUTO_INCREMENT,
                `transaction_id` int(11) NOT NULL,
                `order_id` int(11) NOT NULL,
                `refund_reference` varchar(255) NOT NULL,
                `amount` decimal(15,4) NOT NULL,
                `currency` varchar(3) NOT NULL,
                `status` varchar(50) NOT NULL DEFAULT 'pending',
                `gateway_response` text,
                `reason` varchar(255) DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`refund_id`),
                UNIQUE KEY `refund_reference` (`refund_reference`),
                KEY `transaction_id` (`transaction_id`),
                KEY `order_id` (`order_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Set default configuration
        $this->load->model('setting/setting');
        
        $default_settings = [
            'payment_paystack_test_mode' => '1',
            'payment_paystack_test_secret_key' => '',
            'payment_paystack_test_public_key' => '',
            'payment_paystack_live_secret_key' => '',
            'payment_paystack_live_public_key' => '',
            'payment_paystack_webhook_url' => '',
            'payment_paystack_payment_methods' => ['card', 'bank', 'ussd'],
            'payment_paystack_transaction_fee' => '0',
            'payment_paystack_fee_bearer' => 'account',
            'payment_paystack_custom_fields' => '[]',
            'payment_paystack_success_message' => 'Payment successful! Thank you for your order.',
            'payment_paystack_failed_order_status_id' => '10',
            'payment_paystack_pending_order_status_id' => '1',
            'payment_paystack_completed_order_status_id' => '5',
            'payment_paystack_refunded_order_status_id' => '11',
            'payment_paystack_total' => '0.01',
            'payment_paystack_geo_zone_id' => '0',
            'payment_paystack_status' => '0',
            'payment_paystack_sort_order' => '1',
            'payment_paystack_debug_mode' => '1'
        ];
        
        $this->model_setting_setting->editSetting('payment_paystack', $default_settings);
    }
    
    /**
     * Uninstall the extension
     */
    public function uninstall(): void {
        // Drop tables
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paystack_refund`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paystack_webhook_log`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "paystack_transaction`");
        
        // Remove settings
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'payment_paystack'");
    }
    
    /**
     * Get transactions with filtering
     */
    public function getTransactions(array $data = []): array {
        $sql = "SELECT pt.*, o.firstname, o.lastname 
                FROM `" . DB_PREFIX . "paystack_transaction` pt 
                LEFT JOIN `" . DB_PREFIX . "order` o ON (pt.order_id = o.order_id) 
                WHERE 1=1";
        
        if (!empty($data['filter_reference'])) {
            $sql .= " AND pt.reference LIKE '" . $this->db->escape($data['filter_reference']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND pt.status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND pt.order_id = '" . (int)$data['filter_order_id'] . "'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(pt.created_at) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(pt.created_at) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $sql .= " ORDER BY pt.created_at DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get total transactions count
     */
    public function getTotalTransactions(array $data = []): int {
        $sql = "SELECT COUNT(*) AS total 
                FROM `" . DB_PREFIX . "paystack_transaction` pt 
                WHERE 1=1";
        
        if (!empty($data['filter_reference'])) {
            $sql .= " AND pt.reference LIKE '" . $this->db->escape($data['filter_reference']) . "%'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND pt.status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND pt.order_id = '" . (int)$data['filter_order_id'] . "'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(pt.created_at) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(pt.created_at) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $query = $this->db->query($sql);
        
        return (int)$query->row['total'];
    }
    
    /**
     * Get single transaction
     */
    public function getTransaction(int $transaction_id): array {
        $query = $this->db->query("
            SELECT pt.*, o.firstname, o.lastname, o.email, o.telephone 
            FROM `" . DB_PREFIX . "paystack_transaction` pt 
            LEFT JOIN `" . DB_PREFIX . "order` o ON (pt.order_id = o.order_id) 
            WHERE pt.transaction_id = '" . (int)$transaction_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Get transaction by reference
     */
    public function getTransactionByReference(string $reference): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "paystack_transaction` 
            WHERE reference = '" . $this->db->escape($reference) . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Add transaction
     */
    public function addTransaction(array $data): int {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "paystack_transaction` 
            SET order_id = '" . (int)$data['order_id'] . "',
                reference = '" . $this->db->escape($data['reference']) . "',
                access_code = '" . $this->db->escape($data['access_code'] ?? '') . "',
                amount = '" . (float)$data['amount'] . "',
                currency = '" . $this->db->escape($data['currency']) . "',
                status = '" . $this->db->escape($data['status'] ?? 'pending') . "',
                gateway_response = '" . $this->db->escape($data['gateway_response'] ?? '') . "',
                customer_email = '" . $this->db->escape($data['customer_email'] ?? '') . "',
                customer_name = '" . $this->db->escape($data['customer_name'] ?? '') . "',
                payment_method = '" . $this->db->escape($data['payment_method'] ?? '') . "',
                authorization_code = '" . $this->db->escape($data['authorization_code'] ?? '') . "',
                created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Update transaction
     */
    public function updateTransaction(int $transaction_id, array $data): void {
        $sql = "UPDATE `" . DB_PREFIX . "paystack_transaction` SET ";
        $updates = [];
        
        if (isset($data['status'])) {
            $updates[] = "status = '" . $this->db->escape($data['status']) . "'";
        }
        
        if (isset($data['gateway_response'])) {
            $updates[] = "gateway_response = '" . $this->db->escape($data['gateway_response']) . "'";
        }
        
        if (isset($data['payment_method'])) {
            $updates[] = "payment_method = '" . $this->db->escape($data['payment_method']) . "'";
        }
        
        if (isset($data['authorization_code'])) {
            $updates[] = "authorization_code = '" . $this->db->escape($data['authorization_code']) . "'";
        }
        
        if (!empty($updates)) {
            $updates[] = "updated_at = NOW()";
            $sql .= implode(', ', $updates);
            $sql .= " WHERE transaction_id = '" . (int)$transaction_id . "'";
            
            $this->db->query($sql);
        }
    }
    
    /**
     * Process refund
     */
    public function processRefund(int $transaction_id, float $amount = null, string $reason = ''): array {
        $transaction = $this->getTransaction($transaction_id);
        
        if (!$transaction) {
            return [
                'status' => false,
                'message' => 'Transaction not found'
            ];
        }
        
        if ($transaction['status'] !== 'success') {
            return [
                'status' => false,
                'message' => 'Can only refund successful transactions'
            ];
        }
        
        try {
            // Load Paystack library
            $this->load->library('paystack');
            
            $test_mode = $this->config->get('payment_paystack_test_mode');
            $secret_key = $test_mode 
                ? $this->config->get('payment_paystack_test_secret_key')
                : $this->config->get('payment_paystack_live_secret_key');
            
            $paystack = new \Paystack($secret_key, '', $test_mode);
            
            // Process refund
            $refund_amount = $amount ?? $transaction['amount'];
            $result = $paystack->processRefund(
                $transaction['reference'],
                $refund_amount,
                $transaction['currency'],
                $reason,
                'Refund processed from admin panel'
            );
            
            if (isset($result['status']) && $result['status']) {
                // Record refund
                $this->addRefund([
                    'transaction_id' => $transaction_id,
                    'order_id' => $transaction['order_id'],
                    'refund_reference' => $result['data']['transaction']['reference'] ?? uniqid('refund_'),
                    'amount' => $refund_amount,
                    'currency' => $transaction['currency'],
                    'status' => 'success',
                    'gateway_response' => json_encode($result),
                    'reason' => $reason
                ]);
                
                // Update order status if full refund
                if ($refund_amount >= $transaction['amount']) {
                    $this->load->model('sale/order');
                    $refunded_status_id = $this->config->get('payment_paystack_refunded_order_status_id');
                    
                    if ($refunded_status_id) {
                        $this->model_sale_order->addHistory(
                            $transaction['order_id'],
                            $refunded_status_id,
                            'Payment refunded via Paystack',
                            true
                        );
                    }
                }
                
                return [
                    'status' => true,
                    'message' => 'Refund processed successfully',
                    'data' => $result['data']
                ];
            } else {
                return [
                    'status' => false,
                    'message' => $result['message'] ?? 'Refund failed'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Add refund record
     */
    public function addRefund(array $data): int {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "paystack_refund` 
            SET transaction_id = '" . (int)$data['transaction_id'] . "',
                order_id = '" . (int)$data['order_id'] . "',
                refund_reference = '" . $this->db->escape($data['refund_reference']) . "',
                amount = '" . (float)$data['amount'] . "',
                currency = '" . $this->db->escape($data['currency']) . "',
                status = '" . $this->db->escape($data['status']) . "',
                gateway_response = '" . $this->db->escape($data['gateway_response'] ?? '') . "',
                reason = '" . $this->db->escape($data['reason'] ?? '') . "',
                created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Get refunds for transaction
     */
    public function getRefundsByTransaction(int $transaction_id): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "paystack_refund` 
            WHERE transaction_id = '" . (int)$transaction_id . "' 
            ORDER BY created_at DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Log webhook
     */
    public function logWebhook(array $data): int {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "paystack_webhook_log` 
            SET event_type = '" . $this->db->escape($data['event_type']) . "',
                reference = '" . $this->db->escape($data['reference']) . "',
                payload = '" . $this->db->escape($data['payload']) . "',
                signature = '" . $this->db->escape($data['signature']) . "',
                verified = '" . (int)($data['verified'] ?? 0) . "',
                processed = '" . (int)($data['processed'] ?? 0) . "',
                error_message = '" . $this->db->escape($data['error_message'] ?? '') . "',
                created_at = NOW()
        ");
        
        return $this->db->getLastId();
    }
    
    /**
     * Update webhook log
     */
    public function updateWebhookLog(int $log_id, array $data): void {
        $sql = "UPDATE `" . DB_PREFIX . "paystack_webhook_log` SET ";
        $updates = [];
        
        if (isset($data['processed'])) {
            $updates[] = "processed = '" . (int)$data['processed'] . "'";
        }
        
        if (isset($data['error_message'])) {
            $updates[] = "error_message = '" . $this->db->escape($data['error_message']) . "'";
        }
        
        if (!empty($updates)) {
            $sql .= implode(', ', $updates);
            $sql .= " WHERE log_id = '" . (int)$log_id . "'";
            
            $this->db->query($sql);
        }
    }
    
    /**
     * Get webhook logs
     */
    public function getWebhookLogs(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "paystack_webhook_log` WHERE 1=1";
        
        if (!empty($data['filter_event_type'])) {
            $sql .= " AND event_type = '" . $this->db->escape($data['filter_event_type']) . "'";
        }
        
        if (!empty($data['filter_reference'])) {
            $sql .= " AND reference LIKE '" . $this->db->escape($data['filter_reference']) . "%'";
        }
        
        if (isset($data['filter_verified'])) {
            $sql .= " AND verified = '" . (int)$data['filter_verified'] . "'";
        }
        
        if (isset($data['filter_processed'])) {
            $sql .= " AND processed = '" . (int)$data['filter_processed'] . "'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(array $data = []): array {
        $date_filter = '';
        
        if (!empty($data['date_start'])) {
            $date_filter .= " AND DATE(created_at) >= '" . $this->db->escape($data['date_start']) . "'";
        }
        
        if (!empty($data['date_end'])) {
            $date_filter .= " AND DATE(created_at) <= '" . $this->db->escape($data['date_end']) . "'";
        }
        
        // Total transactions
        $query = $this->db->query("
            SELECT COUNT(*) as total_count, 
                   SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_amount,
                   SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_count,
                   SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count
            FROM `" . DB_PREFIX . "paystack_transaction` 
            WHERE 1=1 {$date_filter}
        ");
        
        $stats = $query->row;
        
        // Payment methods breakdown
        $query = $this->db->query("
            SELECT payment_method, COUNT(*) as count, SUM(amount) as amount
            FROM `" . DB_PREFIX . "paystack_transaction` 
            WHERE status = 'success' {$date_filter}
            GROUP BY payment_method
        ");
        
        $stats['payment_methods'] = $query->rows;
        
        return $stats;
    }
}
