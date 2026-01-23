# Hướng dẫn cài đặt chức năng Import Excel

## 1. Cài đặt package Laravel Excel

Chạy lệnh sau để cài đặt package `maatwebsite/excel`:

```bash
composer require maatwebsite/excel
```

## 2. Chạy migration

Chạy migration để thay đổi kiểu dữ liệu các trường số lượng từ integer sang decimal:

```bash
php artisan migrate
```

Migration này sẽ thay đổi:
- `so_luong`: integer → decimal(10,2)
- `ti_so_chuyen_doi`: integer → decimal(10,2)
- `so_luong_don_vi`: integer → decimal(10,2)

## 3. Publish config (Tùy chọn)

Nếu muốn tùy chỉnh cấu hình của Laravel Excel:

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

## 4. Cấu hình storage (Nếu chưa có)

Đảm bảo symbolic link đã được tạo:

```bash
php artisan storage:link
```

## 5. Sử dụng

### Truy cập trang Import

Vào trang quản lý sản phẩm và click nút **"Import Excel"** hoặc truy cập trực tiếp:

```
http://your-domain.com/san-pham-import
```

### Format file Excel

File Excel cần có các cột sau (dòng đầu tiên là tiêu đề):

| Hàng | Đơn vị | Giá Nhập Vào | Giá Bán Ra | Giá Bán Lẻ | Số Lượng Hàng | Số lượng đơn vị | Ghi chú |
|------|--------|--------------|------------|------------|---------------|-----------------|---------|
| Coca Cola | Thùng | 120,000 | 150,000 | 7,000 | 10 | 240 | Hàng mới về |
| Mì Hảo Hảo | Thùng | 85,000 | 100,000 | 3,500 | 5.5 | 165 | |

**Lưu ý:**
- Cột **"Hàng"** là bắt buộc
- Số tiền có thể có dấu phẩy (50,000) hoặc không (50000)
- Số lượng có thể là số thập phân (2.5, 0.75)
- Tỉ số chuyển đổi sẽ được tự động tính: `ti_so_chuyen_doi = so_luong_don_vi / so_luong`

### Các tên cột được hỗ trợ

Hệ thống hỗ trợ nhiều tên cột khác nhau:

- **Tên sản phẩm**: `Hàng`, `ten_san_pham`
- **Đơn vị**: `Đơn vị`, `don_vi`, `dvt`, `Đơn vị tính`, `don_vi_tinh`
- **Giá nhập**: `Giá Nhập Vào`, `gia_nhap`
- **Giá bán**: `Giá Bán Ra`, `gia_ban`
- **Giá bán lẻ**: `Giá Bán Lẻ`, `gia_ban_le`
- **Số lượng**: `Số Lượng Hàng`, `so_luong`
- **Số lượng đơn vị**: `Số lượng đơn vị`, `so_luong_don_vi`
- **Ghi chú**: `Ghi chú`, `ghi_chu`

## 6. Xử lý lỗi

Nếu có lỗi trong quá trình import:
- Hệ thống sẽ hiển thị thông báo lỗi chi tiết
- Các dòng hợp lệ vẫn được import thành công
- Kiểm tra lại format file Excel theo hướng dẫn

## 7. Tạo file Excel mẫu

Bạn có thể tạo file Excel mẫu với cấu trúc như sau:

1. Mở Excel/Google Sheets
2. Tạo dòng tiêu đề với các cột: `Hàng`, `Đơn vị`, `Giá Nhập Vào`, `Giá Bán Ra`, `Giá Bán Lẻ`, `Số Lượng Hàng`, `Số lượng đơn vị`, `Ghi chú`
3. Nhập dữ liệu mẫu
4. Lưu file với định dạng `.xlsx` hoặc `.xls`

## 8. Troubleshooting

### Lỗi "Class 'Maatwebsite\Excel\Facades\Excel' not found"

Chạy lại:
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Lỗi upload file quá lớn

Chỉnh sửa file `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

Hoặc trong file `.htaccess` (Apache):
```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

### Lỗi permission khi upload

Đảm bảo thư mục `storage` có quyền ghi:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 9. Tính năng nâng cao

### Xuất dữ liệu ra Excel (Tùy chọn)

Nếu muốn thêm chức năng xuất Excel, có thể tham khảo tài liệu Laravel Excel:
https://docs.laravel-excel.com/3.1/exports/

### Validation nâng cao

Có thể tùy chỉnh validation trong file `app/Imports/SanPhamImport.php` method `rules()`.

## 10. Liên hệ hỗ trợ

Nếu gặp vấn đề, vui lòng tạo issue trên GitHub hoặc liên hệ với team phát triển.
