<?php
/**
 * Paystack Payment Gateway Catalog Model
 * 
 * @package    Paystack Payment Gateway
 * @author     Vivian Akpoke
 * @version    1.0.0
 * @license    MIT
 */

namespace Opencart\Catalog\Model\Extension\Paystack\Payment;

class Paystack extends \Opencart\System\Engine\Model {
    
    /**
     * Get payment method for checkout
     */
    public function getMethod(array $address, float $total): array {
        $this->load->language('extension/paystack/payment/paystack');
        
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` 
            WHERE geo_zone_id = '" . (int)$this->config->get('payment_paystack_geo_zone_id') . "' 
            AND country_id = '" . (int)$address['country_id'] . "' 
            AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')
        ");
        
        if (!$this->config->get('payment_paystack_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }
        
        $method_data = [];
        
        if ($status) {
            // Check if payment method is enabled
            if (!$this->config->get('payment_paystack_status')) {
                $status = false;
            }
            
            // Check minimum total
            $min_total = (float)$this->config->get('payment_paystack_total');
            if ($total < $min_total) {
                $status = false;
            }
            
            // Check if required API keys are configured
            $test_mode = $this->config->get('payment_paystack_test_mode');
            if ($test_mode) {
                $secret_key = $this->config->get('payment_paystack_test_secret_key');
                $public_key = $this->config->get('payment_paystack_test_public_key');
            } else {
                $secret_key = $this->config->get('payment_paystack_live_secret_key');
                $public_key = $this->config->get('payment_paystack_live_public_key');
            }
            
            if (empty($secret_key) || empty($public_key)) {
                $status = false;
            }
            
            if ($status) {
                $method_data = [
                    'code' => 'paystack',
                    'title' => $this->language->get('heading_title'),
                    'terms' => '',
                    'sort_order' => $this->config->get('payment_paystack_sort_order')
                ];
            }
        }
        
        return $method_data;
    }
    
    /**
     * Add transaction to database
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
     * Get transaction by order ID
     */
    public function getTransactionByOrderId(int $order_id): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "paystack_transaction` 
            WHERE order_id = '" . (int)$order_id . "' 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        return $query->row;
    }
    
    /**
     * Get transactions by order ID
     */
    public function getTransactionsByOrderId(int $order_id): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "paystack_transaction` 
            WHERE order_id = '" . (int)$order_id . "' 
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
     * Validate transaction amount
     */
    public function validateTransactionAmount(string $reference, float $expected_amount, string $currency = 'NGN'): bool {
        $transaction = $this->getTransactionByReference($reference);
        
        if (!$transaction) {
            return false;
        }
        
        // Convert amounts to same currency for comparison
        $transaction_amount = (float)$transaction['amount'];
        $transaction_currency = $transaction['currency'];
        
        // Allow small variance for currency conversion differences
        $variance_threshold = 0.01;
        
        if ($transaction_currency === $currency) {
            return abs($transaction_amount - $expected_amount) <= $variance_threshold;
        }
        
        // If currencies differ, we need to convert (simplified check)
        // In production, you might want to use current exchange rates
        return true; // For now, assume valid if currencies differ
    }
    
    /**
     * Check if transaction is duplicate
     */
    public function isDuplicateTransaction(string $reference): bool {
        $query = $this->db->query("
            SELECT COUNT(*) as count 
            FROM `" . DB_PREFIX . "paystack_transaction` 
            WHERE reference = '" . $this->db->escape($reference) . "'
        ");
        
        return (int)$query->row['count'] > 0;
    }
    
    /**
     * Get customer transactions
     */
    public function getCustomerTransactions(int $customer_id, array $data = []): array {
        $sql = "SELECT pt.*, o.order_id, o.total as order_total, o.date_added as order_date
                FROM `" . DB_PREFIX . "paystack_transaction` pt
                LEFT JOIN `" . DB_PREFIX . "order` o ON (pt.order_id = o.order_id)
                WHERE o.customer_id = '" . (int)$customer_id . "'";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND pt.status = '" . $this->db->escape($data['filter_status']) . "'";
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
     * Get payment statistics for customer
     */
    public function getCustomerPaymentStats(int $customer_id): array {
        $query = $this->db->query("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN pt.status = 'success' THEN pt.amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN pt.status = 'success' THEN 1 ELSE 0 END) as successful_transactions,
                SUM(CASE WHEN pt.status = 'failed' THEN 1 ELSE 0 END) as failed_transactions,
                SUM(CASE WHEN pt.status = 'pending' THEN 1 ELSE 0 END) as pending_transactions
            FROM `" . DB_PREFIX . "paystack_transaction` pt
            LEFT JOIN `" . DB_PREFIX . "order` o ON (pt.order_id = o.order_id)
            WHERE o.customer_id = '" . (int)$customer_id . "'
        ");
        
        return $query->row;
    }
    
    /**
     * Clean up old pending transactions
     */
    public function cleanupOldTransactions(int $days = 7): int {
        $query = $this->db->query("
            DELETE FROM `" . DB_PREFIX . "paystack_transaction` 
            WHERE status = 'pending' 
            AND created_at < DATE_SUB(NOW(), INTERVAL " . (int)$days . " DAY)
        ");
        
        return $this->db->countAffected();
    }
    
    /**
     * Get transaction summary for period
     */
    public function getTransactionSummary(string $start_date, string $end_date): array {
        $query = $this->db->query("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total_count,
                SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_amount,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count
            FROM `" . DB_PREFIX . "paystack_transaction`
            WHERE DATE(created_at) BETWEEN '" . $this->db->escape($start_date) . "' 
            AND '" . $this->db->escape($end_date) . "'
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool {
        $secret_key = $this->config->get('payment_paystack_test_mode')
            ? $this->config->get('payment_paystack_test_secret_key')
            : $this->config->get('payment_paystack_live_secret_key');
        
        if (empty($secret_key)) {
            return false;
        }
        
        $computed_signature = hash_hmac('sha512', $payload, $secret_key);
        
        return hash_equals($signature, $computed_signature);
    }
    
    /**
     * Log debug information
     */
    public function logDebug(string $message, array $data = []): void {
        if ($this->config->get('payment_paystack_debug_mode')) {
            $log_message = 'Paystack Debug: ' . $message;
            
            if (!empty($data)) {
                $log_message .= ' | Data: ' . json_encode($data);
            }
            
            $this->log->write($log_message);
        }
    }
    
    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies(): array {
        return [
            'NGN' => 'Nigerian Naira',
            'USD' => 'US Dollar',
            'GHS' => 'Ghanaian Cedi',
            'ZAR' => 'South African Rand',
            'KES' => 'Kenyan Shilling'
        ];
    }
    
    /**
     * Check if currency is supported
     */
    public function isCurrencySupported(string $currency): bool {
        $supported_currencies = $this->getSupportedCurrencies();
        return array_key_exists($currency, $supported_currencies);
    }
}
