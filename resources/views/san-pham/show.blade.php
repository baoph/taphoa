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
                                    {{ $sanPham->so_luong_ton_kho }} {{ $sanPham->don_vi_co_ban }}
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
