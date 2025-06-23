# 🏗️ Phân Tích Chi Tiết Kiến Trúc Plugin AFF Pro

## 📋 Tổng Quan Plugin

**AFF Pro** là một plugin WordPress quản lý hệ thống affiliate marketing, tích hợp với WooCommerce để theo dõi và tính toán hoa hồng cho các cộng tác viên (CTV).

### 🎯 Mục Đích Chính
- Quản lý hệ thống cộng tác viên đa cấp
- Tính toán hoa hồng tự động từ đơn hàng WooCommerce
- Theo dõi traffic và conversion
- Quản lý thanh toán hoa hồng
- Tạo banner và link affiliate

## 🏛️ Kiến Trúc Tổng Thể

```
aff-pro/
├── aff-pro.php                 # Entry point chính
├── includes/                   # Core classes
│   ├── class-aff-pro.php      # Main plugin class
│   ├── class-aff-pro-loader.php # Hook loader
│   ├── class-aff-pro-license.php # License management
│   └── class-query.php        # Custom query builder
├── admin/                      # Admin interface
│   ├── class-aff-pro-admin.php # Admin functionality
│   ├── ajax-admin.php         # AJAX handlers
│   └── partials/              # Vue.js admin interface
├── public/                     # Frontend functionality
├── classes/                    # Business logic classes
└── helpers/                    # Utility functions
```

## 🚀 Luồng Khởi Tạo Plugin

### 1. Entry Point (aff-pro.php)
```php
// 1. Kiểm tra WordPress environment
if (!defined('WPINC')) {
    die;
}

// 2. Define constants
define('AFF_Pro_VERSION', '1.0.0');
define('AFF_URL', plugin_dir_url(__FILE__));
define('AFF_PATH', plugin_dir_path(__FILE__));

// 3. Activation/Deactivation hooks
register_activation_hook(__FILE__, 'activate_AFF_Pro');
register_deactivation_hook(__FILE__, 'deactivate_AFF_Pro');

// 4. Khởi tạo plugin
function run_AFF_Pro() {
    $plugin = new AFF_Pro();
    $plugin->run();
}
run_AFF_Pro();
```

### 2. Main Plugin Class (includes/class-aff-pro.php)
```php
class AFF_Pro {
    protected $loader;      // Hook loader
    protected $plugin_name; // Plugin identifier
    protected $version;     // Plugin version
    
    public function __construct() {
        $this->plugin_name = 'aff-pro';
        $this->version = AFF_Pro_VERSION;
        
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    public function run() {
        $this->loader->run(); // Execute all registered hooks
    }
}
```

## 🔧 Hệ Thống Hook Loader

### AFF_Pro_Loader Class
```php
class AFF_Pro_Loader {
    protected $actions = [];  // WordPress actions
    protected $filters = [];  // WordPress filters
    
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    public function run() {
        // Register all actions
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']), 
                      $hook['priority'], $hook['accepted_args']);
        }
        
        // Register all filters
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']), 
                      $hook['priority'], $hook['accepted_args']);
        }
    }
}
```

## 🎛️ Admin Interface System

### 1. Admin Class Structure
```php
class AFF_Pro_Admin {
    private $plugin_name;
    private $version;
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Initialize AJAX handler
        new AFF_Ajax_Admin();
        
        // Register hooks
        add_action('admin_menu', [$this, 'add_admin_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    public function add_admin_pages() {
        // Kiểm tra user capabilities
        $user = wp_get_current_user();
        $role = $user->roles[0] ?? '';
        
        if (in_array($role, ['administrator', 'shop_manager'])) {
            add_menu_page(
                'AffPro AFF',           // Page title
                'AffPro AFF',           // Menu title
                'manage_options',       // Capability
                'aff-pro',             // Menu slug
                [$this, 'admin_template'], // Callback
                AFF_URL . 'public/images/box.svg', // Icon
                110                     // Position
            );
        }
    }
}
```

### 2. Vue.js Admin Interface
```javascript
// admin/partials/aff-pro-admin-display.php
const router = new VueRouter({
    routes: [
        { path: '/', component: indexPage },           // Dashboard
        { path: '/users', component: userPage },       // User management
        { path: '/settings', component: settingsPage }, // Settings
        { path: '/history', component: historyPage },   // Commission history
        { path: '/payments', component: paymentPage }   // Payment management
    ]
});

new Vue({
    el: '#q-app',
    router,
    data: {
        // Global state
    },
    methods: {
        // Global methods
    }
});
```

## 🗄️ Database Schema & Query System

### 1. Custom Query Builder (MH_Query)
```php
class MH_Query {
    protected $select = [];
    protected $from = null;
    protected $join = [];
    protected $where = [];
    protected $order = [];
    protected $limit = null;
    
    public static function init($id = null, $table = '', $prefix = true) {
        $builder = new self();
        if ($table) {
            $builder->table($table, $prefix);
        }
        return $builder;
    }
    
    // Fluent interface methods
    public function where($column, $param1 = null, $param2 = null, $joint = 'and') {
        // Build WHERE conditions
        return $this;
    }
    
    public function join($table, $localKey, $operator = null, $referenceKey = null) {
        // Build JOIN statements
        return $this;
    }
    
    public function get() {
        // Execute query and return results
        global $wpdb;
        $sql = $this->buildQuery();
        return $wpdb->get_results($sql, ARRAY_A);
    }
}
```

### 2. Database Tables
```sql
-- Bảng quan hệ cộng tác viên (đa cấp)
wp_affpro_user_relationship
├── ancestor_id (INT)    # ID cấp trên
├── descendant_id (INT)  # ID cấp dưới  
├── distance (INT)       # Khoảng cách cấp (0=trực tiếp, 1=cấp 2, ...)
└── created_at (DATETIME)

-- Bảng đơn hàng cộng tác viên
wp_affpro_user_order
├── id (INT PRIMARY KEY)
├── user_id (INT)        # ID cộng tác viên
├── user_ref (VARCHAR)   # Mã giới thiệu
├── order_id (INT)       # ID đơn hàng WooCommerce
├── level (INT)          # Cấp độ hoa hồng (0=trực tiếp, 1=gián tiếp)
├── commission (DECIMAL) # Số tiền hoa hồng
├── status (INT)         # Trạng thái (0=pending, 1=approved, 2=paid)
└── created_at (DATETIME)

-- Bảng lịch sử traffic
wp_affpro_traffic
├── id (INT PRIMARY KEY)
├── user_id (INT)        # ID cộng tác viên
├── ip_address (VARCHAR) # IP người truy cập
├── user_agent (TEXT)    # Browser info
├── referrer (TEXT)      # Trang giới thiệu
├── landing_page (TEXT)  # Trang đích
└── created_at (DATETIME)

-- Bảng thanh toán
wp_affpro_payment
├── id (INT PRIMARY KEY)
├── user_id (INT)        # ID cộng tác viên
├── amount (DECIMAL)     # Số tiền thanh toán
├── method (VARCHAR)     # Phương thức (bank, momo, etc.)
├── status (VARCHAR)     # Trạng thái (pending, completed, failed)
├── transaction_id (VARCHAR) # Mã giao dịch
└── created_at (DATETIME)
```

## 🔄 Business Logic Classes

### 1. User Management (classes/user-class.php)
```php
class AFF_User {
    public static function getUserBy($params) {
        // Lấy thông tin user theo điều kiện
        return MH_Query::init(null, 'users')
            ->where($params['column'], $params['value'])
            ->first();
    }
    
    public static function createAffiliate($user_data) {
        // Tạo tài khoản cộng tác viên mới
        $user_id = wp_insert_user($user_data);
        
        if (!is_wp_error($user_id)) {
            // Gán role affiliate
            $user = new WP_User($user_id);
            $user->set_role('ctv'); // Custom role
            
            // Tạo mã giới thiệu
            $ref_code = self::generateRefCode($user_id);
            update_user_meta($user_id, 'aff_ref_code', $ref_code);
        }
        
        return $user_id;
    }
    
    private static function generateRefCode($user_id) {
        // Tạo mã giới thiệu unique
        return 'AFF' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
    }
}
```

### 2. Commission Calculation (classes/commission-settings-class.php)
```php
class AFF_Commission_Settings {
    public static function calculateCommission($order_id, $user_id, $level = 0) {
        $order = wc_get_order($order_id);
        $order_total = $order->get_total();
        
        // Lấy cài đặt hoa hồng theo cấp
        $commission_rate = self::getCommissionRate($level);
        
        // Tính hoa hồng
        $commission = $order_total * ($commission_rate / 100);
        
        // Áp dụng các rule đặc biệt
        $commission = self::applySpecialRules($commission, $order, $user_id);
        
        return $commission;
    }
    
    private static function getCommissionRate($level) {
        $rates = get_option('aff_commission_rates', [
            0 => 10, // Cấp 1: 10%
            1 => 5,  // Cấp 2: 5%
            2 => 2   // Cấp 3: 2%
        ]);
        
        return $rates[$level] ?? 0;
    }
    
    private static function applySpecialRules($commission, $order, $user_id) {
        // Rule 1: Minimum commission
        $min_commission = get_option('aff_min_commission', 0);
        $commission = max($commission, $min_commission);
        
        // Rule 2: Maximum commission per order
        $max_commission = get_option('aff_max_commission', 1000000);
        $commission = min($commission, $max_commission);
        
        // Rule 3: Product-specific rates
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $product_rate = get_post_meta($product_id, '_aff_commission_rate', true);
            if ($product_rate) {
                // Recalculate based on product rate
            }
        }
        
        return $commission;
    }
}
```

### 3. Order Processing (classes/user-order-class.php)
```php
class AFF_User_Order {
    public static function processOrder($order_id) {
        $order = wc_get_order($order_id);
        
        // Kiểm tra có ref_id không
        $ref_id = $order->get_meta('_ref_id');
        if (!$ref_id) {
            return false; // Không phải đơn affiliate
        }
        
        // Tìm user từ ref_code
        $affiliate_user = self::getUserByRefCode($ref_id);
        if (!$affiliate_user) {
            return false;
        }
        
        // Tính hoa hồng cho cấp trực tiếp
        self::createCommissionRecord($order_id, $affiliate_user['ID'], 0);
        
        // Tính hoa hồng cho các cấp gián tiếp
        self::processUplineCommissions($order_id, $affiliate_user['ID']);
        
        return true;
    }
    
    private static function createCommissionRecord($order_id, $user_id, $level) {
        $commission = AFF_Commission_Settings::calculateCommission($order_id, $user_id, $level);
        
        if ($commission > 0) {
            MH_Query::init(null, 'affpro_user_order')->insert([
                'user_id' => $user_id,
                'order_id' => $order_id,
                'level' => $level,
                'commission' => $commission,
                'status' => 0, // Pending
                'created_at' => current_time('mysql')
            ]);
            
            // Trigger hooks
            do_action('aff_commission_created', $user_id, $order_id, $commission, $level);
        }
    }
    
    private static function processUplineCommissions($order_id, $user_id) {
        // Lấy danh sách upline (cấp trên)
        $uplines = MH_Query::init(null, 'affpro_user_relationship')
            ->where('descendant_id', $user_id)
            ->where('distance', '>', 0)
            ->orderBy('distance', 'ASC')
            ->get();
        
        foreach ($uplines as $upline) {
            $level = $upline['distance'];
            self::createCommissionRecord($order_id, $upline['ancestor_id'], $level);
        }
    }
}
```

## 🌐 Frontend Integration

### 1. Public Class (public/class-aff-pro-public.php)
```php
class AFF_Pro_Public {
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Hook vào WooCommerce
        add_action('woocommerce_checkout_order_processed', [$this, 'process_affiliate_order']);
        add_action('wp_head', [$this, 'track_affiliate_visit']);
        add_filter('woocommerce_add_to_cart_redirect', [$this, 'handle_affiliate_redirect']);
    }
    
    public function track_affiliate_visit() {
        // Theo dõi traffic từ link affiliate
        if (isset($_GET['ref'])) {
            $ref_code = sanitize_text_field($_GET['ref']);
            
            // Lưu vào session/cookie
            setcookie('aff_ref', $ref_code, time() + (30 * DAY_IN_SECONDS), '/');
            
            // Log traffic
            $this->logTraffic($ref_code);
        }
    }
    
    public function process_affiliate_order($order_id) {
        // Xử lý đơn hàng có affiliate
        $ref_code = $_COOKIE['aff_ref'] ?? null;
        
        if ($ref_code) {
            $order = wc_get_order($order_id);
            $order->update_meta_data('_ref_id', $ref_code);
            $order->save();
            
            // Process commissions
            AFF_User_Order::processOrder($order_id);
        }
    }
}
```

### 2. Shortcodes & Templates
```php
// helpers/aff-pro-public-display.php
class AFF_Pro_Display {
    public static function affiliate_dashboard_shortcode($atts) {
        // [aff_dashboard] shortcode
        $user_id = get_current_user_id();
        if (!$user_id) {
            return 'Vui lòng đăng nhập';
        }
        
        ob_start();
        include AFF_PATH . 'public/partials/dashboard.php';
        return ob_get_clean();
    }
    
    public static function affiliate_link_shortcode($atts) {
        // [aff_link product_id="123"] shortcode
        $atts = shortcode_atts([
            'product_id' => 0,
            'text' => 'Mua ngay'
        ], $atts);
        
        $user_id = get_current_user_id();
        $ref_code = get_user_meta($user_id, 'aff_ref_code', true);
        
        if (!$ref_code) {
            return '';
        }
        
        $product_url = get_permalink($atts['product_id']);
        $affiliate_url = add_query_arg('ref', $ref_code, $product_url);
        
        return sprintf('<a href="%s" class="aff-link">%s</a>', 
                      esc_url($affiliate_url), 
                      esc_html($atts['text']));
    }
}

// Register shortcodes
add_shortcode('aff_dashboard', ['AFF_Pro_Display', 'affiliate_dashboard_shortcode']);
add_shortcode('aff_link', ['AFF_Pro_Display', 'affiliate_link_shortcode']);
```

## 🔐 License Management System

### License Manager (includes/class-aff-pro-license.php)
```php
class AFF_Pro_License_Manager {
    private $plugin_data;
    private $api_url = 'https://affpro.dev/wp-json/license/v1/';
    
    public function __construct() {
        // Load plugin data safely
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $plugin_file = AFF_PATH . 'aff-pro.php';
        if (file_exists($plugin_file)) {
            $this->plugin_data = get_plugin_data($plugin_file);
        }
        
        // Register hooks
        add_action('admin_init', [$this, 'check_license']);
        add_action('wp_ajax_aff_activate_license', [$this, 'activate_license']);
    }
    
    public function check_license() {
        $license_key = get_option('aff_pro_license_key');
        $license_status = get_option('aff_pro_license_status');
        
        if ($license_key && $license_status !== 'valid') {
            $this->validate_license($license_key);
        }
    }
    
    private function validate_license($license_key) {
        $response = wp_remote_post($this->api_url . 'validate', [
            'body' => [
                'license_key' => $license_key,
                'domain' => home_url(),
                'plugin_version' => $this->plugin_data['Version'] ?? ''
            ]
        ]);
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if ($data['valid']) {
                update_option('aff_pro_license_status', 'valid');
                update_option('aff_pro_license_expires', $data['expires']);
            } else {
                update_option('aff_pro_license_status', 'invalid');
            }
        }
    }
}
```

## 🔄 AJAX System

### AJAX Handler (admin/ajax-admin.php)
```php
class AFF_Ajax_Admin {
    public function __construct() {
        // User management
        add_action('wp_ajax_aff_get_users', [$this, 'get_users']);
        add_action('wp_ajax_aff_create_user', [$this, 'create_user']);
        add_action('wp_ajax_aff_update_user_role', [$this, 'update_user_role']);
        
        // Commission management
        add_action('wp_ajax_aff_re_commission', [$this, 're_calculate_commission']);
        add_action('wp_ajax_aff_assign_commission', [$this, 'assign_commission']);
        
        // Statistics
        add_action('wp_ajax_aff_get_dashboard_stats', [$this, 'get_dashboard_stats']);
    }
    
    public function get_users() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'aff_ajax_nonce')) {
            wp_die('Security check failed');
        }
        
        // Get pagination params
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 20);
        $search = sanitize_text_field($_POST['search'] ?? '');
        
        // Build query
        $query = MH_Query::init(null, 'users u')
            ->select('u.*, um.meta_value as ref_code')
            ->leftJoin('usermeta um', 'u.ID', 'um.user_id')
            ->where('um.meta_key', 'aff_ref_code');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('u.user_login', 'LIKE', "%{$search}%")
                  ->orWhere('u.user_email', 'LIKE', "%{$search}%")
                  ->orWhere('u.display_name', 'LIKE', "%{$search}%");
            });
        }
        
        // Get total count
        $total = $query->count();
        
        // Get paginated results
        $users = $query->limit($per_page)
                      ->offset(($page - 1) * $per_page)
                      ->get();
        
        // Add commission stats for each user
        foreach ($users as &$user) {
            $user['total_commission'] = $this->getUserTotalCommission($user['ID']);
            $user['pending_commission'] = $this->getUserPendingCommission($user['ID']);
        }
        
        wp_send_json_success([
            'users' => $users,
            'total' => $total,
            'pages' => ceil($total / $per_page)
        ]);
    }
    
    private function getUserTotalCommission($user_id) {
        return MH_Query::init(null, 'affpro_user_order')
            ->where('user_id', $user_id)
            ->where('status', 1) // Approved
            ->sum('commission');
    }
}
```

## 📊 Reporting & Analytics

### Statistics Class (classes/history-class.php)
```php
class AFF_History {
    public static function getDashboardStats($user_id = null) {
        $stats = [];
        
        // Total users
        $stats['total_users'] = MH_Query::init(null, 'users u')
            ->join('usermeta um', 'u.ID', 'um.user_id')
            ->where('um.meta_key', 'aff_ref_code')
            ->count();
        
        // Total orders
        $stats['total_orders'] = MH_Query::init(null, 'affpro_user_order')
            ->count();
        
        // Total commission
        $stats['total_commission'] = MH_Query::init(null, 'affpro_user_order')
            ->where('status', 1)
            ->sum('commission');
        
        // Monthly stats
        $stats['monthly_stats'] = self::getMonthlyStats();
        
        return $stats;
    }
    
    private static function getMonthlyStats() {
        $current_month = date('Y-m');
        
        return [
            'orders' => MH_Query::init(null, 'affpro_user_order')
                ->where('created_at', 'LIKE', "{$current_month}%")
                ->count(),
            'commission' => MH_Query::init(null, 'affpro_user_order')
                ->where('created_at', 'LIKE', "{$current_month}%")
                ->where('status', 1)
                ->sum('commission'),
            'new_users' => MH_Query::init(null, 'users')
                ->where('user_registered', 'LIKE', "{$current_month}%")
                ->count()
        ];
    }
}
```

## 🔧 Configuration & Settings

### Config Management (classes/config-class.php)
```php
class AFF_Config {
    private static $default_settings = [
        'commission_rates' => [0 => 10, 1 => 5, 2 => 2],
        'min_payout' => 100000,
        'cookie_duration' => 30,
        'auto_approve' => false,
        'email_notifications' => true
    ];
    
    public static function get($key, $default = null) {
        $settings = get_option('aff_pro_settings', self::$default_settings);
        return $settings[$key] ?? $default;
    }
    
    public static function set($key, $value) {
        $settings = get_option('aff_pro_settings', self::$default_settings);
        $settings[$key] = $value;
        update_option('aff_pro_settings', $settings);
    }
    
    public static function getCommissionStructure() {
        return [
            'levels' => self::get('commission_rates'),
            'min_commission' => self::get('min_commission', 0),
            'max_commission' => self::get('max_commission', 1000000),
            'calculation_method' => self::get('calculation_method', 'percentage')
        ];
    }
}
```

## 🎯 Tóm Tắt Luồng Hoạt Động

### 1. User Registration Flow
```
1. User đăng ký → AFF_User::createAffiliate()
2. Tạo ref_code unique
3. Gán role 'ctv'
4. Tạo relationship record (nếu có upline)
```

### 2. Order Processing Flow
```
1. Customer click affiliate link → track_affiliate_visit()
2. Set cookie với ref_code
3. Customer đặt hàng → process_affiliate_order()
4. Tính hoa hồng cho tất cả levels → AFF_User_Order::processOrder()
5. Gửi notification → Email/SMS
```

### 3. Commission Calculation Flow
```
1. Lấy order total
2. Áp dụng commission rate theo level
3. Kiểm tra special rules (min/max, product-specific)
4. Tạo commission record
5. Update user balance
```

### 4. Payment Processing Flow
```
1. User request payout
2. Check minimum payout amount
3. Admin approve/reject
4. Process payment via configured method
5. Update commission status
6. Send confirmation
```

---

*Phân tích chi tiết kiến trúc plugin AFF Pro - Created: 2025-06-23*