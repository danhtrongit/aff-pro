# 🗄️ Phân Tích Database Schema & Performance

## 📊 Tổng Quan Database Schema

Plugin AFF Pro sử dụng một hệ thống database phức tạp để quản lý affiliate marketing với cấu trúc đa cấp.

### 🏗️ Kiến Trúc Database

```sql
-- Core Tables Structure
wp_affpro_user_relationship    # Quan hệ cộng tác viên đa cấp
wp_affpro_user_order          # Đơn hàng và hoa hồng
wp_affpro_traffic             # Theo dõi traffic
wp_affpro_payment             # Quản lý thanh toán
wp_affpro_banner              # Banner affiliate
wp_affpro_config              # Cấu hình hệ thống
```

## 📋 Chi Tiết Từng Bảng

### 1. wp_affpro_user_relationship
**Mục đích:** Quản lý cấu trúc đa cấp của hệ thống affiliate

```sql
CREATE TABLE wp_affpro_user_relationship (
    id INT(11) NOT NULL AUTO_INCREMENT,
    ancestor_id INT(11) NOT NULL,      -- ID của cấp trên
    descendant_id INT(11) NOT NULL,    -- ID của cấp dưới
    distance INT(11) NOT NULL,         -- Khoảng cách cấp (0=trực tiếp, 1=cấp 2, 2=cấp 3...)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY unique_relationship (ancestor_id, descendant_id),
    KEY idx_ancestor (ancestor_id),
    KEY idx_descendant (descendant_id),
    KEY idx_distance (distance),
    KEY idx_ancestor_distance (ancestor_id, distance)
);
```

**Cách hoạt động:**
```php
// Ví dụ: User A giới thiệu User B, User B giới thiệu User C
// Khi User C đăng ký, hệ thống tạo:

// Quan hệ trực tiếp B -> C
INSERT INTO wp_affpro_user_relationship 
(ancestor_id, descendant_id, distance) VALUES (B_ID, C_ID, 0);

// Quan hệ gián tiếp A -> C (qua B)
INSERT INTO wp_affpro_user_relationship 
(ancestor_id, descendant_id, distance) VALUES (A_ID, C_ID, 1);
```

### 2. wp_affpro_user_order
**Mục đích:** Lưu trữ thông tin đơn hàng và hoa hồng

```sql
CREATE TABLE wp_affpro_user_order (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,          -- ID cộng tác viên nhận hoa hồng
    user_ref VARCHAR(50),              -- Mã giới thiệu được sử dụng
    order_id INT(11) NOT NULL,         -- ID đơn hàng WooCommerce
    level INT(11) DEFAULT 0,           -- Cấp độ hoa hồng (0=trực tiếp, 1=gián tiếp...)
    commission DECIMAL(15,2) DEFAULT 0, -- Số tiền hoa hồng
    status INT(11) DEFAULT 0,          -- Trạng thái (0=pending, 1=approved, 2=paid, 3=cancelled)
    note TEXT,                         -- Ghi chú
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_order_id (order_id),
    KEY idx_status (status),
    KEY idx_level (level),
    KEY idx_user_status (user_id, status),
    KEY idx_order_status (order_id, status),
    KEY idx_created_at (created_at)
);
```

**Luồng xử lý đơn hàng:**
```php
// 1. Khách hàng đặt hàng với ref_code
$order_id = 12345;
$ref_code = 'AFF000123';

// 2. Tìm user từ ref_code
$affiliate_user = get_user_by_ref_code($ref_code);

// 3. Tính hoa hồng cấp 1 (trực tiếp)
$commission_level_0 = calculate_commission($order_id, 0); // 10%
INSERT INTO wp_affpro_user_order 
(user_id, order_id, level, commission, status) 
VALUES ($affiliate_user->ID, $order_id, 0, $commission_level_0, 0);

// 4. Tính hoa hồng các cấp trên
$uplines = get_user_uplines($affiliate_user->ID);
foreach ($uplines as $level => $upline_user) {
    $commission = calculate_commission($order_id, $level + 1);
    INSERT INTO wp_affpro_user_order 
    (user_id, order_id, level, commission, status) 
    VALUES ($upline_user->ID, $order_id, $level + 1, $commission, 0);
}
```

### 3. wp_affpro_traffic
**Mục đích:** Theo dõi traffic và conversion

```sql
CREATE TABLE wp_affpro_traffic (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11),                   -- ID cộng tác viên (nullable cho anonymous)
    user_ref VARCHAR(50),              -- Mã giới thiệu
    ip_address VARCHAR(45),            -- IP address (support IPv6)
    user_agent TEXT,                   -- Browser information
    referrer TEXT,                     -- Trang giới thiệu
    landing_page TEXT,                 -- Trang đích
    utm_source VARCHAR(100),           -- UTM tracking
    utm_medium VARCHAR(100),
    utm_campaign VARCHAR(100),
    session_id VARCHAR(100),           -- Session tracking
    converted TINYINT(1) DEFAULT 0,   -- Có conversion không
    order_id INT(11),                  -- ID đơn hàng nếu có conversion
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_user_ref (user_ref),
    KEY idx_ip_address (ip_address),
    KEY idx_session_id (session_id),
    KEY idx_converted (converted),
    KEY idx_created_at (created_at)
);
```

### 4. wp_affpro_payment
**Mục đích:** Quản lý thanh toán hoa hồng

```sql
CREATE TABLE wp_affpro_payment (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,          -- ID cộng tác viên
    amount DECIMAL(15,2) NOT NULL,     -- Số tiền thanh toán
    method VARCHAR(50),                -- Phương thức (bank, momo, paypal...)
    account_info JSON,                 -- Thông tin tài khoản (encrypted)
    status VARCHAR(20) DEFAULT 'pending', -- pending, processing, completed, failed
    transaction_id VARCHAR(100),       -- Mã giao dịch
    admin_note TEXT,                   -- Ghi chú admin
    processed_by INT(11),              -- Admin xử lý
    processed_at DATETIME,             -- Thời gian xử lý
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_status (status),
    KEY idx_method (method),
    KEY idx_transaction_id (transaction_id),
    KEY idx_created_at (created_at)
);
```

## 🔍 Query Patterns & Performance

### 1. Common Query Patterns

#### Pattern 1: Lấy danh sách downline
```php
// Lấy tất cả downline của user
$downlines = MH_Query::init(null, 'affpro_user_relationship r')
    ->select('r.*, u.display_name, u.user_email')
    ->join('users u', 'r.descendant_id', 'u.ID')
    ->where('r.ancestor_id', $user_id)
    ->orderBy('r.distance', 'ASC')
    ->get();

// Generated SQL:
SELECT r.*, u.display_name, u.user_email 
FROM wp_affpro_user_relationship r 
LEFT JOIN wp_users u ON r.descendant_id = u.ID 
WHERE r.ancestor_id = 123 
ORDER BY r.distance ASC;
```

#### Pattern 2: Tính tổng hoa hồng theo thời gian
```php
// Tổng hoa hồng theo tháng
$monthly_commission = MH_Query::init(null, 'affpro_user_order')
    ->select('YEAR(created_at) as year, MONTH(created_at) as month, SUM(commission) as total')
    ->where('user_id', $user_id)
    ->where('status', 1) // Approved
    ->groupBy('YEAR(created_at), MONTH(created_at)')
    ->orderBy('year DESC, month DESC')
    ->get();
```

#### Pattern 3: Dashboard statistics
```php
// N+1 Query Problem (BAD)
$users = get_all_affiliate_users();
foreach ($users as $user) {
    $user['total_commission'] = get_user_total_commission($user['ID']);
    $user['pending_orders'] = get_user_pending_orders($user['ID']);
    $user['downline_count'] = get_user_downline_count($user['ID']);
}

// Optimized Query (GOOD)
$users_with_stats = MH_Query::init(null, 'users u')
    ->select('u.*, 
              COALESCE(SUM(uo.commission), 0) as total_commission,
              COUNT(DISTINCT CASE WHEN uo.status = 0 THEN uo.id END) as pending_orders,
              COUNT(DISTINCT r.descendant_id) as downline_count')
    ->leftJoin('affpro_user_order uo', 'u.ID', 'uo.user_id')
    ->leftJoin('affpro_user_relationship r', 'u.ID', 'r.ancestor_id')
    ->where('u.role', 'ctv')
    ->groupBy('u.ID')
    ->get();
```

### 2. Performance Issues

#### Issue 1: Missing Indexes
```sql
-- Current slow query
SELECT * FROM wp_affpro_user_order 
WHERE user_id = 123 AND status = 1 AND created_at >= '2024-01-01';

-- Add composite index
ALTER TABLE wp_affpro_user_order 
ADD INDEX idx_user_status_date (user_id, status, created_at);
```

#### Issue 2: Inefficient Relationship Queries
```php
// BAD: Multiple queries for hierarchy
function get_user_hierarchy($user_id, $max_depth = 3) {
    $result = [];
    for ($level = 0; $level <= $max_depth; $level++) {
        $users = MH_Query::init(null, 'affpro_user_relationship')
            ->where('ancestor_id', $user_id)
            ->where('distance', $level)
            ->get();
        $result[$level] = $users;
    }
    return $result;
}

// GOOD: Single query with CTE (if MySQL 8.0+)
WITH RECURSIVE user_hierarchy AS (
    SELECT descendant_id, 0 as level
    FROM wp_affpro_user_relationship 
    WHERE ancestor_id = 123 AND distance = 0
    
    UNION ALL
    
    SELECT r.descendant_id, uh.level + 1
    FROM wp_affpro_user_relationship r
    JOIN user_hierarchy uh ON r.ancestor_id = uh.descendant_id
    WHERE uh.level < 3
)
SELECT * FROM user_hierarchy;
```

## 📈 Performance Optimization Strategies

### 1. Database Indexes

#### Essential Indexes
```sql
-- User Order Performance
ALTER TABLE wp_affpro_user_order ADD INDEX idx_user_status_level (user_id, status, level);
ALTER TABLE wp_affpro_user_order ADD INDEX idx_order_level (order_id, level);
ALTER TABLE wp_affpro_user_order ADD INDEX idx_status_created (status, created_at);

-- Relationship Performance  
ALTER TABLE wp_affpro_user_relationship ADD INDEX idx_ancestor_distance (ancestor_id, distance);
ALTER TABLE wp_affpro_user_relationship ADD INDEX idx_descendant_distance (descendant_id, distance);

-- Traffic Performance
ALTER TABLE wp_affpro_traffic ADD INDEX idx_user_ref_created (user_ref, created_at);
ALTER TABLE wp_affpro_traffic ADD INDEX idx_converted_created (converted, created_at);

-- Payment Performance
ALTER TABLE wp_affpro_payment ADD INDEX idx_user_status_created (user_id, status, created_at);
```

#### Composite Index Strategy
```sql
-- For common WHERE + ORDER BY patterns
ALTER TABLE wp_affpro_user_order 
ADD INDEX idx_user_status_created_desc (user_id, status, created_at DESC);

-- For aggregation queries
ALTER TABLE wp_affpro_user_order 
ADD INDEX idx_status_created_commission (status, created_at, commission);
```

### 2. Query Optimization

#### Optimization 1: Pagination
```php
// BAD: OFFSET with large numbers
$users = MH_Query::init(null, 'affpro_user_order')
    ->where('status', 1)
    ->orderBy('created_at', 'DESC')
    ->limit(20)
    ->offset(10000) // Very slow for large offsets
    ->get();

// GOOD: Cursor-based pagination
$last_id = $_GET['last_id'] ?? 0;
$users = MH_Query::init(null, 'affpro_user_order')
    ->where('status', 1)
    ->where('id', '>', $last_id)
    ->orderBy('id', 'DESC')
    ->limit(20)
    ->get();
```

#### Optimization 2: Aggregation Caching
```php
class AFF_Stats_Cache {
    public static function get_user_stats($user_id) {
        $cache_key = "aff_user_stats_{$user_id}";
        $stats = wp_cache_get($cache_key, 'aff_pro');
        
        if ($stats === false) {
            $stats = self::calculate_user_stats($user_id);
            wp_cache_set($cache_key, $stats, 'aff_pro', 3600); // 1 hour
        }
        
        return $stats;
    }
    
    private static function calculate_user_stats($user_id) {
        return MH_Query::init(null, 'affpro_user_order')
            ->select('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 1 THEN commission ELSE 0 END) as paid_commission,
                SUM(CASE WHEN status = 0 THEN commission ELSE 0 END) as pending_commission,
                AVG(commission) as avg_commission
            ')
            ->where('user_id', $user_id)
            ->first();
    }
}
```

### 3. Database Partitioning

#### Time-based Partitioning for Large Tables
```sql
-- Partition traffic table by month
ALTER TABLE wp_affpro_traffic 
PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
    PARTITION p202401 VALUES LESS THAN (202402),
    PARTITION p202402 VALUES LESS THAN (202403),
    PARTITION p202403 VALUES LESS THAN (202404),
    -- ... continue for each month
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

## 🔧 Database Maintenance

### 1. Regular Cleanup
```php
class AFF_DB_Maintenance {
    public static function cleanup_old_traffic() {
        // Remove traffic data older than 1 year
        global $wpdb;
        $wpdb->query("
            DELETE FROM {$wpdb->prefix}affpro_traffic 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
    }
    
    public static function archive_old_orders() {
        // Archive orders older than 2 years
        global $wpdb;
        
        // Create archive table if not exists
        $wpdb->query("
            CREATE TABLE IF NOT EXISTS {$wpdb->prefix}affpro_user_order_archive 
            LIKE {$wpdb->prefix}affpro_user_order
        ");
        
        // Move old records
        $wpdb->query("
            INSERT INTO {$wpdb->prefix}affpro_user_order_archive 
            SELECT * FROM {$wpdb->prefix}affpro_user_order 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
        ");
        
        // Delete from main table
        $wpdb->query("
            DELETE FROM {$wpdb->prefix}affpro_user_order 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
        ");
    }
}

// Schedule cleanup
wp_schedule_event(time(), 'monthly', 'aff_db_cleanup');
add_action('aff_db_cleanup', ['AFF_DB_Maintenance', 'cleanup_old_traffic']);
```

### 2. Database Health Monitoring
```php
class AFF_DB_Monitor {
    public static function check_table_sizes() {
        global $wpdb;
        
        $tables = [
            'affpro_user_order',
            'affpro_user_relationship', 
            'affpro_traffic',
            'affpro_payment'
        ];
        
        $sizes = [];
        foreach ($tables as $table) {
            $result = $wpdb->get_row("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    table_rows
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE() 
                AND table_name = '{$wpdb->prefix}{$table}'
            ");
            
            $sizes[$table] = $result;
        }
        
        return $sizes;
    }
    
    public static function analyze_slow_queries() {
        global $wpdb;
        
        // Enable slow query log analysis
        $slow_queries = $wpdb->get_results("
            SELECT sql_text, exec_count, avg_timer_wait/1000000000 as avg_time_sec
            FROM performance_schema.events_statements_summary_by_digest 
            WHERE schema_name = DATABASE()
            AND avg_timer_wait > 1000000000  -- > 1 second
            ORDER BY avg_timer_wait DESC 
            LIMIT 10
        ");
        
        return $slow_queries;
    }
}
```

## 📊 Performance Metrics

### Key Performance Indicators
- **Query Response Time**: < 100ms for simple queries, < 500ms for complex
- **Database Size Growth**: Monitor monthly growth rate
- **Index Usage**: Ensure all queries use indexes
- **Connection Pool**: Monitor active connections
- **Cache Hit Ratio**: Target > 90% for frequently accessed data

### Monitoring Queries
```sql
-- Check index usage
EXPLAIN SELECT * FROM wp_affpro_user_order 
WHERE user_id = 123 AND status = 1 
ORDER BY created_at DESC;

-- Monitor table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
    table_rows
FROM information_schema.TABLES 
WHERE table_schema = DATABASE() 
AND table_name LIKE 'wp_affpro_%';

-- Find unused indexes
SELECT 
    t.table_name,
    t.index_name,
    t.column_name
FROM information_schema.statistics t
LEFT JOIN information_schema.index_statistics i 
    ON t.table_schema = i.table_schema 
    AND t.table_name = i.table_name 
    AND t.index_name = i.index_name
WHERE t.table_schema = DATABASE()
    AND t.table_name LIKE 'wp_affpro_%'
    AND i.index_name IS NULL;
```

---

*Database Analysis & Performance Guide - Created: 2025-06-23*