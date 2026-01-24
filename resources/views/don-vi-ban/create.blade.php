@extends('layouts.app')

@section('title', 'Thêm Đơn vị Bán')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>Thêm Đơn vị Bán Mới</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('don-vi-ban.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="ten_don_vi" class="form-label">Tên Đơn vị <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ten_don_vi') is-invalid @enderror" 
                                   id="ten_don_vi" name="ten_don_vi" value="{{ old('ten_don_vi') }}" required>
                            @error('ten_don_vi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mo_ta" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('mo_ta') is-invalid @enderror" 
                                      id="mo_ta" name="mo_ta" rows="3">{{ old('mo_ta') }}</textarea>
                            @error('mo_ta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('don-vi-ban.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Lưu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
