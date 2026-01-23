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
                        <label for="dvt" class="form-label">Đơn vị tính</label>
                        <select class="form-select @error('dvt') is-invalid @enderror" id="dvt" name="dvt">
                            <option value="">-- Chọn đơn vị tính --</option>
                            @foreach($donViTinhs as $donVi)
                                <option value="{{ $donVi->ten_don_vi }}" {{ old('dvt') == $donVi->ten_don_vi ? 'selected' : '' }}>
                                    {{ $donVi->ten_don_vi }}
                                </option>
                            @endforeach
                        </select>
                        @error('dvt')
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
