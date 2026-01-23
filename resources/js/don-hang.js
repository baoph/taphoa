/**
 * Xử lý AJAX cho CRUD đơn hàng
 * Tích hợp Select2 cho tìm kiếm sản phẩm
 */

$(document).ready(function() {
    // Khởi tạo Select2 cho dropdown sản phẩm
    function initSelect2() {
        $('#selectSanPham').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalThemDonHang'),
            placeholder: 'Tìm sản phẩm...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '/api/san-pham',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || ''
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });
    }

    // Khi chọn sản phẩm, tự động điền giá bán
    $('#selectSanPham').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.gia_ban) {
            $('#giaBan').val(data.gia_ban);
        }
    });

    // Thêm đơn hàng mới
    function themDonHang(data) {
        return $.ajax({
            url: '/don-hang',
            method: 'POST',
            data: data
        });
    }

    // Cập nhật đơn hàng
    function capNhatDonHang(id, data) {
        return $.ajax({
            url: '/don-hang/' + id,
            method: 'PUT',
            data: data
        });
    }

    // Xóa đơn hàng
    function xoaDonHang(id) {
        return $.ajax({
            url: '/don-hang/' + id,
            method: 'DELETE'
        });
    }

    // Tải lại danh sách đơn hàng
    function taiLaiDanhSach(ngay) {
        return $.ajax({
            url: '/don-hang',
            method: 'GET',
            data: { ngay: ngay }
        });
    }

    // Cập nhật tổng tiền
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

    // Format số tiền
    function formatMoney(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    }

    // Khởi tạo
    initSelect2();
});
