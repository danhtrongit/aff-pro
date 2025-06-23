# 🚨 URGENT: Security Fix Required

## ⚠️ Critical Security Issue

### Vấn Đề Phát Hiện
Trong file `includes/class-query.php`, dòng 201-203:

```php
$r = $_SERVER['HTTP_HOST'];
if ( !strpos( $r, 'van' ) )
    return;
```

### Tại Sao Đây Là Vấn Đề Nghiêm Trọng?

1. **Hardcoded Domain Check**: Plugin chỉ hoạt động trên domain chứa từ "van"
2. **Bypass Security**: Có thể bypass bằng cách thay đổi hostname
3. **Unprofessional**: Không phù hợp với plugin thương mại
4. **Maintenance Issue**: Khó maintain và debug

### Tác Động
- Plugin không hoạt động trên domain khác
- Có thể gây lỗi "không được phép truy cập"
- Tạo backdoor tiềm ẩn

## 🔧 Giải Pháp Khuyến Nghị

### Option 1: Loại Bỏ Hoàn Toàn (Khuyến nghị)
```php
// XÓA HOÀN TOÀN các dòng 201-203
// $r = $_SERVER['HTTP_HOST'];
// if ( !strpos( $r, 'van' ) )
//     return;
```

### Option 2: Thay Thế Bằng License Check
```php
// Thay thế bằng proper license validation
if ( ! $this->is_license_valid() ) {
    return;
}

private function is_license_valid() {
    // Implement proper license checking logic
    $license_status = get_option('aff_pro_license_status');
    return $license_status === 'valid';
}
```

### Option 3: Environment Check (Nếu cần)
```php
// Nếu thực sự cần check environment
if ( ! $this->is_valid_environment() ) {
    return;
}

private function is_valid_environment() {
    // Check based on license, not hardcoded domain
    return apply_filters('aff_pro_valid_environment', true);
}
```

## 🚀 Implementation Steps

### Step 1: Backup
```bash
cp includes/class-query.php includes/class-query.php.backup
```

### Step 2: Remove Malicious Code
```php
// Tìm và xóa dòng 201-203 trong method where()
```

### Step 3: Test
- Test plugin functionality
- Verify admin access works
- Check all features

### Step 4: Commit Changes
```bash
git add includes/class-query.php
git commit -m "SECURITY: Remove hardcoded domain check in MH_Query"
```

## 🧪 Testing Checklist

- [ ] Plugin activates successfully
- [ ] Admin pages accessible
- [ ] Database queries work
- [ ] No PHP errors
- [ ] All features functional

## 📋 Additional Security Improvements

### 1. Input Validation
```php
public function where($column, $param1 = null, $param2 = null, $joint = 'and') {
    // Add input validation
    if (!is_string($column) || empty($column)) {
        throw new InvalidArgumentException('Column must be a non-empty string');
    }
    
    if (!in_array(strtolower($joint), ['and', 'or', 'where'])) {
        throw new InvalidArgumentException('Invalid joint type');
    }
    
    // Continue with existing logic...
}
```

### 2. Sanitization
```php
private function sanitize_column_name($column) {
    // Remove dangerous characters
    $column = preg_replace('/[^a-zA-Z0-9_.]/', '', $column);
    return $column;
}
```

### 3. Prepared Statements
```php
// Ensure all queries use prepared statements
$query = $wpdb->prepare("SELECT * FROM {$table} WHERE {$column} = %s", $value);
```

## 🔍 Code Review Recommendations

### Before Deployment
1. **Security Audit**: Review all user inputs
2. **SQL Injection Check**: Verify all queries are prepared
3. **XSS Prevention**: Ensure all outputs are escaped
4. **CSRF Protection**: Add nonces to forms

### Ongoing Monitoring
1. **Error Logging**: Monitor for suspicious activities
2. **Performance Monitoring**: Track query performance
3. **Security Scanning**: Regular security scans
4. **Code Quality**: Maintain coding standards

---

**Priority**: 🔴 CRITICAL
**Estimated Time**: 30 minutes
**Risk Level**: HIGH
**Impact**: Plugin functionality và security