<?php
/**
 * Paystack API Integration Library
 * 
 * @package    Paystack Payment Gateway
 * @author     Vivian Akpoke
 * @version    1.0.0
 * @license    MIT
 */

class Paystack {
    private $secret_key;
    private $public_key;
    private $base_url;
    private $timeout;
    private $verify_ssl;
    
    const LIVE_URL = 'https://api.paystack.co';
    const TEST_URL = 'https://api.paystack.co';
    
    /**
     * Constructor
     *
     * @param string $secret_key Paystack secret key
     * @param string $public_key Paystack public key
     * @param bool $test_mode Whether to use test mode
     */
    public function __construct($secret_key, $public_key = '', $test_mode = true) {
        $this->secret_key = $secret_key;
        $this->public_key = $public_key;
        $this->base_url = $test_mode ? self::TEST_URL : self::LIVE_URL;
        $this->timeout = 30;
        $this->verify_ssl = true;
    }
    
    /**
     * Initialize a transaction
     *
     * @param array $data Transaction data
     * @return array API response
     */
    public function initializeTransaction($data) {
        $required_fields = ['email', 'amount'];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        // Convert amount to kobo (multiply by 100)
        $data['amount'] = (int)($data['amount'] * 100);
        
        return $this->makeRequest('POST', '/transaction/initialize', $data);
    }
    
    /**
     * Verify a transaction
     *
     * @param string $reference Transaction reference
     * @return array API response
     */
    public function verifyTransaction($reference) {
        if (empty($reference)) {
            throw new Exception("Transaction reference is required");
        }
        
        return $this->makeRequest('GET', "/transaction/verify/{$reference}");
    }
    
    /**
     * List transactions
     *
     * @param array $params Query parameters
     * @return array API response
     */
    public function listTransactions($params = []) {
        $query_string = !empty($params) ? '?' . http_build_query($params) : '';
        return $this->makeRequest('GET', "/transaction{$query_string}");
    }
    
    /**
     * Process a refund
     *
     * @param string $transaction_reference Transaction reference
     * @param int $amount Amount to refund in kobo
     * @param string $currency Currency code
     * @param string $customer_note Note for customer
     * @param string $merchant_note Note for merchant
     * @return array API response
     */
    public function processRefund($transaction_reference, $amount = null, $currency = 'NGN', $customer_note = '', $merchant_note = '') {
        if (empty($transaction_reference)) {
            throw new Exception("Transaction reference is required");
        }
        
        $data = [
            'transaction' => $transaction_reference,
            'currency' => $currency
        ];
        
        if ($amount !== null) {
            $data['amount'] = (int)($amount * 100); // Convert to kobo
        }
        
        if (!empty($customer_note)) {
            $data['customer_note'] = $customer_note;
        }
        
        if (!empty($merchant_note)) {
            $data['merchant_note'] = $merchant_note;
        }
        
        return $this->makeRequest('POST', '/refund', $data);
    }
    
    /**
     * Validate webhook signature
     *
     * @param string $payload Raw POST data
     * @param string $signature X-Paystack-Signature header
     * @return bool Whether signature is valid
     */
    public function validateWebhook($payload, $signature) {
        if (empty($payload) || empty($signature)) {
            return false;
        }
        
        $computed_signature = hash_hmac('sha512', $payload, $this->secret_key);
        return hash_equals($signature, $computed_signature);
    }
    
    /**
     * Get supported banks
     *
     * @param string $country Country code (NG, GH, ZA, KE)
     * @param bool $use_cursor Whether to use cursor pagination
     * @param int $per_page Number of banks per page
     * @return array API response
     */
    public function getBanks($country = 'NG', $use_cursor = false, $per_page = 50) {
        $params = [
            'country' => $country,
            'use_cursor' => $use_cursor ? 'true' : 'false',
            'perPage' => $per_page
        ];
        
        $query_string = http_build_query($params);
        return $this->makeRequest('GET', "/bank?{$query_string}");
    }
    
    /**
     * Resolve account number
     *
     * @param string $account_number Account number
     * @param string $bank_code Bank code
     * @return array API response
     */
    public function resolveAccountNumber($account_number, $bank_code) {
        if (empty($account_number) || empty($bank_code)) {
            throw new Exception("Account number and bank code are required");
        }
        
        $params = [
            'account_number' => $account_number,
            'bank_code' => $bank_code
        ];
        
        $query_string = http_build_query($params);
        return $this->makeRequest('GET', "/bank/resolve?{$query_string}");
    }
    
    /**
     * Create a customer
     *
     * @param array $data Customer data
     * @return array API response
     */
    public function createCustomer($data) {
        $required_fields = ['email'];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        return $this->makeRequest('POST', '/customer', $data);
    }
    
    /**
     * Get customer by ID or email
     *
     * @param string $identifier Customer ID or email
     * @return array API response
     */
    public function getCustomer($identifier) {
        if (empty($identifier)) {
            throw new Exception("Customer identifier is required");
        }
        
        return $this->makeRequest('GET', "/customer/{$identifier}");
    }
    
    /**
     * Make HTTP request to Paystack API
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return array API response
     * @throws Exception On API errors
     */
    private function makeRequest($method, $endpoint, $data = []) {
        $url = $this->base_url . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->secret_key,
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: OpenCart-Paystack/1.0.0'
        ];
        
        $curl = curl_init();
        
        $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => $this->verify_ssl,
            CURLOPT_SSL_VERIFYHOST => $this->verify_ssl ? 2 : 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ];
        
        switch (strtoupper($method)) {
            case 'POST':
                $curl_options[CURLOPT_POST] = true;
                if (!empty($data)) {
                    $curl_options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
                
            case 'PUT':
                $curl_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if (!empty($data)) {
                    $curl_options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
                
            case 'DELETE':
                $curl_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
                
            case 'GET':
            default:
                // GET is default, no additional options needed
                break;
        }
        
        curl_setopt_array($curl, $curl_options);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($response === false || !empty($error)) {
            throw new Exception("cURL Error: {$error}");
        }
        
        $decoded_response = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from Paystack API");
        }
        
        // Handle HTTP errors
        if ($http_code >= 400) {
            $error_message = isset($decoded_response['message']) 
                ? $decoded_response['message'] 
                : "HTTP Error {$http_code}";
            throw new Exception($error_message, $http_code);
        }
        
        return $decoded_response;
    }
    
    /**
     * Generate a unique transaction reference
     *
     * @param string $prefix Optional prefix
     * @return string Unique reference
     */
    public static function generateReference($prefix = 'OC') {
        return $prefix . '_' . time() . '_' . uniqid();
    }
    
    /**
     * Convert amount from kobo to naira
     *
     * @param int $amount_in_kobo Amount in kobo
     * @return float Amount in naira
     */
    public static function koboToNaira($amount_in_kobo) {
        return (float)($amount_in_kobo / 100);
    }
    
    /**
     * Convert amount from naira to kobo
     *
     * @param float $amount_in_naira Amount in naira
     * @return int Amount in kobo
     */
    public static function nairaToKobo($amount_in_naira) {
        return (int)($amount_in_naira * 100);
    }
    
    /**
     * Validate email address
     *
     * @param string $email Email address
     * @return bool Whether email is valid
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Sanitize callback URL
     *
     * @param string $url URL to sanitize
     * @return string Sanitized URL
     */
    public static function sanitizeCallbackUrl($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}
