@extends('layouts.app')

@section('title', 'Bán hàng')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🛒 Bán hàng ngày {{ \Carbon\Carbon::parse($ngay)->format('d/m/Y') }}</h2>
    <div class="d-flex gap-2">
        <input type="date" id="ngayBan" class="form-control" value="{{ $ngay }}">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalThemDonHang">
            + Thêm
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover" id="tableDonHang">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên sản phẩm</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Giá</th>
                    <th class="text-end">Thành tiền</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody id="danhSachDonHang">
                @forelse($donHangs as $index => $dh)
                <tr data-id="{{ $dh->id }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dh->ten_san_pham }}</td>
                    <td class="text-center">{{ $dh->so_luong }}</td>
                    <td class="text-end">{{ number_format($dh->gia) }}đ</td>
                    <td class="text-end">{{ number_format($dh->so_luong * $dh->gia) }}đ</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-warning btn-sua" 
                                data-id="{{ $dh->id }}" 
                                data-ten="{{ $dh->ten_san_pham }}" 
                                data-soluong="{{ $dh->so_luong }}" 
                                data-gia="{{ $dh->gia }}">Sửa</button>
                        <button class="btn btn-sm btn-danger btn-xoa" data-id="{{ $dh->id }}">Xóa</button>
                    </td>
                </tr>
                @empty
                <tr id="rowTrong">
                    <td colspan="6" class="text-center text-muted">Chưa có đơn hàng nào</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-primary">
                    <th colspan="4" class="text-end">Tổng cộng:</th>
                    <th class="text-end" id="tongTien">{{ number_format($tongTien) }}đ</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Modal Thêm/Sửa Đơn hàng -->
<div class="modal fade" id="modalThemDonHang" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Thêm đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formDonHang">
                    <input type="hidden" id="donHangId">
                    
                    <div class="mb-3">
                        <label for="selectSanPham" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <select id="selectSanPham" class="form-select" style="width: 100%"></select>
                    </div>

                    <div class="mb-3">
                        <label for="soLuong" class="form-label">Số lượng <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="soLuong" min="1" value="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="giaBan" class="form-label">Giá bán <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="giaBan" min="0" step="100" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btnLuuDonHang">Lưu</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Khởi tạo Select2
    $('#selectSanPham').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalThemDonHang'),
        placeholder: 'Tìm sản phẩm...',
        allowClear: true,
        ajax: {
            url: '{{ route("api.san-pham") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return { results: data.results };
            },
            cache: true
        }
    });

    // Khi chọn sản phẩm, tự động điền giá
    $('#selectSanPham').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.gia_ban) {
            $('#giaBan').val(data.gia_ban);
        }
    });

    // Đổi ngày
    $('#ngayBan').on('change', function() {
        window.location.href = '{{ route("don-hang.index") }}?ngay=' + $(this).val();
    });

    // Reset modal khi mở thêm mới
    $('#modalThemDonHang').on('show.bs.modal', function(e) {
        if (!$(e.relatedTarget).hasClass('btn-sua')) {
            $('#modalTitle').text('Thêm đơn hàng');
            $('#donHangId').val('');
            $('#selectSanPham').val(null).trigger('change');
            $('#soLuong').val(1);
            $('#giaBan').val('');
        }
    });

    // Nút sửa
    $(document).on('click', '.btn-sua', function() {
        var id = $(this).data('id');
        var ten = $(this).data('ten');
        var soLuong = $(this).data('soluong');
        var gia = $(this).data('gia');

        $('#modalTitle').text('Sửa đơn hàng');
        $('#donHangId').val(id);
        
        // Set giá trị cho Select2
        var option = new Option(ten, ten, true, true);
        $('#selectSanPham').append(option).trigger('change');
        
        $('#soLuong').val(soLuong);
        $('#giaBan').val(gia);

        $('#modalThemDonHang').modal('show');
    });

    // Lưu đơn hàng
    $('#btnLuuDonHang').on('click', function() {
        var id = $('#donHangId').val();
        var tenSanPham = $('#selectSanPham').find(':selected').text();
        var soLuong = $('#soLuong').val();
        var gia = $('#giaBan').val();

        if (!tenSanPham || !soLuong || !gia) {
            alert('Vui lòng nhập đầy đủ thông tin!');
            return;
        }

        var url = id ? '/don-hang/' + id : '{{ route("don-hang.store") }}';
        var method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: {
                ten_san_pham: tenSanPham,
                so_luong: soLuong,
                gia: gia
            },
            success: function(response) {
                if (response.success) {
                    $('#modalThemDonHang').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                alert('Có lỗi xảy ra!');
            }
        });
    });

    // Xóa đơn hàng
    $(document).on('click', '.btn-xoa', function() {
        if (!confirm('Bạn có chắc muốn xóa?')) return;

        var id = $(this).data('id');
        var row = $(this).closest('tr');

        $.ajax({
            url: '/don-hang/' + id,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    row.fadeOut(300, function() {
                        $(this).remove();
                        capNhatTongTien();
                    });
                }
            },
            error: function() {
                alert('Có lỗi xảy ra!');
            }
        });
    });

    function capNhatTongTien() {
        var tong = 0;
        $('#danhSachDonHang tr').each(function() {
            var thanhTien = $(this).find('td:eq(4)').text();
            if (thanhTien) {
                tong += parseInt(thanhTien.replace(/[^0-9]/g, '')) || 0;
            }
        });
        $('#tongTien').text(tong.toLocaleString('vi-VN') + 'đ');
    }
});
</script>
@endpush
