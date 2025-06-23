# 🔐 Phân Tích Bảo Mật Plugin AFF Pro

## 🚨 Các Vấn Đề Bảo Mật Nghiêm Trọng

### 1. Hardcoded Domain Check (CRITICAL)
**File:** `includes/class-query.php` - Lines 201-203
```php
$r = $_SERVER['HTTP_HOST'];
if ( !strpos( $r, 'van' ) )
    return;
```

**Vấn đề:**
- Plugin chỉ hoạt động trên domain chứa từ "van"
- Có thể bypass bằng cách modify hostname
- Tạo backdoor tiềm ẩn cho developer
- Không phù hợp với plugin thương mại

**Tác động:**
- Plugin không hoạt động trên production domain
- Khách hàng không thể sử dụng plugin sau khi mua
- Có thể gây lỗi "không được phép truy cập"

**Giải pháp:**
```php
// XÓA HOÀN TOÀN đoạn code này
// Thay thế bằng proper license validation
if (!$this->is_license_valid()) {
    return false;
}
```

### 2. SQL Injection Vulnerabilities

#### 2.1 Unsafe Query Building
**File:** `includes/class-query.php`
```php
// UNSAFE: Direct string concatenation
$param2 = is_array( $param2 ) ? ('("' . implode( '","', $param2 ) . '")') : ...

// SAFE: Should use wpdb->prepare()
$param2 = is_array($param2) ? 
    '(' . implode(',', array_fill(0, count($param2), '%s')) . ')' : 
    '%s';
```

#### 2.2 Missing Input Sanitization
**File:** `admin/ajax-admin.php`
```php
// UNSAFE: Direct use of $_POST data
$user_id = $_POST['user_id']; // No sanitization
$role_slug = $_POST['role_slug']; // No validation

// SAFE: Should sanitize inputs
$user_id = intval($_POST['user_id'] ?? 0);
$role_slug = sanitize_text_field($_POST['role_slug'] ?? '');
```

### 3. Authentication & Authorization Issues

#### 3.1 Weak Capability Checks
**File:** `admin/class-aff-pro-admin.php`
```php
// WEAK: Only checks user role
$u_role = $user->roles[0];
if ( $u_role == 'administrator' ) {
    // add menu
}

// STRONG: Should check capabilities
if ( current_user_can( 'manage_options' ) ) {
    // add menu
}
```

#### 3.2 Missing Nonce Verification
**File:** `admin/ajax-admin.php`
```php
// MISSING: No nonce verification in most AJAX handlers
public function update_user_role() {
    // Should verify nonce first
    if (!wp_verify_nonce($_POST['nonce'], 'aff_update_user_role')) {
        wp_die('Security check failed');
    }
    // ... rest of code
}
```

### 4. Cross-Site Scripting (XSS) Vulnerabilities

#### 4.1 Unescaped Output
**File:** `admin/partials/aff-pro-admin-display.php`
```php
// UNSAFE: Direct output without escaping
echo $user_data['display_name']; // Potential XSS

// SAFE: Escape output
echo esc_html($user_data['display_name']);
```

#### 4.2 JavaScript Injection
**File:** `admin/class-aff-pro-admin.php`
```php
// UNSAFE: Direct JavaScript output
echo "<script>var orderId = {$post->ID};</script>";

// SAFE: Use wp_localize_script()
wp_localize_script('aff-admin', 'affData', [
    'orderId' => intval($post->ID),
    'nonce' => wp_create_nonce('aff_admin_nonce')
]);
```

## 🛡️ Cơ Chế Bảo Mật Hiện Tại

### 1. License Management
```php
class AFF_Pro_License_Manager {
    // ✅ GOOD: Proper error handling
    public function __construct() {
        try {
            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            
            $plugin_file = AFF_PATH . 'aff-pro.php';
            if (file_exists($plugin_file) && is_readable($plugin_file)) {
                $this->plugin_data = get_plugin_data($plugin_file);
            }
        } catch (Exception $e) {
            error_log('AFF Pro License Manager Error: ' . $e->getMessage());
        }
    }
    
    // ⚠️ IMPROVEMENT NEEDED: Add rate limiting
    private function validate_license($license_key) {
        // Should add rate limiting to prevent brute force
        $attempts = get_transient('aff_license_attempts_' . get_current_user_id());
        if ($attempts && $attempts > 5) {
            return false; // Too many attempts
        }
        
        // ... validation logic
    }
}
```

### 2. Database Security
```php
// ✅ GOOD: Using wpdb->prepare()
$query = $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}affpro_user_order WHERE user_id = %d AND status = %d",
    $user_id,
    $status
);

// ⚠️ IMPROVEMENT NEEDED: Add input validation
public function where($column, $param1 = null, $param2 = null, $joint = 'and') {
    // Validate column name
    if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $column)) {
        throw new InvalidArgumentException('Invalid column name');
    }
    
    // Validate joint type
    if (!in_array(strtolower($joint), ['and', 'or', 'where'])) {
        throw new InvalidArgumentException('Invalid joint type');
    }
    
    // ... rest of logic
}
```

## 🔒 Khuyến Nghị Bảo Mật

### 1. Immediate Security Fixes

#### Fix 1: Remove Hardcoded Domain Check
```php
// File: includes/class-query.php
// DELETE lines 201-203 completely
// Replace with proper license validation if needed
```

#### Fix 2: Add Nonce Verification
```php
// File: admin/ajax-admin.php
class AFF_Ajax_Admin {
    public function __construct() {
        // Add nonce verification to all AJAX handlers
        add_action('wp_ajax_aff_get_users', [$this, 'verify_nonce_and_get_users']);
    }
    
    private function verify_nonce($action) {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', $action)) {
            wp_send_json_error('Security check failed');
            wp_die();
        }
    }
    
    public function verify_nonce_and_get_users() {
        $this->verify_nonce('aff_get_users');
        $this->get_users();
    }
}
```

#### Fix 3: Sanitize All Inputs
```php
// File: admin/ajax-admin.php
public function update_user_role() {
    // Verify nonce
    $this->verify_nonce('aff_update_user_role');
    
    // Sanitize inputs
    $user_id = intval($_POST['user_id'] ?? 0);
    $role_slug = sanitize_text_field($_POST['role_slug'] ?? '');
    
    // Validate inputs
    if ($user_id <= 0) {
        wp_send_json_error('Invalid user ID');
    }
    
    $allowed_roles = ['ctv', 'subscriber', 'contributor'];
    if (!in_array($role_slug, $allowed_roles)) {
        wp_send_json_error('Invalid role');
    }
    
    // ... rest of logic
}
```

### 2. Enhanced Security Measures

#### 2.1 Rate Limiting
```php
class AFF_Rate_Limiter {
    public static function check_rate_limit($action, $limit = 10, $window = 3600) {
        $user_id = get_current_user_id();
        $key = "aff_rate_limit_{$action}_{$user_id}";
        
        $attempts = get_transient($key) ?: 0;
        
        if ($attempts >= $limit) {
            return false; // Rate limit exceeded
        }
        
        set_transient($key, $attempts + 1, $window);
        return true;
    }
}
```

#### 2.2 Activity Logging
```php
class AFF_Security_Logger {
    public static function log_activity($action, $details = []) {
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'ip_address' => self::get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'action' => $action,
            'details' => $details
        ];
        
        // Log to database or file
        error_log('AFF Pro Activity: ' . json_encode($log_entry));
    }
    
    private static function get_client_ip() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
```

#### 2.3 Data Encryption
```php
class AFF_Encryption {
    private static function get_encryption_key() {
        $key = get_option('aff_encryption_key');
        if (!$key) {
            $key = wp_generate_password(32, false);
            update_option('aff_encryption_key', $key);
        }
        return $key;
    }
    
    public static function encrypt($data) {
        $key = self::get_encryption_key();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    public static function decrypt($encrypted_data) {
        $key = self::get_encryption_key();
        $data = base64_decode($encrypted_data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
}
```

### 3. Security Headers
```php
class AFF_Security_Headers {
    public static function init() {
        add_action('send_headers', [__CLASS__, 'add_security_headers']);
    }
    
    public static function add_security_headers() {
        if (is_admin() && strpos($_SERVER['REQUEST_URI'], 'aff-pro') !== false) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            
            if (is_ssl()) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }
        }
    }
}
```

## 🧪 Security Testing Checklist

### 1. Input Validation Tests
- [ ] Test SQL injection in all form fields
- [ ] Test XSS in text inputs and outputs
- [ ] Test file upload vulnerabilities
- [ ] Test parameter tampering

### 2. Authentication Tests
- [ ] Test privilege escalation
- [ ] Test session management
- [ ] Test password policies
- [ ] Test account lockout mechanisms

### 3. Authorization Tests
- [ ] Test access control bypass
- [ ] Test direct object references
- [ ] Test function-level access control
- [ ] Test data exposure

### 4. Business Logic Tests
- [ ] Test commission calculation manipulation
- [ ] Test payment processing vulnerabilities
- [ ] Test affiliate link manipulation
- [ ] Test referral fraud prevention

## 📋 Security Implementation Plan

### Phase 1: Critical Fixes (Week 1)
1. **Remove hardcoded domain check**
2. **Add nonce verification to all AJAX**
3. **Sanitize all user inputs**
4. **Fix SQL injection vulnerabilities**

### Phase 2: Enhanced Security (Week 2)
1. **Implement rate limiting**
2. **Add activity logging**
3. **Enhance capability checks**
4. **Add security headers**

### Phase 3: Advanced Security (Week 3)
1. **Implement data encryption**
2. **Add intrusion detection**
3. **Security audit logging**
4. **Penetration testing**

## 🔍 Security Monitoring

### 1. Real-time Monitoring
```php
class AFF_Security_Monitor {
    public static function init() {
        add_action('wp_login_failed', [__CLASS__, 'log_failed_login']);
        add_action('wp_login', [__CLASS__, 'log_successful_login']);
        add_filter('authenticate', [__CLASS__, 'check_brute_force'], 30, 3);
    }
    
    public static function check_brute_force($user, $username, $password) {
        $ip = AFF_Security_Logger::get_client_ip();
        $attempts = get_transient("aff_login_attempts_{$ip}") ?: 0;
        
        if ($attempts >= 5) {
            return new WP_Error('too_many_attempts', 
                'Too many login attempts. Please try again later.');
        }
        
        return $user;
    }
}
```

### 2. Security Alerts
```php
class AFF_Security_Alerts {
    public static function send_security_alert($type, $details) {
        $admin_email = get_option('admin_email');
        $subject = "AFF Pro Security Alert: {$type}";
        
        $message = "Security incident detected:\n\n";
        $message .= "Type: {$type}\n";
        $message .= "Time: " . current_time('mysql') . "\n";
        $message .= "Details: " . print_r($details, true);
        
        wp_mail($admin_email, $subject, $message);
    }
}
```

---

*Phân tích bảo mật chi tiết - Created: 2025-06-23*
*Priority: CRITICAL - Cần fix ngay lập tức*