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
                            <td>{{ $sanPham->don_vi_co_ban }}</td>
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
                            <td><strong>{{ $sanPham->so_luong_ton_kho }} {{ $sanPham->don_vi_co_ban }}</strong></td>
                        </tr>
                        <tr>
                            <th>Ghi chú:</th>
                            <td>{{ $sanPham->ghi_chu ?? 'Không có' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Đơn vị Bán & Giá</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDonViModal">
                        <i class="bi bi-plus-circle"></i> Thêm đơn vị
                    </button>
                </div>
                <div class="card-body">
                    @if($sanPham->sanPhamDonVi->count() > 0)
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Đơn vị</th>
                                    <th>Tỉ lệ quy đổi</th>
                                    <th>Giá bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sanPham->sanPhamDonVi as $spDonVi)
                                    <tr>
                                        <td>{{ $spDonVi->donViBan->ten_don_vi }}</td>
                                        <td>{{ $spDonVi->ti_le_quy_doi }} {{ $sanPham->don_vi_co_ban }}</td>
                                        <td>{{ number_format($spDonVi->gia_ban) }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center">Chưa có đơn vị bán nào</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
