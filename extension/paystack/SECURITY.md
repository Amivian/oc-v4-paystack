# Security Policy

## Supported Versions

We actively support the following versions of the Paystack OpenCart 4 Payment Gateway:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Security Features

### Built-in Security Measures

1. **API Key Protection**
   - Secret keys are never exposed in frontend code
   - Secure storage of API credentials
   - Separate test and live environment keys

2. **Webhook Security**
   - Signature verification for all webhooks
   - HMAC-SHA512 signature validation
   - Replay attack prevention

3. **Data Validation**
   - Input sanitization on all user inputs
   - SQL injection prevention
   - XSS protection

4. **CSRF Protection**
   - Token-based CSRF protection
   - Secure form submissions
   - Request validation

5. **SSL/TLS Encryption**
   - All API communications over HTTPS
   - Secure payment processing
   - Certificate validation

6. **PCI DSS Compliance**
   - No sensitive card data stored locally
   - Secure payment tokenization
   - Compliant payment processing

### Security Best Practices

#### For Merchants

1. **API Key Management**
   ```php
   // ✅ Good: Store in environment variables
   $secret_key = getenv('PAYSTACK_SECRET_KEY');
   
   // ❌ Bad: Hardcoded in files
   $secret_key = 'sk_live_abc123...';
   ```

2. **Webhook URL Security**
   ```
   ✅ Use HTTPS: https://yourstore.com/webhook
   ❌ Avoid HTTP: http://yourstore.com/webhook
   ```

3. **Server Configuration**
   - Keep OpenCart updated
   - Use latest PHP version
   - Enable SSL certificates
   - Regular security patches

4. **Access Control**
   - Strong admin passwords
   - Two-factor authentication
   - Limited admin access
   - Regular access reviews

#### For Developers

1. **Code Security**
   ```php
   // ✅ Always validate and sanitize inputs
   $reference = $this->db->escape($this->request->post['reference']);
   
   // ✅ Use prepared statements
   $query = $this->db->query("SELECT * FROM table WHERE id = ?", [$id]);
   
   // ✅ Verify webhook signatures
   if (!$paystack->validateWebhook($payload, $signature)) {
       throw new Exception('Invalid webhook signature');
   }
   ```

2. **Error Handling**
   ```php
   // ✅ Don't expose sensitive information in errors
   try {
       $result = $paystack->verifyTransaction($reference);
   } catch (Exception $e) {
       $this->log->write('Paystack Error: ' . $e->getMessage());
       throw new Exception('Payment verification failed');
   }
   ```

3. **Logging Security**
   ```php
   // ✅ Log security events
   $this->log->write('Webhook signature verification failed for IP: ' . $this->request->server['REMOTE_ADDR']);
   
   // ❌ Don't log sensitive data
   $this->log->write('Secret key: ' . $secret_key); // Never do this
   ```

## Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability, please follow these steps:

### 1. Do Not Publicly Disclose

Please do not create public GitHub issues for security vulnerabilities. This helps protect users while we work on a fix.

### 2. Contact Us Securely

Send your security report to:
- **Email**: vivian.akpoke@example.com
- **Subject**: [SECURITY] Paystack OpenCart 4 Extension Vulnerability Report
- **GitHub Issues**: https://github.com/vivianakpoke/paystack-opencart4/issues (for non-sensitive issues)

### 3. Include These Details

Please include as much information as possible:

```
- Description of the vulnerability
- Steps to reproduce the issue
- Potential impact assessment
- Suggested fix (if available)
- Your contact information
- Whether you'd like to be credited
```

### 4. Our Response Process

1. **Acknowledgment**: We'll acknowledge receipt within 24 hours
2. **Investigation**: We'll investigate and assess the vulnerability
3. **Fix Development**: We'll develop and test a fix
4. **Disclosure**: We'll coordinate disclosure with you
5. **Release**: We'll release the security update
6. **Credit**: We'll credit you (if desired) in our security advisory

### 5. Response Timeline

- **24 hours**: Initial acknowledgment
- **72 hours**: Preliminary assessment
- **7 days**: Detailed investigation complete
- **14 days**: Fix developed and tested
- **30 days**: Security update released

## Security Checklist

### Pre-Installation Security

- [ ] Verify extension authenticity
- [ ] Check for latest version
- [ ] Review security documentation
- [ ] Ensure server meets security requirements

### Installation Security

- [ ] Use HTTPS for admin panel
- [ ] Set strong admin passwords
- [ ] Configure proper file permissions
- [ ] Enable SSL certificates
- [ ] Update OpenCart to latest version

### Configuration Security

- [ ] Use separate test/live API keys
- [ ] Configure webhook URL with HTTPS
- [ ] Enable debug mode only for testing
- [ ] Set appropriate order status mappings
- [ ] Configure proper geo-zone restrictions

### Post-Installation Security

- [ ] Test webhook signature verification
- [ ] Verify payment processing works correctly
- [ ] Monitor transaction logs
- [ ] Set up security monitoring
- [ ] Regular security updates

### Ongoing Security Maintenance

- [ ] Regular extension updates
- [ ] Monitor security advisories
- [ ] Review access logs
- [ ] Audit payment transactions
- [ ] Update API keys periodically

## Security Monitoring

### Log Monitoring

Monitor these log files for security events:

1. **System Logs**
   ```
   system/storage/logs/error.log
   ```

2. **Webhook Logs**
   ```
   Admin Panel > Paystack > Webhook Logs
   ```

3. **Server Logs**
   ```
   /var/log/apache2/error.log
   /var/log/nginx/error.log
   ```

### Security Alerts

Set up monitoring for:
- Failed webhook signature verifications
- Unusual payment patterns
- Multiple failed API requests
- Unauthorized admin access attempts
- Database connection errors

### Incident Response

If you suspect a security incident:

1. **Immediate Actions**
   - Change API keys immediately
   - Review recent transactions
   - Check webhook logs
   - Monitor for unusual activity

2. **Investigation**
   - Collect relevant logs
   - Document timeline of events
   - Assess potential impact
   - Contact developer (Vivian Akpoke) if needed

3. **Recovery**
   - Apply security patches
   - Update configurations
   - Monitor for continued threats
   - Document lessons learned

## Compliance

### PCI DSS Compliance

This extension is designed to help maintain PCI DSS compliance:

- No card data is stored locally
- All payments processed through Paystack's secure infrastructure
- Secure API communications
- Proper error handling

### Data Protection

- Customer payment data is handled securely
- Minimal data retention
- Secure data transmission
- Proper access controls

## Security Resources

### Documentation
- [Paystack Security Documentation](https://paystack.com/docs/security)
- [OpenCart Security Guide](https://docs.opencart.com/en-gb/administration/security/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)

### Tools
- [OWASP Security Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
- [SSL Labs SSL Test](https://www.ssllabs.com/ssltest/)
- [Security Headers](https://securityheaders.com/)

### Updates
- Subscribe to Paystack security advisories
- Monitor OpenCart security updates
- Follow PHP security announcements

## Contact

For security-related questions or concerns:
- **Developer**: Vivian Akpoke
- **Email**: vivian.akpoke@example.com
- **GitHub**: https://github.com/vivianakpoke/paystack-opencart4
- **Paystack Documentation**: https://paystack.com/docs/security

---

**Remember**: Security is a shared responsibility. While we provide a secure foundation, proper configuration and maintenance are essential for maintaining security in production environments.
