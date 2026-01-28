@extends('layouts.app')

@section('title', 'Thêm Sản phẩm')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Thêm Sản phẩm mới</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('san-pham.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="ten_san_pham" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('ten_san_pham') is-invalid @enderror"
                               id="ten_san_pham" name="ten_san_pham" value="{{ old('ten_san_pham') }}" required>
                        @error('ten_san_pham')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="dv_nhap_hang" class="form-label">Đơn vị tính</label>
                        <select class="form-select @error('dv_nhap_hang') is-invalid @enderror" id="dv_nhap_hang" name="dv_nhap_hang">
                            <option value="">-- Chọn đơn vị tính --</option>
                            @foreach($donViTinhs as $donVi)
                                <option value="{{ $donVi->ten_don_vi }}" {{ old('dv_nhap_hang') == $donVi->ten_don_vi ? 'selected' : '' }}>
                                    {{ $donVi->ten_don_vi }}
                                </option>
                            @endforeach
                        </select>
                        @error('dv_nhap_hang')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="gia_nhap" class="form-label">Giá nhập (đ)</label>
                            <input type="number" class="form-control @error('gia_nhap') is-invalid @enderror"
                                   id="gia_nhap" name="gia_nhap" value="{{ old('gia_nhap', 0) }}" min="0">
                            @error('gia_nhap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="gia_ban" class="form-label">Giá bán (đ)</label>
                            <input type="number" class="form-control @error('gia_ban') is-invalid @enderror"
                                   id="gia_ban" name="gia_ban" value="{{ old('gia_ban', 0) }}" min="0">
                            @error('gia_ban')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="gia_ban_le" class="form-label">Giá bán lẻ (đ)</label>
                            <input type="number" class="form-control @error('gia_ban_le') is-invalid @enderror"
                                   id="gia_ban_le" name="gia_ban_le" value="{{ old('gia_ban_le', 0) }}" min="0">
                            @error('gia_ban_le')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="so_luong" class="form-label">Số lượng</label>
                            <input type="number" class="form-control @error('so_luong') is-invalid @enderror"
                                   id="so_luong" name="so_luong" value="{{ old('so_luong', 0) }}" min="0">
                            @error('so_luong')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="ti_so_chuyen_doi" class="form-label">Tỉ số chuyển đổi</label>
                            <input type="number" class="form-control @error('ti_so_chuyen_doi') is-invalid @enderror"
                                   id="ti_so_chuyen_doi" name="ti_so_chuyen_doi" value="{{ old('ti_so_chuyen_doi', 1) }}" min="1">
                            @error('ti_so_chuyen_doi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="so_luong_don_vi" class="form-label">Số lượng đơn vị</label>
                            <input type="number" class="form-control" id="so_luong_don_vi"
                                   name="so_luong_don_vi" value="{{ old('so_luong_don_vi', 0) }}" readonly>
                            <small class="text-muted">Tự động tính</small>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="ghi_chu" class="form-label">Ghi chú</label>
                            <textarea class="form-control @error('ghi_chu') is-invalid @enderror"
                                      id="ghi_chu" name="ghi_chu" rows="1">{{ old('ghi_chu') }}</textarea>
                            @error('ghi_chu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('san-pham.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Lưu sản phẩm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tự động tính số lượng đơn vị khi thay đổi số lượng hoặc tỉ số chuyển đổi
    function calculateSoLuongDonVi() {
        const soLuong = parseInt(document.getElementById('so_luong').value) || 0;
        const tiSoChuyenDoi = parseInt(document.getElementById('ti_so_chuyen_doi').value) || 1;
        const soLuongDonVi = soLuong * tiSoChuyenDoi;
        document.getElementById('so_luong_don_vi').value = soLuongDonVi;
    }

    document.getElementById('so_luong').addEventListener('input', calculateSoLuongDonVi);
    document.getElementById('ti_so_chuyen_doi').addEventListener('input', calculateSoLuongDonVi);

    // Tính toán ban đầu
    calculateSoLuongDonVi();
</script>
@endpush
