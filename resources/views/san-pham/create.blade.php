@extends('layouts.app')

@section('title', 'Thêm sản phẩm')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Thêm sản phẩm mới</h4>
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
                        <label for="dvt" class="form-label">Đơn vị tính <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('dvt') is-invalid @enderror" 
                               id="dvt" name="dvt" value="{{ old('dvt') }}" placeholder="VD: cái, kg, lít, gói..." required>
                        @error('dvt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="gia_nhap" class="form-label">Giá nhập <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('gia_nhap') is-invalid @enderror" 
                               id="gia_nhap" name="gia_nhap" value="{{ old('gia_nhap') }}" min="0" step="100" required>
                        @error('gia_nhap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="gia_ban" class="form-label">Giá bán <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('gia_ban') is-invalid @enderror" 
                               id="gia_ban" name="gia_ban" value="{{ old('gia_ban') }}" min="0" step="100" required>
                        @error('gia_ban')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                        <a href="{{ route('san-pham.index') }}" class="btn btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
