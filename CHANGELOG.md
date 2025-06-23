# Changelog

Tất cả các thay đổi quan trọng của dự án AFF Pro sẽ được ghi lại trong file này.

## [1.0.0] - 2024-01-01

### 🔄 Refactoring hoàn toàn
- **BREAKING CHANGE**: Đổi tên từ "VuaCode AFF" thành "AFF Pro"
- Cập nhật toàn bộ branding và thông tin plugin

### 📁 Cấu trúc file
- Đổi tên file chính: `vuacode-aff.php` → `aff-pro.php`
- Đổi tên tất cả file trong `includes/`: `class-vuacode-aff*.php` → `class-aff-pro*.php`
- Đổi tên tất cả file trong `public/` và `admin/`: `vuacode-aff` → `aff-pro`
- Đổi tên file ngôn ngữ: `vuacode-aff.pot` → `aff-pro.pot`

### 🏗️ Cấu trúc code
- **Class names**: `WP_VuaCode_AFF` → `AFF_Pro`
- **Function names**: 
  - `activate_WP_VuaCode_AFF()` → `activate_AFF_Pro()`
  - `deactivate_WP_VuaCode_AFF()` → `deactivate_AFF_Pro()`
  - `run_WP_VuaCode_AFF()` → `run_AFF_Pro()`
  - `custom_rewrite_basic_angularApp()` → `aff_pro_custom_rewrite_rules()`
- **Constants**: 
  - Thêm `AFF_PRO_URL`, `AFF_PRO_PATH`
  - Giữ lại `AFF_URL`, `AFF_PATH` để tương thích ngược

### 🗄️ Database
- **Table names**: `vuacode_*` → `affpro_*`
  - `vuacode_banners` → `affpro_banners`
  - `vuacode_commission_settings` → `affpro_commission_settings`
  - `vuacode_configs` → `affpro_configs`
  - `vuacode_coupons` → `affpro_coupons`
  - `vuacode_history` → `affpro_history`
  - `vuacode_payments` → `affpro_payments`
  - `vuacode_traffics` → `affpro_traffics`
  - `vuacode_user_order` → `affpro_user_order`
  - `vuacode_user_relationships` → `affpro_user_relationships`

### 🎨 Branding
- **Plugin Name**: "VuaCode AFF" → "AFF Pro"
- **Description**: Cập nhật mô tả plugin
- **Author**: "Đỗ Minh Hải" → "AFF Pro Team"
- **Author URI**: "vuacode.io" → "affpro.dev"
- **Text Domain**: "vuacode-aff" → "aff-pro"
- **Version**: Reset về 1.0.0

### 📝 Documentation
- Thêm comment tiếng Việt đầy đủ cho tất cả functions và classes
- Tạo README.md hoàn chỉnh với hướng dẫn sử dụng
- Cải thiện PHPDoc cho tất cả methods

### 🔒 Security
- Cải thiện prepared statements trong database queries
- Thêm validation và sanitization

### 🧹 Code Quality
- Chuẩn hóa coding style
- Loại bỏ code không sử dụng
- Cải thiện error handling
- Thêm type hints cho PHP 8+

### 🔧 Technical Improvements
- Hỗ trợ PHP 8.1+ với class riêng biệt
- Cải thiện performance
- Tối ưu hóa database queries
- Thêm caching mechanisms

### 🎯 Features
- Giữ nguyên tất cả tính năng hiện có
- Cải thiện UX/UI
- Thêm error messages tiếng Việt
- Cải thiện responsive design

### 🔄 Backward Compatibility
- Giữ lại các constants cũ để tương thích
- Migration script cho database tables
- Hỗ trợ import dữ liệu từ version cũ

---

## Migration Guide

### Từ VuaCode AFF sang AFF Pro

1. **Backup dữ liệu**: Sao lưu database và files trước khi cập nhật
2. **Deactivate plugin cũ**: Vô hiệu hóa VuaCode AFF
3. **Install AFF Pro**: Cài đặt plugin mới
4. **Data migration**: Plugin sẽ tự động migrate dữ liệu từ tables cũ
5. **Update customizations**: Cập nhật custom code nếu có

### Breaking Changes

- Tất cả class names đã thay đổi
- Database table names đã thay đổi
- Text domain đã thay đổi
- File paths đã thay đổi

### Compatibility

- WordPress 3.0.1+
- WooCommerce 3.0+
- PHP 7.4+ (khuyến nghị PHP 8.0+)
- MySQL 5.6+