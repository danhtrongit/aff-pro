# 💰 Phân Tích Chi Tiết Lưu Trữ Hoa Hồng - Plugin AFF Pro

## 📊 Tổng Quan Hệ Thống Lưu Trữ Hoa Hồng

Plugin AFF Pro sử dụng **hệ thống lưu trữ đa tầng** để quản lý hoa hồng, bao gồm:

1. **Bảng chính**: `wp_affpro_user_order` - Lưu chi tiết từng giao dịch hoa hồng
2. **Bảng user**: `wp_users` (mở rộng) - Lưu số dư và tổng thu nhập
3. **Bảng lịch sử**: `wp_affpro_history` - Lưu lịch sử thay đổi số dư
4. **Bảng thanh toán**: `wp_affpro_payments` - Lưu thông tin thanh toán

## 🗄️ Chi Tiết Cấu Trúc Lưu Trữ

### 1. Bảng Chính: `wp_affpro_user_order`

**Mục đích**: Lưu trữ chi tiết từng khoản hoa hồng từ đơn hàng

```sql
CREATE TABLE wp_affpro_user_order (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,              -- ID cộng tác viên nhận hoa hồng
    user_ref VARCHAR(50),                  -- Mã giới thiệu được sử dụng
    user_login VARCHAR(60),                -- Username của CTV
    order_id INT(11) NOT NULL,             -- ID đơn hàng WooCommerce
    level INT(11) DEFAULT 0,               -- Cấp độ (0=trực tiếp, 1=cấp 2, 2=cấp 3...)
    commission DECIMAL(15,2) DEFAULT 0,    -- 💰 SỐ TIỀN HOA HỒNG
    total DECIMAL(15,2) DEFAULT 0,         -- Giá trị đơn hàng
    status INT(11) DEFAULT 0,              -- Trạng thái (0=pending, 1=approved, 2=paid)
    description TEXT,                      -- Mô tả giao dịch
    customer_name VARCHAR(255),            -- Tên khách hàng
    customer_phone VARCHAR(20),            -- SĐT khách hàng
    ref_product VARCHAR(255),              -- Sản phẩm giới thiệu
    ref_coupon VARCHAR(100),               -- Coupon sử dụng
    order_json TEXT,                       -- Chi tiết đơn hàng (JSON)
    date DATETIME DEFAULT CURRENT_TIMESTAMP, -- Thời gian tạo
    
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_order_id (order_id),
    KEY idx_status (status),
    KEY idx_level (level)
);
```

**Ví dụ dữ liệu:**
```sql
INSERT INTO wp_affpro_user_order VALUES
(1, 123, 'AFF000123', 'ctv_nguyen', 5001, 0, 50000.00, 500000.00, 1, '+50,000 Hoa hồng đơn hàng #5001', 'Nguyễn Văn A', '0901234567', NULL, NULL, '{}', '2024-01-15 10:30:00'),
(2, 456, 'AFF000123', 'ctv_tran', 5001, 1, 25000.00, 500000.00, 1, '+25,000 Hoa hồng cấp 2 từ ctv_nguyen', 'Nguyễn Văn A', '0901234567', NULL, NULL, '{}', '2024-01-15 10:30:00');
```

### 2. Bảng User (Mở Rộng): `wp_users`

**Các trường được thêm vào bảng users:**

```sql
-- Thêm các cột vào wp_users
ALTER TABLE wp_users ADD COLUMN balance DECIMAL(15,2) DEFAULT 0;      -- 💰 SỐ DƯ HIỆN TẠI
ALTER TABLE wp_users ADD COLUMN income DECIMAL(15,2) DEFAULT 0;       -- 💰 TỔNG THU NHẬP
ALTER TABLE wp_users ADD COLUMN commission_percent DECIMAL(5,2) DEFAULT 0; -- % hoa hồng cá nhân
ALTER TABLE wp_users ADD COLUMN level INT(11) DEFAULT 0;              -- Cấp độ CTV
ALTER TABLE wp_users ADD COLUMN aff_active TINYINT(1) DEFAULT 1;      -- Trạng thái hoạt động
ALTER TABLE wp_users ADD COLUMN parent_id INT(11) DEFAULT 0;          -- ID người giới thiệu
ALTER TABLE wp_users ADD COLUMN user_phone VARCHAR(20);               -- Số điện thoại
```

**Ví dụ dữ liệu:**
```sql
-- User có ID 123
balance: 150000.00        -- Số dư khả dụng để rút
income: 500000.00         -- Tổng thu nhập từ trước đến nay
commission_percent: 15.00 -- Hoa hồng cá nhân 15%
level: 2                  -- Cấp độ CTV level 2
```

### 3. Bảng Lịch Sử: `wp_affpro_history`

**Mục đích**: Theo dõi mọi thay đổi số dư

```sql
CREATE TABLE wp_affpro_history (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,              -- ID user
    user_login VARCHAR(60),                -- Username
    amount DECIMAL(15,2) NOT NULL,         -- 💰 SỐ TIỀN THAY ĐỔI
    type TINYINT(1) NOT NULL,              -- Loại (1=cộng, 0=trừ)
    begin_balance DECIMAL(15,2),           -- Số dư trước khi thay đổi
    end_balance DECIMAL(15,2),             -- 💰 SỐ DƯ SAU KHI THAY ĐỔI
    description TEXT,                      -- Mô tả giao dịch
    order_id INT(11),                      -- ID đơn hàng liên quan
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_type (type),
    KEY idx_created_at (created_at)
);
```

### 4. Bảng Thanh Toán: `wp_affpro_payments`

**Mục đích**: Quản lý các lần rút tiền

```sql
CREATE TABLE wp_affpro_payments (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,              -- ID user rút tiền
    amount DECIMAL(15,2) NOT NULL,         -- 💰 SỐ TIỀN RÚT
    method VARCHAR(50),                    -- Phương thức (bank, momo, paypal)
    account_info TEXT,                     -- Thông tin tài khoản
    status VARCHAR(20) DEFAULT 'pending',  -- Trạng thái
    transaction_id VARCHAR(100),           -- Mã giao dịch
    admin_note TEXT,                       -- Ghi chú admin
    processed_by INT(11),                  -- Admin xử lý
    processed_at DATETIME,                 -- Thời gian xử lý
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_status (status)
);
```

## 🔄 Luồng Xử Lý Hoa Hồng

### 1. Khi Có Đơn Hàng Mới

```php
// File: classes/user-order-class.php
function processNewOrder($order_id, $ref_code) {
    // 1. Tìm user từ ref_code
    $affiliate_user = getUserByRefCode($ref_code);
    
    // 2. Tính hoa hồng cấp 1 (trực tiếp)
    $commission_data = calculateCommission($order, $affiliate_user);
    
    // 3. Lưu vào bảng affpro_user_order
    $record = [
        'user_id' => $affiliate_user->ID,
        'order_id' => $order_id,
        'level' => 0,                    // Cấp trực tiếp
        'commission' => $commission_data['commission'],
        'total' => $order->get_total(),
        'status' => 0,                   // Pending
        'description' => "+{$commission_data['commission']} Hoa hồng đơn hàng #{$order_id}",
        'date' => current_time('mysql')
    ];
    
    MH_Query::init(null, 'affpro_user_order')->insert($record);
    
    // 4. Xử lý hoa hồng các cấp trên
    setCommissionAncestors($affiliate_user, $order, $commission_data['commission']);
}
```

### 2. Khi Duyệt Hoa Hồng (Approve)

```php
// File: classes/user-order-class.php - function approveCommission()
function approveCommission($order_id, $status) {
    // 1. Lấy tất cả commission pending của đơn hàng
    $rows = MH_Query::init(null, 'affpro_user_order')
        ->where('order_id', $order_id)
        ->where('status', 0)  // Pending
        ->get();
    
    foreach ($rows as $row) {
        // 2. Cập nhật số dư user
        $user = AFF_User::getUserBy(['column' => 'ID', 'value' => $row['user_id']]);
        
        // 3. Gọi function changeBalance để cập nhật
        AFF_User::changeBalance(
            $user['ID'], 
            $row['commission'],  // Số tiền hoa hồng
            1,                   // Type = 1 (cộng tiền)
            $row['total'],       // Thu nhập (chỉ tính cho cấp 0)
            $row['description'], 
            $row['order_id']
        );
        
        // 4. Cập nhật status = 1 (approved)
        MH_Query::init(null, 'affpro_user_order')
            ->where('id', $row['id'])
            ->update(['status' => 1]);
    }
}
```

### 3. Function changeBalance() - Core Logic

```php
// File: classes/user-class.php
static function changeBalance($user_id, $amount, $type, $income = 0, $description = '', $order_id = '') {
    $user = self::getUserBy(['column' => 'ID', 'value' => $user_id]);
    
    if ($user) {
        // 1. Tính số dư mới
        $begin_balance = $user['balance'];
        $end_balance = $type == 1 ? $begin_balance + $amount : $begin_balance - $amount;
        
        // 2. Cập nhật wp_users
        $data = [
            'balance' => $end_balance,           // 💰 CẬP NHẬT SỐ DƯ
            'income' => $user['income'] + $income, // 💰 CẬP NHẬT THU NHẬP
        ];
        
        MH_Query::init(null, 'users')->where('ID', $user_id)->update($data);
        
        // 3. Ghi lịch sử vào affpro_history
        if ($amount) {
            $note = [
                'user_id' => $user_id,
                'user_login' => $user['user_login'],
                'amount' => $amount,
                'type' => $type,
                'end_balance' => $end_balance,      // 💰 SỐ DƯ SAU THAY ĐỔI
                'begin_balance' => $begin_balance,  // 💰 SỐ DƯ TRƯỚC THAY ĐỔI
                'description' => $description,
                'order_id' => $order_id
            ];
            
            AFF_History::create($note);
        }
        
        return true;
    }
}
```

## 📈 Cách Tính Toán Hoa Hồng

### 1. Hoa Hồng Theo Sản Phẩm (Product Mode)

```php
// File: classes/user-order-class.php - setCommissionProductMode()
function setCommissionProductMode($user, $order, $ref_coupon) {
    $commission = 0;
    $settings = getSettings();
    
    // Lấy % hoa hồng mặc định
    $commission_percent_default = $settings['commission_percent_default'];
    
    // Lấy % hoa hồng theo level user
    $commission_level = 0;
    if ($user->level > 0) {
        $commission_user_levels = $settings['commission_user_levels'];
        $commission_level = $commission_user_levels[$user->level]['commission'];
    }
    
    // Duyệt từng sản phẩm trong đơn hàng
    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $line_total = $item->get_total();
        
        // Lấy % hoa hồng riêng của sản phẩm
        $commission_setting = AFF_Commission_Settings::getCommissionSettingById($product_id);
        
        if ($commission_setting) {
            $commission_percent = $commission_setting + $commission_level;
        } else {
            $commission_percent = $commission_percent_default + $commission_level;
        }
        
        // Nếu user có % hoa hồng riêng
        if ($user->commission_percent && $user->commission_percent > 0) {
            $commission_percent = $user->commission_percent;
        }
        
        // Tính hoa hồng cho sản phẩm này
        $commission += ceil(($line_total / 100) * $commission_percent);
    }
    
    return [
        'commission' => $commission,
        'commission_percent' => $commission_percent,
    ];
}
```

### 2. Hoa Hồng Theo Đơn Hàng (Order Mode)

```php
// File: classes/user-order-class.php - setCommissionOrderMode()
function setCommissionOrderMode($user, $order, $ref_coupon) {
    $settings = getSettings();
    
    // Tính % hoa hồng
    if ($user->commission_percent) {
        $commission_percent = $user->commission_percent; // % riêng của user
    } else {
        $commission_percent_default = $settings['commission_percent_default'];
        $commission_level = 0;
        
        // Cộng % theo level
        if ($user->level > 0) {
            $commission_user_levels = $settings['commission_user_levels'];
            $commission_level = $commission_user_levels[$user->level]['commission'];
        }
        
        $commission_percent = $commission_percent_default + $commission_level;
    }
    
    // Tính tổng đơn hàng
    $total = $order->get_total();
    
    // Có tính phí ship và thuế không?
    if ($settings['aff_commission_include_order_shipping'] == 'false') {
        $total = $order->get_total() - $order->get_total_tax() - $order->get_total_shipping();
    }
    
    // Tính hoa hồng
    $commission = ceil(($total / 100) * $commission_percent);
    
    return [
        'commission' => $commission,
        'commission_percent' => $commission_percent,
    ];
}
```

### 3. Hoa Hồng Đa Cấp

```php
// File: classes/user-order-class.php - setCommissionAncestors()
function setCommissionAncestors($user, $order, $commission, $order_json) {
    $settings = getSettings();
    $commission_relationship_levels = $settings['commission_relationship_levels'];
    
    if ($settings['relationship_level']) {
        // Lấy danh sách upline (cấp trên)
        $ancestors = AFF_User_Relationship::getAncestor($user->ID, $settings['relationship_level']);
        
        foreach ($ancestors as $ancestor) {
            if ($ancestor['distance'] == 0) continue; // Bỏ qua chính mình
            
            $level = $ancestor['distance']; // 1, 2, 3...
            
            // Lấy % hoa hồng cho level này
            if (isset($commission_relationship_levels[$level - 1]['commission'])) {
                $commission_percent = floatval($commission_relationship_levels[$level - 1]['commission']);
                
                // 2 chế độ tính:
                if ($settings['commission_relationship_mode'] == 'commission') {
                    // Mode 1: % từ hoa hồng cấp dưới
                    $commission_level = $commission / 100 * $commission_percent;
                } else {
                    // Mode 2: % từ giá trị đơn hàng
                    $total = $order->get_total();
                    $commission_level = $total / 100 * $commission_percent;
                }
                
                // Lưu vào database
                $data = [
                    'user_id' => $ancestor['ancestor_id'],
                    'user_ref' => $user->user_login,
                    'order_id' => $order->get_id(),
                    'level' => $ancestor['distance'],
                    'commission' => $commission_level,  // 💰 HOA HỒNG CẤP TRÊN
                    'total' => $order->get_total(),
                    'status' => 0, // Pending
                    'description' => "+{$commission_level} Hoa hồng cấp {$ancestor['distance']} từ {$user->user_login}",
                    'date' => current_time('mysql')
                ];
                
                MH_Query::init(null, 'affpro_user_order')->insert($data);
            }
        }
    }
}
```

## 💳 Hệ Thống Rút Tiền

### 1. Tạo Yêu Cầu Rút Tiền

```php
function createPaymentRequest($user_id, $amount, $method, $account_info) {
    // 1. Kiểm tra số dư
    $user = AFF_User::getUserBy(['column' => 'ID', 'value' => $user_id]);
    
    if ($user['balance'] < $amount) {
        return ['success' => false, 'message' => 'Số dư không đủ'];
    }
    
    // 2. Kiểm tra số tiền rút tối thiểu
    $min_payout = AFF_Config::get('min_payout', 100000);
    if ($amount < $min_payout) {
        return ['success' => false, 'message' => 'Số tiền rút tối thiểu: ' . number_format($min_payout)];
    }
    
    // 3. Tạo yêu cầu rút tiền
    $payment_data = [
        'user_id' => $user_id,
        'amount' => $amount,
        'method' => $method,
        'account_info' => json_encode($account_info),
        'status' => 'pending',
        'created_at' => current_time('mysql')
    ];
    
    $payment_id = MH_Query::init(null, 'affpro_payments')->insert($payment_data);
    
    // 4. Trừ tiền tạm thời (hold)
    AFF_User::changeBalance(
        $user_id, 
        $amount, 
        0, // Type = 0 (trừ tiền)
        0, 
        "Yêu cầu rút tiền #{$payment_id}", 
        0
    );
    
    return ['success' => true, 'payment_id' => $payment_id];
}
```

### 2. Xử Lý Thanh Toán

```php
function processPayment($payment_id, $status, $transaction_id = '', $admin_note = '') {
    $payment = MH_Query::init(null, 'affpro_payments')->where('id', $payment_id)->first();
    
    if ($status == 'completed') {
        // Thanh toán thành công - không cần hoàn tiền
        MH_Query::init(null, 'affpro_payments')->where('id', $payment_id)->update([
            'status' => 'completed',
            'transaction_id' => $transaction_id,
            'admin_note' => $admin_note,
            'processed_at' => current_time('mysql'),
            'processed_by' => get_current_user_id()
        ]);
        
    } else if ($status == 'failed') {
        // Thanh toán thất bại - hoàn tiền
        AFF_User::changeBalance(
            $payment['user_id'], 
            $payment['amount'], 
            1, // Type = 1 (cộng tiền)
            0, 
            "Hoàn tiền rút tiền thất bại #{$payment_id}", 
            0
        );
        
        MH_Query::init(null, 'affpro_payments')->where('id', $payment_id)->update([
            'status' => 'failed',
            'admin_note' => $admin_note,
            'processed_at' => current_time('mysql'),
            'processed_by' => get_current_user_id()
        ]);
    }
}
```

## 📊 Truy Vấn Thống Kê Hoa Hồng

### 1. Tổng Hoa Hồng Của User

```php
function getUserCommissionStats($user_id) {
    return MH_Query::init(null, 'affpro_user_order')
        ->select('
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 0 THEN commission ELSE 0 END) as pending_commission,
            SUM(CASE WHEN status = 1 THEN commission ELSE 0 END) as approved_commission,
            SUM(CASE WHEN status = 2 THEN commission ELSE 0 END) as paid_commission,
            SUM(CASE WHEN level = 0 THEN commission ELSE 0 END) as direct_commission,
            SUM(CASE WHEN level > 0 THEN commission ELSE 0 END) as indirect_commission
        ')
        ->where('user_id', $user_id)
        ->first();
}
```

### 2. Hoa Hồng Theo Thời Gian

```php
function getCommissionByPeriod($user_id, $start_date, $end_date) {
    return MH_Query::init(null, 'affpro_user_order')
        ->select('
            DATE(date) as date,
            SUM(commission) as daily_commission,
            COUNT(*) as daily_orders
        ')
        ->where('user_id', $user_id)
        ->where('status', 1) // Approved only
        ->whereRaw("date BETWEEN '{$start_date}' AND '{$end_date} 23:59:59'")
        ->groupBy('DATE(date)')
        ->orderBy('date', 'DESC')
        ->get();
}
```

### 3. Top Earners

```php
function getTopEarners($limit = 10, $period = 'month') {
    $date_condition = '';
    
    switch ($period) {
        case 'month':
            $date_condition = "AND MONTH(uo.date) = MONTH(NOW()) AND YEAR(uo.date) = YEAR(NOW())";
            break;
        case 'year':
            $date_condition = "AND YEAR(uo.date) = YEAR(NOW())";
            break;
    }
    
    return MH_Query::init(null, 'affpro_user_order uo')
        ->select('
            uo.user_id,
            u.user_login,
            u.display_name,
            SUM(uo.commission) as total_commission,
            COUNT(uo.id) as total_orders
        ')
        ->join('users u', 'uo.user_id', 'u.ID')
        ->where('uo.status', 1)
        ->whereRaw("1=1 {$date_condition}")
        ->groupBy('uo.user_id')
        ->orderBy('total_commission', 'DESC')
        ->limit($limit)
        ->get();
}
```

## 🔍 Tóm Tắt Các Vị Trí Lưu Hoa Hồng

### 💰 **Vị Trí Chính Lưu Hoa Hồng:**

1. **`wp_affpro_user_order.commission`** - Chi tiết từng khoản hoa hồng
2. **`wp_users.balance`** - Số dư khả dụng để rút
3. **`wp_users.income`** - Tổng thu nhập tích lũy
4. **`wp_affpro_history.amount`** - Lịch sử thay đổi số dư
5. **`wp_affpro_payments.amount`** - Số tiền đã rút

### 🔄 **Luồng Dữ Liệu:**

```
Đơn hàng → wp_affpro_user_order (pending)
    ↓
Admin duyệt → wp_users.balance += commission
    ↓
Ghi lịch sử → wp_affpro_history
    ↓
User rút tiền → wp_affpro_payments
    ↓
Trừ số dư → wp_users.balance -= amount
```

### 📈 **Công Thức Tính Toán:**

- **Số dư hiện tại** = `wp_users.balance`
- **Tổng hoa hồng pending** = `SUM(wp_affpro_user_order.commission WHERE status = 0)`
- **Tổng hoa hồng approved** = `SUM(wp_affpro_user_order.commission WHERE status = 1)`
- **Tổng đã rút** = `SUM(wp_affpro_payments.amount WHERE status = 'completed')`

---

*Phân tích chi tiết hệ thống lưu trữ hoa hồng - Created: 2025-06-23*