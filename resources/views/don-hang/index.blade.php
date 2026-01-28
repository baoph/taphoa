@extends('layouts.app')

@section('title', 'Bán hàng')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 d-inline"><i class="fas fa-shopping-cart me-2"></i>Bán hàng ngày</h5>
            <input type="date" id="ngayBan" class="form-control d-inline-block ms-3" style="width: 180px;" value="{{ $ngay }}">
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#donHangModal" onclick="resetForm()">
            <i class="fas fa-plus me-1"></i>Thêm đơn hàng
        </button>
    </div>
    <div class="card-body">
        <!-- Form tìm kiếm -->
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary text-white py-2">
                <i class="fas fa-search me-1"></i>Tìm kiếm
            </div>
            <div class="card-body py-2">
                <form id="searchForm" method="GET" action="{{ route('don-hang.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label for="tuNgay" class="form-label small mb-1">Từ ngày</label>
                            <input type="date" class="form-control form-control-sm" id="tuNgay" name="tu_ngay" value="{{ $tuNgay ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label for="denNgay" class="form-label small mb-1">Đến ngày</label>
                            <input type="date" class="form-control form-control-sm" id="denNgay" name="den_ngay" value="{{ $denNgay ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label for="filterSanPham" class="form-label small mb-1">Tên sản phẩm</label>
                            <select class="form-select form-select-sm" id="filterSanPham" name="san_pham_id" style="width: 100%;">
                                <option value="">-- Tất cả sản phẩm --</option>
                                @foreach($sanPhams as $sp)
                                    <option value="{{ $sp->id }}" {{ ($sanPhamId ?? '') == $sp->id ? 'selected' : '' }}>{{ $sp->ten_san_pham }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                        </div>
                    </div>
                    @if($isFiltering ?? false)
                    <div class="row mt-2">
                        <div class="col-12">
                            <a href="{{ route('don-hang.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times me-1"></i>Xóa bộ lọc
                            </a>
                            <span class="ms-2 text-muted small">
                                <i class="fas fa-info-circle"></i> Đang hiển thị kết quả tìm kiếm
                            </span>
                        </div>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="donHangTable">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Tên sản phẩm</th>
                        <th style="width: 120px;" class="text-center">Số lượng</th>
                        <th style="width: 150px;" class="text-end">Giá</th>
                        <th style="width: 150px;" class="text-end">Thành tiền</th>
                        <th style="width: 120px;" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="donHangBody">
                    @forelse($donHangs as $index => $dh)
                    <tr data-id="{{ $dh->id }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $dh->ten_san_pham }}</td>
                        <td class="text-center">{{ $dh->so_luong }} {{ $dh->donViBan->ten_don_vi ?? '' }}</td>
                        <td class="text-end">{{ number_format($dh->gia, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($dh->so_luong * $dh->gia, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-action" onclick="editDonHang({{ $dh->id }})" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action" onclick="deleteDonHang({{ $dh->id }})" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="6" class="text-center text-muted">Chưa có đơn hàng nào{{ ($isFiltering ?? false) ? ' phù hợp với bộ lọc' : ' trong ngày' }}</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="4" class="text-end">Tổng cộng:</th>
                        <th class="text-end" id="tongTien">{{ number_format($tongTien, 0, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

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
@endsection

@push('scripts')
<script src="{{ asset('js/multi-unit-handler.js') }}"></script>
<script>
    let currentNgay = '{{ $ngay }}';

    $(document).ready(function() {
        // Khởi tạo MultiUnitHandler
        MultiUnitHandler.init();

        // Initialize Select2 cho filter sản phẩm
        $('#filterSanPham').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Tất cả sản phẩm --',
            allowClear: true,
            width: '100%'
        });

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
        const tenSanPham = $('#tenSanPham').find(':selected').text();
        const data = {
            san_pham_id: $('#sanPhamId').val(),
            ten_san_pham: tenSanPham,
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
                const donViTen = dh.don_vi_ban?.ten_don_vi ?? '';
                html += `
                    <tr data-id="${dh.id}">
                        <td>${index + 1}</td>
                        <td>${dh.ten_san_pham}</td>
                        <td class="text-center">${dh.so_luong} ${donViTen}</td>
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
