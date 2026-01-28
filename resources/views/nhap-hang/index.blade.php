@extends('layouts.app')

@section('title', 'Nhập hàng')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 d-inline"><i class="fas fa-truck-loading me-2"></i>Nhập hàng ngày</h5>
            <input type="date" id="ngayNhap" class="form-control d-inline-block ms-3" style="width: 180px;" value="{{ $ngay }}">
        </div>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#nhapHangModal" onclick="resetForm()">
            <i class="fas fa-plus me-1"></i>Thêm nhập hàng
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="nhapHangTable">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Tên sản phẩm</th>
                        <th style="width: 100px;" class="text-center">Số lượng</th>
                        <th style="width: 150px;" class="text-end">Giá nhập</th>
                        <th style="width: 150px;" class="text-end">Thành tiền</th>
                        <th style="width: 120px;" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="nhapHangBody">
                    @forelse($nhapHangs as $index => $nh)
                    <tr data-id="{{ $nh->id }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $nh->ten_san_pham }}</td>
                        <td class="text-center">{{ $nh->so_luong }} {{ $nh->donViBan->ten_don_vi ?? '' }}</td>
                        <td class="text-end">{{ number_format($nh->gia_nhap, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($nh->so_luong * $nh->gia_nhap, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-action" onclick="editNhapHang({{ $nh->id }})" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action" onclick="deleteNhapHang({{ $nh->id }})" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRow">
                        <td colspan="6" class="text-center text-muted">Chưa có nhập hàng nào trong ngày</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-success">
                        <th colspan="4" class="text-end">Tổng tiền nhập:</th>
                        <th class="text-end" id="tongTien">{{ number_format($tongTien, 0, ',', '.') }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Nhập hàng -->
<div class="modal fade" id="nhapHangModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-plus me-2"></i>Thêm nhập hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="nhapHangForm">
                    <input type="hidden" id="nhapHangId">
                    <input type="hidden" id="sanPhamId">

                    <!-- Chọn sản phẩm -->
                    <div class="mb-3">
                        <label for="tenSanPham" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <select id="tenSanPham" class="form-select" style="width: 100%;">
                            <option value=""></option>
                        </select>
                    </div>

                    <!-- Chọn đơn vị nhập -->
                    <div class="mb-3">
                        <label for="donViBanId" class="form-label">Đơn vị nhập <span class="text-danger">*</span></label>
                        <select id="donViBanId" class="form-select" disabled>
                            <option value="">-- Chọn sản phẩm trước --</option>
                        </select>
                        <div class="form-text">Chọn đơn vị nhập (thùng, lốc, lon...)</div>
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

                    <!-- Giá nhập -->
                    <div class="mb-3">
                        <label for="giaNhap" class="form-label">Giá nhập (đ) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="giaNhap" min="0" value="0" required>
                        <div class="form-text">Nhập giá nhập hàng</div>
                    </div>

                    <!-- Thành tiền -->
                    <div class="mb-3">
                        <label class="form-label">Thành tiền:</label>
                        <div class="form-control-plaintext fw-bold text-success fs-5" id="thanhTienPreview">0đ</div>
                    </div>

                    <!-- Ghi chú -->
                    <div class="mb-3">
                        <label for="ghiChu" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="ghiChu" rows="2" placeholder="Ghi chú thêm (không bắt buộc)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Hủy
                </button>
                <button type="button" class="btn btn-success" onclick="saveNhapHang()">
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
    let currentTiLeQuyDoi = 1;
    let selectedSanPhamId = null;
    let currentDonViCoBan = '';


    $(document).ready(function() {
        // Initialize Select2 cho sản phẩm
        $('#tenSanPham').select2({
            dropdownParent: $('#nhapHangModal'),
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

        // Khi chọn sản phẩm
        $('#tenSanPham').on('select2:select', function(e) {
            const data = e.params.data;
            selectedSanPhamId = data.id;
            $('#sanPhamId').val(data.id);

            // Load đơn vị bán của sản phẩm
            loadDonViBanOptions(data.id);
        });

        // Khi clear sản phẩm
        $('#tenSanPham').on('select2:clear', function() {
            selectedSanPhamId = null;
            $('#sanPhamId').val('');
            $('#donViBanId').prop('disabled', true).html('<option value="">-- Chọn sản phẩm trước --</option>');
            $('#tonKhoInfo').html('');
            resetCalculation();
        });

        // Khi thay đổi đơn vị bán
        $('#donViBanId').on('change', function() {
            const donViBanId = $(this).val();
            if (donViBanId && selectedSanPhamId) {
                // Lấy tỉ lệ quy đổi
                const option = $(this).find(':selected');
                currentTiLeQuyDoi = parseInt(option.data('ti-le')) || 1;
                updateCalculation();
            } else {
                currentTiLeQuyDoi = 1;
                updateCalculation();
            }
        });

        // Khi thay đổi số lượng hoặc giá
        $('#soLuong, #giaNhap').on('input', function() {
            updateCalculation();
        });

        // Change date
        $('#ngayNhap').on('change', function() {
            currentNgay = $(this).val();
            loadNhapHang();
        });
    });

    function loadDonViBanOptions(sanPhamId) {
        $.get(`/san-pham-don-vi/${sanPhamId}/options`, function(response) {
            if (response.success) {
                let html = '<option value="">-- Chọn đơn vị --</option>';
                currentDonViCoBan = response.data.san_pham.don_vi_co_ban_text;
                response.data.don_vi_list.forEach(function(item){
                    html += `<option value="${item.don_vi_ban_id}" data-ti-le="${item.ti_le_quy_doi}" data-gia="${item.gia_ban}">${item.ten_don_vi} (${item.ti_le_quy_doi} ${currentDonViCoBan})</option>`;
                });
                $('#donViBanId').prop('disabled', false).html(html);

                // Hiển thị thông tin tồn kho
                if (response.ton_kho_hien_thi) {
                    $('#tonKhoInfo').html(`<div class="alert alert-info py-2"><i class="fas fa-boxes"></i> Tồn kho hiện tại: <strong>${response.ton_kho_hien_thi}</strong></div>`);
                }
            }
        });
    }

    function updateCalculation() {
        const soLuong = parseFloat($('#soLuong').val()) || 0;
        const giaNhap = parseFloat($('#giaNhap').val()) || 0;

        // Tính tương đương
        const tuongDuong = soLuong * currentTiLeQuyDoi;
        $('#tuongDuong').text(tuongDuong);
        $('#donViCoBan').text(currentDonViCoBan);

        // Tính thành tiền
        const thanhTien = soLuong * giaNhap;
        $('#thanhTienPreview').text(formatNumber(thanhTien) + 'đ');
    }

    function resetCalculation() {
        currentTiLeQuyDoi = 1;
        $('#tuongDuong').text('0');
        $('#donViCoBan').text('');
        $('#thanhTienPreview').text('0đ');
    }

    function resetForm() {
        $('#nhapHangId').val('');
        $('#sanPhamId').val('');
        selectedSanPhamId = null;
        $('#tenSanPham').val(null).trigger('change');
        $('#donViBanId').prop('disabled', true).html('<option value="">-- Chọn sản phẩm trước --</option>');
        $('#soLuong').val(1);
        $('#giaNhap').val(0);
        $('#ghiChu').val('');
        $('#tonKhoInfo').html('');
        $('#modalTitle').html('<i class="fas fa-plus me-2"></i>Thêm nhập hàng');
        resetCalculation();
    }

    function saveNhapHang() {
        const tenSanPham = $('#tenSanPham').find(':selected').text();

        if (!tenSanPham || tenSanPham.trim() === '') {
            toastr.error('Vui lòng chọn sản phẩm!');
            return;
        }

        const soLuong = parseFloat($('#soLuong').val()) || 0;
        if (soLuong <= 0) {
            toastr.error('Số lượng phải lớn hơn 0!');
            return;
        }

        const id = $('#nhapHangId').val();
        const data = {
            san_pham_id: $('#sanPhamId').val(),
            ten_san_pham: tenSanPham,
            don_vi_ban_id: $('#donViBanId').val(),
            so_luong: soLuong,
            gia_nhap: $('#giaNhap').val(),
            ngay_nhap: currentNgay,
            ghi_chu: $('#ghiChu').val()
        };

        const url = id ? `/nhap-hang/${id}` : '{{ route("nhap-hang.store") }}';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#nhapHangModal').modal('hide');
                    loadNhapHang();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    toastr.error(Object.values(errors).flat().join('<br>'));
                } else if (xhr.responseJSON?.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('Có lỗi xảy ra!');
                }
            }
        });
    }

    function loadNhapHang() {
        $.get('{{ route("nhap-hang.index") }}', { ngay: currentNgay }, function(response) {
            if (response.nhapHangs) {
                renderTable(response.nhapHangs, response.tongTien);
                window.history.pushState({}, '', '{{ route("nhap-hang.index") }}?ngay=' + currentNgay);
            }
        });
    }

    function renderTable(data, tongTien) {
        let html = '';
        if (data.length === 0) {
            html = '<tr id="emptyRow"><td colspan="6" class="text-center text-muted">Chưa có nhập hàng nào trong ngày</td></tr>';
        } else {
            data.forEach(function(nh, index) {
                html += `
                    <tr data-id="${nh.id}">
                        <td>${index + 1}</td>
                        <td>${nh.ten_san_pham}</td>
                        <td class="text-center">${nh.so_luong} ${nh.don_vi_ban?.ten_don_vi ?? ''}</td>
                        <td class="text-end">${formatNumber(nh.gia_nhap)}</td>
                        <td class="text-end">${formatNumber(nh.so_luong * nh.gia_nhap)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-action" onclick="editNhapHang(${nh.id})" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-action" onclick="deleteNhapHang(${nh.id})" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        $('#nhapHangBody').html(html);
        $('#tongTien').text(formatNumber(tongTien));
    }

    function editNhapHang(id) {
        $.get(`/nhap-hang/${id}`, function(response) {
            if (response.success) {
                const nh = response.data;
                $('#nhapHangId').val(nh.id);
                $('#sanPhamId').val(nh.san_pham_id || '');
                selectedSanPhamId = nh.san_pham_id;

                const option = new Option(nh.ten_san_pham, nh.san_pham_id || nh.ten_san_pham, true, true);
                $('#tenSanPham').append(option).trigger('change');

                // Load đơn vị bán nếu có san_pham_id
                if (nh.san_pham_id) {
                    loadDonViBanOptions(nh.san_pham_id);
                    setTimeout(function() {
                        $('#donViBanId').val(nh.don_vi_ban_id);
                        const selectedOption = $('#donViBanId').find(':selected');
                        currentTiLeQuyDoi = parseInt(selectedOption.data('ti-le')) || 1;
                        updateCalculation();
                    }, 500);
                }

                $('#soLuong').val(nh.so_luong);
                $('#giaNhap').val(nh.gia_nhap);
                $('#ghiChu').val(nh.ghi_chu || '');
                $('#modalTitle').html('<i class="fas fa-edit me-2"></i>Sửa nhập hàng');
                updateCalculation();
                $('#nhapHangModal').modal('show');
            }
        });
    }

    function deleteNhapHang(id) {
        if (!confirm('Bạn có chắc muốn xóa nhập hàng này?\n\nLưu ý: Tồn kho sẽ được trừ đi tương ứng!')) return;

        $.ajax({
            url: `/nhap-hang/${id}`,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    loadNhapHang();
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
