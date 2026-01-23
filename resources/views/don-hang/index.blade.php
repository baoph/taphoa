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
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="donHangTable">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Tên sản phẩm</th>
                        <th style="width: 100px;" class="text-center">Số lượng</th>
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
                        <td class="text-center">{{ $dh->so_luong }}</td>
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
                        <td colspan="6" class="text-center text-muted">Chưa có đơn hàng nào trong ngày</td>
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
                    
                    <div class="mb-3">
                        <label for="tenSanPham" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <select id="tenSanPham" class="form-select" style="width: 100%;">
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="soLuong" class="form-label">
                                Số lượng <span id="dvtLabel" class="text-muted"></span> <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="soLuong" min="1" value="1" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="giaBan" class="form-label">Giá bán (đ) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="giaBan" min="0" value="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thành tiền:</label>
                        <div class="form-control-plaintext fw-bold text-primary" id="thanhTienPreview">0 đ</div>
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
<script>
    let currentNgay = '{{ $ngay }}';
    let currentDvt = '';

    $(document).ready(function() {
        // Initialize Select2
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
            },
            tags: true,
            createTag: function(params) {
                return {
                    id: params.term,
                    text: params.term,
                    newTag: true
                };
            }
        });

        // When select product, auto-fill price and show DVT
        $('#tenSanPham').on('select2:select', function(e) {
            const data = e.params.data;
            
            // Lưu san_pham_id nếu có
            if (data.id && !data.newTag) {
                $('#sanPhamId').val(data.id);
            } else {
                $('#sanPhamId').val('');
            }
            
            // Auto-fill giá bán
            if (data.gia_ban) {
                $('#giaBan').val(data.gia_ban);
                updateThanhTien();
            }
            
            // Hiển thị đơn vị tính
            if (data.dvt) {
                currentDvt = data.dvt;
                $('#dvtLabel').text('(' + data.dvt + ')');
            } else {
                currentDvt = '';
                $('#dvtLabel').text('');
            }
        });

        // Update thanh tien preview
        $('#soLuong, #giaBan').on('input', updateThanhTien);

        // Change date
        $('#ngayBan').on('change', function() {
            currentNgay = $(this).val();
            loadDonHang();
        });
    });

    function updateThanhTien() {
        const soLuong = parseInt($('#soLuong').val()) || 0;
        const gia = parseInt($('#giaBan').val()) || 0;
        const thanhTien = soLuong * gia;
        $('#thanhTienPreview').text(formatNumber(thanhTien) + ' đ');
    }

    function resetForm() {
        $('#donHangId').val('');
        $('#sanPhamId').val('');
        $('#tenSanPham').val(null).trigger('change');
        $('#soLuong').val(1);
        $('#giaBan').val(0);
        $('#thanhTienPreview').text('0 đ');
        $('#dvtLabel').text('');
        currentDvt = '';
        $('#modalTitle').html('<i class="fas fa-plus me-2"></i>Thêm đơn hàng');
    }

    function loadDonHang() {
        $.get('{{ route("don-hang.index") }}', { ngay: currentNgay }, function(response) {
            if (response.donHangs) {
                renderTable(response.donHangs, response.tongTien);
                // Update URL
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

    function saveDonHang() {
        const id = $('#donHangId').val();
        const sanPhamId = $('#sanPhamId').val();
        const tenSanPham = $('#tenSanPham').val();
        const soLuong = $('#soLuong').val();
        const gia = $('#giaBan').val();

        if (!tenSanPham) {
            toastr.warning('Vui lòng chọn sản phẩm!');
            return;
        }

        const data = {
            san_pham_id: sanPhamId || null,
            ten_san_pham: tenSanPham,
            so_luong: soLuong,
            gia: gia,
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

    function editDonHang(id) {
        $.get(`/don-hang/${id}`, function(response) {
            if (response.success) {
                const dh = response.data;
                $('#donHangId').val(dh.id);
                $('#sanPhamId').val(dh.san_pham_id || '');
                
                // Set Select2 value
                const option = new Option(dh.ten_san_pham, dh.san_pham_id || dh.ten_san_pham, true, true);
                $('#tenSanPham').append(option).trigger('change');
                
                $('#soLuong').val(dh.so_luong);
                $('#giaBan').val(dh.gia);
                updateThanhTien();
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
