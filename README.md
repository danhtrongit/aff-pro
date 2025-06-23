# AFF Pro - Hệ thống Affiliate Marketing Chuyên nghiệp

AFF Pro là một WordPress plugin mạnh mẽ được thiết kế để xây dựng hệ thống affiliate marketing hoàn chỉnh cho WooCommerce. Plugin cung cấp đầy đủ các tính năng cần thiết để quản lý cộng tác viên, theo dõi hiệu suất và tính toán hoa hồng một cách tự động.

## ✨ Tính năng chính

### 🎯 Quản lý Affiliate
- Đăng ký và quản lý tài khoản affiliate
- Hệ thống phân cấp (parent-child relationship)
- Kích hoạt/vô hiệu hóa tài khoản
- Dashboard chuyên nghiệp cho affiliate

### 💰 Hệ thống Hoa hồng
- Cài đặt tỷ lệ hoa hồng linh hoạt theo sản phẩm/danh mục
- Tính toán hoa hồng tự động
- Hỗ trợ nhiều cấp độ hoa hồng
- Lịch sử chi tiết các khoản hoa hồng

### 📊 Theo dõi & Báo cáo
- Tracking clicks và conversions
- Thống kê hiệu suất affiliate
- Báo cáo chi tiết theo thời gian
- Dashboard với biểu đồ trực quan

### 💳 Quản lý Thanh toán
- Hệ thống thanh toán tự động
- Tích hợp MoMo payment
- Lịch sử giao dịch
- Quản lý số dư affiliate

### 🎨 Marketing Tools
- Hệ thống banner quảng cáo
- Link affiliate tự động
- Mã giảm giá (coupon codes)
- Email notifications

## 🚀 Cài đặt

1. Upload thư mục `aff-pro` vào `/wp-content/plugins/`
2. Kích hoạt plugin trong WordPress Admin
3. Truy cập menu "AFF Pro" để cấu hình

## 📋 Yêu cầu hệ thống

- WordPress 3.0.1+
- WooCommerce
- PHP 7.4+ (khuyến nghị PHP 8.0+)
- MySQL 5.6+

## 🛠️ Công nghệ sử dụng

- **Backend**: PHP với WordPress API
- **Frontend**: Vue.js + Quasar UI Framework
- **Database**: MySQL với custom tables
- **AJAX**: Real-time interactions

## 📖 Hướng dẫn sử dụng

### Cấu hình cơ bản
1. Truy cập **AFF Pro > Cài đặt**
2. Cấu hình tỷ lệ hoa hồng mặc định
3. Thiết lập phương thức thanh toán
4. Cấu hình email notifications

### Quản lý Affiliate
1. Truy cập **AFF Pro > Tài khoản**
2. Thêm/sửa/xóa affiliate
3. Thiết lập mối quan hệ phân cấp
4. Kích hoạt tài khoản

### Theo dõi hiệu suất
1. Truy cập **AFF Pro > Dashboard**
2. Xem thống kê tổng quan
3. Phân tích báo cáo chi tiết
4. Theo dõi traffic và conversions

## 🔧 Cấu hình nâng cao

### Custom Hooks
```php
// Hook khi có đơn hàng mới
add_action('affpro_new_order', 'custom_new_order_handler');

// Hook khi tính hoa hồng
add_filter('affpro_calculate_commission', 'custom_commission_calculator');
```

### Shortcodes
```php
// Hiển thị dashboard affiliate
[affpro_dashboard]

// Hiển thị form đăng ký
[affpro_register]

// Hiển thị banner
[affpro_banner id="1"]
```

## 🤝 Đóng góp

Chúng tôi hoan nghênh mọi đóng góp để cải thiện AFF Pro:

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📝 License

Dự án này được phân phối dưới giấy phép GPL-2.0+. Xem file `LICENSE.txt` để biết thêm chi tiết.

## 📞 Hỗ trợ

- **Website**: https://affpro.dev
- **Documentation**: https://docs.affpro.dev
- **Support**: support@affpro.dev

## 🔄 Changelog

### v1.0.0
- Phiên bản đầu tiên
- Đầy đủ tính năng cơ bản
- Giao diện Vue.js + Quasar
- Tích hợp WooCommerce

---

**AFF Pro** - Giải pháp affiliate marketing chuyên nghiệp cho WordPress & WooCommerce.