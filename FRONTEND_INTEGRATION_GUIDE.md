# Hướng dẫn Tích hợp Frontend - Hệ thống Đa Đơn vị

## 📋 Mục lục

1. [Giới thiệu](#giới-thiệu)
2. [Cấu trúc File](#cấu-trúc-file)
3. [Tích hợp Form Bán hàng](#tích-hợp-form-bán-hàng)
4. [Tích hợp Quản lý Đơn vị](#tích-hợp-quản-lý-đơn-vị)
5. [API Endpoints](#api-endpoints)
6. [Troubleshooting](#troubleshooting)
7. [Ví dụ Sử dụng](#ví-dụ-sử-dụng)

---

## Giới thiệu

Hệ thống đa đơn vị cho phép bán sản phẩm theo nhiều đơn vị khác nhau (thùng, lốc, lon, chai...) với tỉ lệ quy đổi và giá bán riêng cho mỗi đơn vị.

### Tính năng chính:

- ✅ Chọn sản phẩm → Load dropdown đơn vị bán
- ✅ Nhập số lượng → Tự động tính tương đương (quy đổi về đơn vị cơ bản)
- ✅ Tự động tính thành tiền
- ✅ Kiểm tra tồn kho trước khi bán
- ✅ Quản lý đơn vị bán: Thêm/Sửa/Xóa
- ✅ Validation đầy đủ

### Ví dụ thực tế:

**Sản phẩm: Bia Tiger**
- Đơn vị cơ bản: `lon`
- Tồn kho: `240 lon`

**Các đơn vị bán:**
- Thùng (24 lon) - 280,000đ
- Lốc (6 lon) - 72,000đ  
- Lon (1 lon) - 12,500đ

**Khi bán:**
- Chọn "Thùng", nhập số lượng 2
- Hệ thống tự động tính: `2 thùng = 48 lon`
- Kiểm tra tồn kho: `48 ≤ 240` ✅
- Thành tiền: `2 × 280,000 = 560,000đ`

---

## Cấu trúc File

```
resources/
├── js/
│   ├── multi-unit-handler.js          # Xử lý logic form bán hàng
│   ├── san-pham-don-vi-manager.js     # Quản lý đơn vị bán
│   └── app.js                          # Entry point (cần import 2 file trên)
├── views/
│   ├── layouts/
│   │   └── app.blade.php               # Layout chính (đã có CSRF token)
│   ├── don-hang/
│   │   └── index.blade.php             # Form bán hàng (CẦN CẬP NHẬT)
│   ├── san-pham/
│   │   ├── index.blade.php             # Danh sách sản phẩm (CẦN CẬP NHẬT)
│   │   └── show.blade.php              # Chi tiết sản phẩm (CẦN CẬP NHẬT)
```

---

## Tích hợp Form Bán hàng

### File: `resources/views/don-hang/index.blade.php`

#### Bước 1: Cập nhật Modal Form

Tìm phần modal `#donHangModal` và thay thế bằng code sau:

```blade
<!-- Modal Thêm/Sửa Đơn hàng -->
<div class="modal fade" id="donHangModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-plus me-2"></i>Thêm đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="donHangForm">
                    <input type="hidden" id="donHangId">
                    <input type="hidden" id="sanPhamId">
                    
                    <!-- Chọn sản phẩm -->
                    <div class="mb-3">
                        <label for="tenSanPham" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <select id="tenSanPham" class="form-select" style="width: 100%;">
                            <option value=""></option>
                        </select>
                    </div>

                    <!-- Chọn đơn vị bán -->
                    <div class="mb-3">
                        <label for="donViBanId" class="form-label">Đơn vị bán <span class="text-danger">*</span></label>
                        <select id="donViBanId" class="form-select" disabled>
                            <option value="">-- Chọn sản phẩm trước --</option>
                        </select>
                        <div class="form-text">Chọn đơn vị bán (thùng, lốc, lon...)</div>
                    </div>

                    <!-- Thông tin tồn kho -->
                    <div class="mb-3" id="tonKhoInfo"></div>

                    <!-- Số lượng -->
                    <div class="mb-3">
                        <label for="soLuong" class="form-label">Số lượng <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="soLuong" min="1" value="1" required>
                    </div>

                    <!-- Hiển thị tương đương -->
                    <div class="mb-3">
                        <div class="alert alert-secondary py-2">
                            <i class="fas fa-calculator"></i> 
                            <strong>Tương đương:</strong> 
                            <span id="tuongDuong" class="text-primary fw-bold">0</span> 
                            <span id="donViCoBan" class="text-muted"></span>
                        </div>
                    </div>

                    <!-- Giá bán -->
                    <div class="mb-3">
                        <label for="giaBan" class="form-label">Giá bán (đ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="giaBan" min="0" value="0" required>
                        <div class="form-text">Giá sẽ tự động điền theo đơn vị, có thể chỉnh sửa</div>
                    </div>

                    <!-- Thành tiền -->
                    <div class="mb-3">
                        <label class="form-label">Thành tiền:</label>
                        <div class="form-control-plaintext fw-bold text-primary fs-5" id="thanhTienPreview">0đ</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Hủy
                </button>
                <button type="button" class="btn btn-primary" onclick="saveDonHang()">
                    <i class="fas fa-save me-1"></i>Lưu
                </button>
            </div>
        </div>
    </div>
</div>
```

#### Bước 2: Include JavaScript

Thêm vào cuối file, trong section `@push('scripts')`:

```blade
@push('scripts')
<script src="{{ asset('js/multi-unit-handler.js') }}"></script>
<script>
    let currentNgay = '{{ $ngay }}';

    $(document).ready(function() {
        // Khởi tạo MultiUnitHandler
        MultiUnitHandler.init();

        // Initialize Select2 cho sản phẩm
        $('#tenSanPham').select2({
            dropdownParent: $('#donHangModal'),
            theme: 'bootstrap-5',
            placeholder: 'Tìm tên sản phẩm...',
            allowClear: true,
            ajax: {
                url: '{{ route("san-pham.search") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return data;
                },
                cache: true
            }
        });

        // Change date
        $('#ngayBan').on('change', function() {
            currentNgay = $(this).val();
            loadDonHang();
        });
    });

    function resetForm() {
        $('#donHangId').val('');
        $('#tenSanPham').val(null).trigger('change');
        $('#soLuong').val(1);
        $('#giaBan').val(0);
        $('#thanhTienPreview').text('0đ');
        $('#modalTitle').html('<i class="fas fa-plus me-2"></i>Thêm đơn hàng');
        
        // Reset MultiUnitHandler
        MultiUnitHandler.resetForm();
    }

    function saveDonHang() {
        // Validate bằng MultiUnitHandler
        if (!MultiUnitHandler.validateBeforeSubmit()) {
            return;
        }

        const id = $('#donHangId').val();
        const data = {
            san_pham_id: $('#sanPhamId').val(),
            don_vi_ban_id: $('#donViBanId').val(),
            so_luong: $('#soLuong').val(),
            gia: $('#giaBan').val(),
            ngay_ban: currentNgay
        };

        const url = id ? `/don-hang/${id}` : '{{ route("don-hang.store") }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#donHangModal').modal('hide');
                    loadDonHang();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    toastr.error(Object.values(errors).flat().join('<br>'));
                } else {
                    toastr.error('Có lỗi xảy ra!');
                }
            }
        });
    }

    function loadDonHang() {
        $.get('{{ route("don-hang.index") }}', { ngay: currentNgay }, function(response) {
            if (response.donHangs) {
                renderTable(response.donHangs, response.tongTien);
                window.history.pushState({}, '', '{{ route("don-hang.index") }}?ngay=' + currentNgay);
            }
        });
    }

    function renderTable(data, tongTien) {
        let html = '';
        if (data.length === 0) {
            html = '<tr id="emptyRow"><td colspan="6" class="text-center text-muted">Chưa có đơn hàng nào trong ngày</td></tr>';
        } else {
            data.forEach(function(dh, index) {
                html += `
                    <tr data-id="${dh.id}">
                        <td>${index + 1}</td>
                        <td>${dh.ten_san_pham}</td>
                        <td class="text-center">${dh.so_luong}</td>
                        <td class="text-end">${formatNumber(dh.gia)}</td>
                        <td class="text-end">${formatNumber(dh.so_luong * dh.gia)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-action" onclick="editDonHang(${dh.id})" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action" onclick="deleteDonHang(${dh.id})" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#donHangBody').html(html);
        $('#tongTien').text(formatNumber(tongTien));
    }

    function editDonHang(id) {
        $.get(`/don-hang/${id}`, function(response) {
            if (response.success) {
                const dh = response.data;
                $('#donHangId').val(dh.id);
                $('#sanPhamId').val(dh.san_pham_id || '');
                
                const option = new Option(dh.ten_san_pham, dh.san_pham_id || dh.ten_san_pham, true, true);
                $('#tenSanPham').append(option).trigger('change');
                
                $('#soLuong').val(dh.so_luong);
                $('#giaBan').val(dh.gia);
                $('#modalTitle').html('<i class="fas fa-edit me-2"></i>Sửa đơn hàng');
                $('#donHangModal').modal('show');
            }
        });
    }

    function deleteDonHang(id) {
        if (!confirm('Bạn có chắc muốn xóa đơn hàng này?')) return;

        $.ajax({
            url: `/don-hang/${id}`,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    loadDonHang();
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('Có lỗi xảy ra!');
            }
        });
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }
</script>
@endpush
```

---

## Tích hợp Quản lý Đơn vị

### File: `resources/views/san-pham/show.blade.php`

Thay thế toàn bộ nội dung bằng:

```blade
@extends('layouts.app')

@section('title', 'Chi tiết Sản phẩm')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Chi tiết Sản phẩm: {{ $sanPham->ten_san_pham }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('san-pham.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('san-pham.edit', $sanPham) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin cơ bản -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Tên sản phẩm:</th>
                            <td>{{ $sanPham->ten_san_pham }}</td>
                        </tr>
                        <tr>
                            <th>Đơn vị tính:</th>
                            <td>{{ $sanPham->dvt }}</td>
                        </tr>
                        <tr>
                            <th>Đơn vị cơ bản:</th>
                            <td><span class="badge bg-primary">{{ $sanPham->don_vi_co_ban }}</span></td>
                        </tr>
                        <tr>
                            <th>Giá nhập:</th>
                            <td>{{ number_format($sanPham->gia_nhap) }} đ</td>
                        </tr>
                        <tr>
                            <th>Giá bán:</th>
                            <td>{{ number_format($sanPham->gia_ban) }} đ</td>
                        </tr>
                        <tr>
                            <th>Giá bán lẻ:</th>
                            <td>{{ number_format($sanPham->gia_ban_le) }} đ</td>
                        </tr>
                        <tr>
                            <th>Tồn kho:</th>
                            <td>
                                <strong class="text-success fs-5">
                                    {{ $sanPham->so_luong }} {{ $sanPham->don_vi_co_ban }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Ghi chú:</th>
                            <td>{{ $sanPham->ghi_chu ?? 'Không có' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Đơn vị bán & Giá -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Đơn vị Bán & Giá</h5>
                    <button class="btn btn-sm btn-primary" id="btnAddDonVi">
                        <i class="bi bi-plus-circle"></i> Thêm đơn vị
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Đơn vị</th>
                                    <th>Tỉ lệ quy đổi</th>
                                    <th class="text-end">Giá bán</th>
                                    <th class="text-center" style="width: 120px;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="donViTableBody">
                                @forelse($sanPham->sanPhamDonVi as $index => $spDonVi)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $spDonVi->donViBan->ten_don_vi }}</strong></td>
                                        <td>{{ $spDonVi->ti_le_quy_doi }} {{ $sanPham->don_vi_co_ban }}</td>
                                        <td class="text-end">{{ number_format($spDonVi->gia_ban) }} đ</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="SanPhamDonViManager.showEditDonViModal({{ $spDonVi->id }})" 
                                                    title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="SanPhamDonViManager.deleteDonVi({{ $spDonVi->id }})" 
                                                    title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Chưa có đơn vị bán nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Lưu ý:</strong> Đơn vị bán giúp bạn bán sản phẩm theo nhiều cách khác nhau 
                        (thùng, lốc, lon...). Tỉ lệ quy đổi sẽ tự động tính số lượng tồn kho.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Đơn vị -->
<div class="modal fade" id="donViModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="donViModalTitle">
                    <i class="bi bi-plus-circle"></i> Thêm đơn vị bán mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="donViForm">
                    <!-- Chọn đơn vị -->
                    <div class="mb-3">
                        <label for="donViBanIdSelect" class="form-label">
                            Đơn vị <span class="text-danger">*</span>
                        </label>
                        <select id="donViBanIdSelect" class="form-select" required>
                            <option value="">-- Chọn đơn vị --</option>
                        </select>
                        <div class="form-text">Ví dụ: Thùng, Lốc, Chai, Hộp...</div>
                    </div>

                    <!-- Tỉ lệ quy đổi -->
                    <div class="mb-3">
                        <label for="tiLeQuyDoi" class="form-label">
                            Tỉ lệ quy đổi <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="tiLeQuyDoi" 
                                   min="0.01" step="0.01" placeholder="Ví dụ: 24" required>
                            <span class="input-group-text">{{ $sanPham->don_vi_co_ban }}</span>
                        </div>
                        <div class="form-text">
                            Ví dụ: 1 Thùng = 24 {{ $sanPham->don_vi_co_ban }}
                        </div>
                    </div>

                    <!-- Giá bán -->
                    <div class="mb-3">
                        <label for="giaBanDonVi" class="form-label">
                            Giá bán <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="giaBanDonVi" 
                                   min="0" step="1000" placeholder="Ví dụ: 280000" required>
                            <span class="input-group-text">đ</span>
                        </div>
                        <div class="form-text text-primary" id="giaBanSuggestion"></div>
                    </div>

                    <input type="hidden" id="giaBanLeHidden" value="{{ $sanPham->gia_ban_le }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Hủy
                </button>
                <button type="button" class="btn btn-primary" id="btnSaveDonVi">
                    <i class="fas fa-save"></i> Lưu
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/san-pham-don-vi-manager.js') }}"></script>
<script>
    $(document).ready(function() {
        // Khởi tạo SanPhamDonViManager
        SanPhamDonViManager.init({{ $sanPham->id }});
    });
</script>
@endpush
```

### File: `resources/views/san-pham/index.blade.php`

Thêm cột "Đơn vị bán" vào bảng. Tìm phần `<thead>` và thêm:

```blade
<th style="width: 100px;">Đơn vị bán</th>
```

Trong `<tbody>`, thêm:

```blade
<td>
    @if($sp->sanPhamDonVi->count() > 0)
        <span class="badge bg-success">{{ $sp->sanPhamDonVi->count() }} đơn vị</span>
    @else
        <span class="badge bg-secondary">Chưa có</span>
    @endif
</td>
```

Thêm nút "Quản lý đơn vị" trong cột "Thao tác":

```blade
<a href="{{ route('san-pham.show', $sp) }}" class="btn btn-info btn-action" title="Quản lý đơn vị">
    <i class="bi bi-box-seam"></i>
</a>
```

---

## API Endpoints

### 1. Lấy danh sách đơn vị bán của sản phẩm

```
GET /api/san-pham/{id}/don-vi-options
```

**Response:**
```json
{
  "success": true,
  "data": {
    "san_pham": {
      "id": 1,
      "ten_san_pham": "Bia Tiger",
      "don_vi_co_ban": "lon",
      "so_luong": 240
    },
    "don_vi_list": [
      {
        "id": 1,
        "ten_don_vi": "Thùng",
        "ti_le_quy_doi": 24,
        "gia_ban": 280000,
        "don_vi_co_ban": "lon"
      },
      {
        "id": 2,
        "ten_don_vi": "Lốc",
        "ti_le_quy_doi": 6,
        "gia_ban": 72000,
        "don_vi_co_ban": "lon"
      }
    ]
  }
}
```

### 2. Lấy danh sách tất cả đơn vị bán (từ bảng don_vi_ban)

```
GET /api/don-vi-ban
```

**Response:**
```json
{
  "success": true,
  "data": [
    {"id": 1, "ten_don_vi": "Thùng"},
    {"id": 2, "ten_don_vi": "Lốc"},
    {"id": 3, "ten_don_vi": "Lon"},
    {"id": 4, "ten_don_vi": "Chai"}
  ]
}
```

### 3. Thêm đơn vị bán cho sản phẩm

```
POST /api/san-pham-don-vi
```

**Request Body:**
```json
{
  "san_pham_id": 1,
  "don_vi_ban_id": 1,
  "ti_le_quy_doi": 24,
  "gia_ban": 280000
}
```

**Response:**
```json
{
  "success": true,
  "message": "Thêm đơn vị bán thành công",
  "data": {
    "id": 1,
    "san_pham_id": 1,
    "don_vi_ban_id": 1,
    "ti_le_quy_doi": 24,
    "gia_ban": 280000
  }
}
```

### 4. Cập nhật đơn vị bán

```
PUT /api/san-pham-don-vi/{id}
```

**Request Body:** (giống POST)

### 5. Xóa đơn vị bán

```
DELETE /api/san-pham-don-vi/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Xóa đơn vị bán thành công"
}
```

### 6. Lấy chi tiết đơn vị bán

```
GET /api/san-pham-don-vi/{id}
```

### 7. Lấy danh sách đơn vị của sản phẩm (để refresh table)

```
GET /api/san-pham/{id}/don-vi-list
```

---

## Troubleshooting

### Lỗi: "Không thể load đơn vị bán"

**Nguyên nhân:** API endpoint chưa được tạo hoặc route chưa đúng.

**Giải pháp:**
1. Kiểm tra file `routes/api.php` có các route sau:
   ```php
   Route::get('/san-pham/{id}/don-vi-options', [SanPhamController::class, 'getDonViOptions']);
   Route::get('/don-vi-ban', [DonViBanController::class, 'index']);
   Route::apiResource('san-pham-don-vi', SanPhamDonViController::class);
   ```

2. Chạy lệnh:
   ```bash
   php artisan route:list | grep api
   ```

### Lỗi: "CSRF token mismatch"

**Nguyên nhân:** CSRF token không được gửi trong AJAX request.

**Giải pháp:**
1. Kiểm tra `layouts/app.blade.php` có meta tag:
   ```html
   <meta name="csrf-token" content="{{ csrf_token() }}">
   ```

2. Kiểm tra AJAX setup:
   ```javascript
   $.ajaxSetup({
       headers: {
           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
       }
   });
   ```

### Lỗi: "Vượt quá tồn kho"

**Nguyên nhân:** Số lượng quy đổi lớn hơn tồn kho.

**Giải pháp:**
- Kiểm tra lại tồn kho trong database
- Kiểm tra tỉ lệ quy đổi có đúng không
- Nhập kho thêm sản phẩm

### Dropdown đơn vị bị disabled

**Nguyên nhân:** Chưa chọn sản phẩm hoặc sản phẩm chưa có đơn vị bán.

**Giải pháp:**
1. Chọn sản phẩm trước
2. Nếu sản phẩm chưa có đơn vị bán, vào trang chi tiết sản phẩm để thêm

### JavaScript không hoạt động

**Nguyên nhân:** File JS chưa được compile hoặc đường dẫn sai.

**Giải pháp:**
1. Nếu dùng Laravel Mix/Vite:
   ```bash
   npm run dev
   # hoặc
   npm run build
   ```

2. Kiểm tra đường dẫn file:
   ```blade
   <script src="{{ asset('js/multi-unit-handler.js') }}"></script>
   ```

3. Kiểm tra file có tồn tại:
   ```bash
   ls -la public/js/
   ```

---

## Ví dụ Sử dụng

### Ví dụ 1: Thêm đơn vị bán cho sản phẩm "Bia Tiger"

1. Vào trang **Sản phẩm** → Click vào "Bia Tiger"
2. Trong tab "Đơn vị Bán & Giá", click **"Thêm đơn vị"**
3. Điền thông tin:
   - Đơn vị: `Thùng`
   - Tỉ lệ quy đổi: `24` (1 thùng = 24 lon)
   - Giá bán: `280000`
4. Click **"Lưu"**

### Ví dụ 2: Bán hàng với đơn vị "Thùng"

1. Vào trang **Bán hàng**
2. Click **"Thêm đơn hàng"**
3. Chọn sản phẩm: `Bia Tiger`
4. Dropdown "Đơn vị bán" tự động load → Chọn `Thùng (24 lon) - 280,000đ`
5. Nhập số lượng: `2`
6. Hệ thống tự động:
   - Tương đương: `48 lon`
   - Thành tiền: `560,000đ`
   - Kiểm tra tồn kho: ✅ (nếu đủ)
7. Click **"Lưu"**

### Ví dụ 3: Xử lý khi vượt tồn kho

**Tình huống:** Tồn kho còn 20 lon, bán 2 thùng (48 lon)

**Kết quả:**
- Hiển thị cảnh báo màu đỏ: "Vượt quá tồn kho! (Còn 20 lon)"
- Không cho phép lưu
- Đề xuất: Giảm số lượng hoặc chọn đơn vị nhỏ hơn

---

## Checklist Tích hợp

- [ ] Copy 2 file JavaScript vào `resources/js/`
- [ ] Cập nhật `resources/views/don-hang/index.blade.php`
- [ ] Cập nhật `resources/views/san-pham/show.blade.php`
- [ ] Cập nhật `resources/views/san-pham/index.blade.php`
- [ ] Compile JavaScript (nếu dùng Mix/Vite)
- [ ] Test API endpoints
- [ ] Test form bán hàng
- [ ] Test quản lý đơn vị
- [ ] Test validation
- [ ] Test kiểm tra tồn kho

---

## Liên hệ & Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra Console log (F12 → Console)
2. Kiểm tra Network tab (F12 → Network) để xem API response
3. Kiểm tra Laravel log: `storage/logs/laravel.log`

---

**Phiên bản:** 1.0  
**Ngày cập nhật:** 2026-01-28  
**Tác giả:** Backend Team
