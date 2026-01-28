/**
 * San Pham Don Vi Manager - Quản lý đơn vị bán của sản phẩm
 *
 * Chức năng:
 * - Thêm đơn vị bán mới cho sản phẩm
 * - Sửa thông tin đơn vị bán
 * - Xóa đơn vị bán
 * - Refresh bảng danh sách đơn vị
 * - Validation dữ liệu
 */

const SanPhamDonViManager = {
    // Biến lưu trữ
    currentSanPhamId: null,
    currentEditId: null,
    donViBanList: [],

    /**
     * Khởi tạo manager
     * @param {number} sanPhamId - ID sản phẩm
     */
    init: function(sanPhamId) {
        console.log('SanPhamDonViManager initialized for product:', sanPhamId);
        this.currentSanPhamId = sanPhamId;
        this.bindEvents();
        this.loadDonViBanList();
    },

    /**
     * Gắn các event listeners
     */
    bindEvents: function() {
        const self = this;

        // Nút thêm đơn vị mới
        $('#btnAddDonVi').off('click').on('click', function() {
            self.showAddDonViModal();
        });

        // Nút lưu trong modal
        $('#btnSaveDonVi').off('click').on('click', function() {
            self.saveDonVi();
        });

        // Reset form khi đóng modal
        $('#donViModal').on('hidden.bs.modal', function() {
            self.resetModalForm();
        });

        // Tính giá bán tự động khi thay đổi tỉ lệ quy đổi
        $('#tiLeQuyDoi').on('input', function() {
            self.calculateGiaBanSuggestion();
        });
    },

    /**
     * Load danh sách đơn vị bán có sẵn từ bảng don_vi_ban
     */
    loadDonViBanList: function() {
        const self = this;

        $.ajax({
            url: '/api/don-vi-ban',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    self.donViBanList = response.data;
                    self.populateDonViBanDropdown();
                }
            },
            error: function(xhr) {
                console.error('Error loading don vi ban list:', xhr);
            }
        });
    },

    /**
     * Populate dropdown đơn vị bán
     */
    populateDonViBanDropdown: function() {
        const $select = $('#donViBanIdSelect');
        $select.empty();
        $select.append('<option value="">-- Chọn đơn vị --</option>');

        this.donViBanList.forEach(function(donVi) {
            $select.append(`<option value="${donVi.id}">${donVi.ten_don_vi}</option>`);
        });
    },

    /**
     * Hiển thị modal thêm đơn vị mới
     */
    showAddDonViModal: function() {
        this.currentEditId = null;
        $('#donViModalTitle').html('<i class="bi bi-plus-circle"></i> Thêm đơn vị bán mới');
        $('#donViModal').modal('show');
    },

    /**
     * Hiển thị modal sửa đơn vị
     * @param {number} id - ID của san_pham_don_vi
     */
    showEditDonViModal: function(id) {
        const self = this;
        this.currentEditId = id;

        // Load dữ liệu
        $.ajax({
            url: `/api/san-pham-don-vi/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    // Fill form
                    $('#donViBanIdSelect').val(data.don_vi_ban_id);
                    $('#tiLeQuyDoi').val(data.ti_le_quy_doi);
                    $('#giaBanDonVi').val(data.gia_ban);

                    // Đổi title
                    $('#donViModalTitle').html('<i class="bi bi-pencil"></i> Sửa đơn vị bán');

                    // Hiển thị modal
                    $('#donViModal').modal('show');
                } else {
                    toastr.error(response.message || 'Không thể load dữ liệu');
                }
            },
            error: function(xhr) {
                console.error('Error loading don vi data:', xhr);
                toastr.error('Có lỗi khi tải dữ liệu');
            }
        });
    },

    /**
     * Lưu đơn vị bán (thêm mới hoặc cập nhật)
     */
    saveDonVi: function() {
        const self = this;

        // Validate
        if (!this.validateForm()) {
            return;
        }

        // Lấy dữ liệu từ form
        const data = {
            san_pham_id: this.currentSanPhamId,
            don_vi_ban_id: $('#donViBanIdSelect').val(),
            ti_le_quy_doi: parseFloat($('#tiLeQuyDoi').val()),
            gia_ban: parseFloat($('#giaBanDonVi').val())
        };

        // Xác định URL và method
        let url, method;
        if (this.currentEditId) {
            // Cập nhật
            url = `/api/san-pham-don-vi/${this.currentEditId}`;
            method = 'PUT';
        } else {
            // Thêm mới
            url = '/api/san-pham-don-vi';
            method = 'POST';
        }

        // Disable nút lưu
        $('#btnSaveDonVi').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');

        // AJAX request
        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Lưu thành công!');
                    $('#donViModal').modal('hide');
                    self.refreshDonViTable();
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra');
                }
            },
            error: function(xhr) {
                console.error('Error saving don vi:', xhr);

                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = '';
                    Object.values(errors).forEach(function(msgs) {
                        errorMsg += msgs.join('<br>') + '<br>';
                    });
                    toastr.error(errorMsg);
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Có lỗi khi lưu dữ liệu');
                }
            },
            complete: function() {
                // Enable lại nút lưu
                $('#btnSaveDonVi').prop('disabled', false).html('<i class="fas fa-save"></i> Lưu');
            }
        });
    },

    /**
     * Xóa đơn vị bán
     * @param {number} id - ID của san_pham_don_vi
     */
    deleteDonVi: function(id) {
        const self = this;

        // Confirm
        if (!confirm('Bạn có chắc muốn xóa đơn vị bán này?\n\nLưu ý: Các đơn hàng đã bán với đơn vị này sẽ không bị ảnh hưởng.')) {
            return;
        }

        $.ajax({
            url: `/api/san-pham-don-vi/${id}`,
            method: 'DELETE',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Xóa thành công!');
                    self.refreshDonViTable();
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra');
                }
            },
            error: function(xhr) {
                console.error('Error deleting don vi:', xhr);
                toastr.error(xhr.responseJSON?.message || 'Có lỗi khi xóa');
            }
        });
    },

    /**
     * Refresh bảng danh sách đơn vị
     */
    refreshDonViTable: function() {
        const self = this;

        $.ajax({
            url: `/api/san-pham/${this.currentSanPhamId}/don-vi-list`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    self.renderDonViTable(response.data, response.don_vi_co_ban_text);
                }
            },
            error: function(xhr) {
                console.error('Error refreshing table:', xhr);
            }
        });
    },

    /**
     * Render bảng danh sách đơn vị
     * @param {Array} data - Danh sách đơn vị
     */
    renderDonViTable: function(data, donViCoBan) {
        const self = this;
        const $tbody = $('#donViTableBody');
        $tbody.empty();

        if (data.length === 0) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted">Chưa có đơn vị bán nào</td></tr>');
            return;
        }

        data.forEach(function(item, index) {
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>${item.don_vi_ban.ten_don_vi}</strong></td>
                    <td>${self.formatNumber(item.ti_le_quy_doi)} ${donViCoBan}</td>
                    <td class="text-end">${self.formatCurrency(item.gia_ban)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-warning" onclick="SanPhamDonViManager.showEditDonViModal(${item.id})" title="Sửa">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="SanPhamDonViManager.deleteDonVi(${item.id})" title="Xóa">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $tbody.append(row);
        });
    },

    /**
     * Validate form
     * @returns {boolean}
     */
    validateForm: function() {
        const donViBanId = $('#donViBanIdSelect').val();
        const tiLe = parseFloat($('#tiLeQuyDoi').val());
        const gia = parseFloat($('#giaBanDonVi').val());

        if (!donViBanId) {
            toastr.warning('Vui lòng chọn đơn vị bán');
            $('#donViBanIdSelect').focus();
            return false;
        }

        if (!tiLe || tiLe <= 0) {
            toastr.warning('Tỉ lệ quy đổi phải lớn hơn 0');
            $('#tiLeQuyDoi').focus();
            return false;
        }

        if (!gia || gia <= 0) {
            toastr.warning('Giá bán phải lớn hơn 0');
            $('#giaBanDonVi').focus();
            return false;
        }

        return true;
    },

    /**
     * Tính giá bán gợi ý dựa trên tỉ lệ quy đổi
     */
    calculateGiaBanSuggestion: function() {
        const tiLe = parseFloat($('#tiLeQuyDoi').val());
        const giaBanLe = parseFloat($('#giaBanLeHidden').val()) || 0;

        if (tiLe > 0 && giaBanLe > 0) {
            const giaSuggestion = tiLe * giaBanLe;
            $('#giaBanSuggestion').text('Gợi ý: ' + this.formatCurrency(giaSuggestion));

            // Tự động điền nếu giá bán đang trống
            if (!$('#giaBanDonVi').val()) {
                $('#giaBanDonVi').val(giaSuggestion);
            }
        } else {
            $('#giaBanSuggestion').text('');
        }
    },

    /**
     * Reset form trong modal
     */
    resetModalForm: function() {
        this.currentEditId = null;
        $('#donViBanIdSelect').val('');
        $('#tiLeQuyDoi').val('');
        $('#giaBanDonVi').val('');
        $('#giaBanSuggestion').text('');
    },

    /**
     * Format số
     */
    formatNumber: function(number) {
        return new Intl.NumberFormat('vi-VN').format(number);
    },

    /**
     * Format tiền tệ
     */
    formatCurrency: function(number) {
        return new Intl.NumberFormat('vi-VN').format(number) + 'đ';
    }
};

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SanPhamDonViManager;
}
