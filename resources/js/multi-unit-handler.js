/**
 * Multi-Unit Handler - Xử lý logic đa đơn vị trong form bán hàng
 *
 * Chức năng:
 * - Load danh sách đơn vị bán của sản phẩm
 * - Tính toán tự động số lượng tương đương (quy đổi về đơn vị cơ bản)
 * - Tính toán thành tiền
 * - Kiểm tra tồn kho trước khi bán
 * - Format hiển thị tiền tệ
 */

const MultiUnitHandler = {
    // Biến lưu trữ thông tin hiện tại
    currentSanPham: null,
    currentDonViList: [],
    currentTonKho: 0,
    currentDonViCoBan: '',

    /**
     * Khởi tạo handler
     */
    init: function() {
        console.log('MultiUnitHandler initialized');
        this.bindEvents();
    },

    /**
     * Gắn các event listeners
     */
    bindEvents: function() {
        const self = this;

        // Khi chọn sản phẩm từ Select2
        $('#tenSanPham').on('select2:select', function(e) {
            const data = e.params.data;
            console.log('Selected product:', data);

            if (data.id && !data.newTag) {
                // Sản phẩm có sẵn trong database
                $('#sanPhamId').val(data.id);
                self.loadDonViOptions(data.id);
            } else {
                // Sản phẩm mới (tạo nhanh)
                $('#sanPhamId').val('');
                self.resetDonViDropdown();

                // Nếu có giá bán mặc định
                if (data.gia_ban) {
                    $('#giaBan').val(data.gia_ban);
                }
            }
        });

        // Khi chọn đơn vị bán
        $('#donViBanId').on('change', function() {
            self.onDonViChange();
        });

        // Khi thay đổi số lượng
        $('#soLuong').on('input', function() {
            self.calculateTuongDuong();
            self.calculateThanhTien();
            self.checkTonKho();
        });

        // Khi thay đổi giá bán thủ công
        $('#giaBan').on('input', function() {
            self.calculateThanhTien();
        });
    },

    /**
     * Load danh sách đơn vị bán từ API
     * @param {number} sanPhamId - ID sản phẩm
     */
    loadDonViOptions: function(sanPhamId) {
        const self = this;

        // Hiển thị loading
        $('#donViBanId').html('<option value="">Đang tải...</option>').prop('disabled', true);
        $('#tonKhoInfo').html('<span class="text-muted">Đang tải...</span>');

        $.ajax({
            url: `/api/san-pham/${sanPhamId}/don-vi-options`,
            method: 'GET',
            success: function(response) {
                console.log('Don vi options loaded:', response);

                if (response.success && response.data) {
                    const sanPham = response.data.san_pham;
                    const donViList = response.data.don_vi_list || [];

                    // Kiểm tra dữ liệu sản phẩm tồn tại trước khi truy cập thuộc tính
                    if (!sanPham) {
                        console.error('Dữ liệu sản phẩm không tồn tại trong response');
                        toastr.error('Không tìm thấy thông tin sản phẩm');
                        self.resetDonViDropdown();
                        return;
                    }

                    self.currentSanPham = sanPham;
                    self.currentDonViList = donViList;
                    self.currentTonKho = sanPham.so_luong || 0;
                    self.currentDonViCoBan = sanPham.don_vi_co_ban || '';

                    // Populate dropdown
                    self.populateDonViDropdown(donViList);

                    // Hiển thị thông tin tồn kho
                    self.updateTonKhoDisplay();

                    // Tự động chọn đơn vị đầu tiên nếu có
                    if (donViList.length > 0) {
                        $('#donViBanId').val(donViList[0].id).trigger('change');
                    }
                } else {
                    toastr.error(response.message || 'Không thể load đơn vị bán');
                    self.resetDonViDropdown();
                }
            },
            error: function(xhr) {
                console.error('Error loading don vi options:', xhr);

                let errorMessage = 'Có lỗi khi tải đơn vị bán';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'Không tìm thấy sản phẩm';
                } else if (xhr.status === 500) {
                    errorMessage = 'Lỗi server, vui lòng thử lại';
                }

                toastr.error(errorMessage);
                self.resetDonViDropdown();
            }
        });
    },

    /**
     * Render dropdown đơn vị bán
     * @param {Array} donViList - Danh sách đơn vị bán
     */
    populateDonViDropdown: function(donViList) {
        const $select = $('#donViBanId');
        $select.empty().prop('disabled', false);

        if (!donViList || donViList.length === 0) {
            $select.html('<option value="">Chưa có đơn vị bán</option>');
            toastr.warning('Sản phẩm này chưa có đơn vị bán. Vui lòng thêm đơn vị bán trước.');
            return;
        }

        // Thêm option placeholder
        $select.append('<option value="">-- Chọn đơn vị --</option>');

        // Thêm các option đơn vị
        donViList.forEach(function(donVi) {
            const label = `${donVi.ten_don_vi} (${donVi.ti_le_quy_doi} ${donVi.don_vi_co_ban || MultiUnitHandler.currentDonViCoBan}) - ${MultiUnitHandler.formatCurrency(donVi.gia_ban)}`;
            $select.append(`<option value="${donVi.id}"
                                    data-ti-le="${donVi.ti_le_quy_doi}"
                                    data-gia="${donVi.gia_ban}"
                                    data-ten="${donVi.ten_don_vi}">${label}</option>`);
        });
    },

    /**
     * Reset dropdown đơn vị bán
     */
    resetDonViDropdown: function() {
        $('#donViBanId').html('<option value="">-- Chọn sản phẩm trước --</option>').prop('disabled', true);
        $('#tonKhoInfo').html('');
        $('#tuongDuong').text('0');
        $('#donViCoBan').text('');
        this.currentSanPham = null;
        this.currentDonViList = [];
        this.currentTonKho = 0;
        this.currentDonViCoBan = '';
    },

    /**
     * Xử lý khi thay đổi đơn vị bán
     */
    onDonViChange: function() {
        const $selected = $('#donViBanId option:selected');
        const gia = $selected.data('gia');

        if (gia) {
            // Tự động điền giá bán
            $('#giaBan').val(gia);
        }

        // Tính toán lại
        this.calculateTuongDuong();
        this.calculateThanhTien();
        this.checkTonKho();
    },

    /**
     * Tính số lượng tương đương (quy đổi về đơn vị cơ bản)
     */
    calculateTuongDuong: function() {
        const soLuong = parseFloat($('#soLuong').val()) || 0;
        const $selected = $('#donViBanId option:selected');
        const tiLe = parseFloat($selected.data('ti-le')) || 1;

        const tuongDuong = soLuong * tiLe;

        $('#tuongDuong').text(this.formatNumber(tuongDuong));
        $('#donViCoBan').text(this.currentDonViCoBan);

        return tuongDuong;
    },

    /**
     * Tính thành tiền
     */
    calculateThanhTien: function() {
        const soLuong = parseFloat($('#soLuong').val()) || 0;
        const gia = parseFloat($('#giaBan').val()) || 0;
        const thanhTien = soLuong * gia;

        $('#thanhTienPreview').text(this.formatCurrency(thanhTien));

        return thanhTien;
    },

    /**
     * Kiểm tra tồn kho
     */
    checkTonKho: function() {
        const tuongDuong = this.calculateTuongDuong();
        const tonKho = this.currentTonKho;

        if (tuongDuong > tonKho) {
            $('#tonKhoWarning').remove();
            $('#soLuong').after(
                '<div id="tonKhoWarning" class="text-danger small mt-1">' +
                '<i class="fas fa-exclamation-triangle"></i> ' +
                'Vượt quá tồn kho! (Còn ' + this.formatNumber(tonKho) + ' ' + this.currentDonViCoBan + ')' +
                '</div>'
            );
            return false;
        } else {
            $('#tonKhoWarning').remove();
            return true;
        }
    },

    /**
     * Cập nhật hiển thị tồn kho
     */
    updateTonKhoDisplay: function() {
        const html = `
            <div class="alert alert-info py-2 mb-0">
                <i class="fas fa-box"></i>
                <strong>Tồn kho:</strong> ${this.formatNumber(this.currentTonKho)} ${this.currentDonViCoBan}
            </div>
        `;
        $('#tonKhoInfo').html(html);
    },

    /**
     * Validate trước khi submit
     */
    validateBeforeSubmit: function() {
        const sanPhamId = $('#sanPhamId').val();
        const donViBanId = $('#donViBanId').val();
        const soLuong = parseFloat($('#soLuong').val());

        // Kiểm tra sản phẩm
        if (!sanPhamId) {
            toastr.warning('Vui lòng chọn sản phẩm có sẵn (không phải tạo mới)');
            return false;
        }

        // Kiểm tra đơn vị bán
        if (!donViBanId) {
            toastr.warning('Vui lòng chọn đơn vị bán');
            return false;
        }

        // Kiểm tra số lượng
        if (!soLuong || soLuong <= 0) {
            toastr.warning('Số lượng phải lớn hơn 0');
            return false;
        }

        // Kiểm tra tồn kho
        if (!this.checkTonKho()) {
            toastr.error('Số lượng vượt quá tồn kho!');
            return false;
        }

        return true;
    },

    /**
     * Format số với dấu phẩy ngăn cách hàng nghìn
     * @param {number} number - Số cần format
     * @returns {string}
     */
    formatNumber: function(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    },

    /**
     * Format tiền tệ
     * @param {number} number - Số tiền
     * @returns {string}
     */
    formatCurrency: function(number) {
        return new Intl.NumberFormat('vi-VN').format(number) + 'đ';
    },

    /**
     * Reset form về trạng thái ban đầu
     */
    resetForm: function() {
        this.currentSanPham = null;
        this.currentDonViList = [];
        this.currentTonKho = 0;
        this.currentDonViCoBan = '';
        this.resetDonViDropdown();
        $('#tonKhoWarning').remove();
    }
};

// Export để sử dụng trong các file khác
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MultiUnitHandler;
}
