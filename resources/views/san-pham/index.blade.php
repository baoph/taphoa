@extends('layouts.app')

@section('title', 'Quản lý Sản phẩm')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-box me-2"></i>Danh sách Sản phẩm</h5>
        <div>
            <a href="{{ route('san-pham.import.form') }}" class="btn btn-success btn-sm me-2">
                <i class="fas fa-file-excel me-1"></i>Import Excel
            </a>
            <a href="{{ route('san-pham.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Thêm sản phẩm
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Tên sản phẩm</th>
                        <th style="width: 80px;">DVT</th>
                        <th style="width: 100px;" class="text-end">Giá nhập</th>
                        <th style="width: 100px;" class="text-end">Giá bán</th>
                        <th style="width: 100px;" class="text-end">Giá bán lẻ</th>
                        <th style="width: 80px;" class="text-center">Số lượng</th>
                        <th style="width: 80px;" class="text-center">Tỉ số</th>
                        <th style="width: 80px;" class="text-center">SL đơn vị</th>
                        <th style="width: 150px;">Ghi chú</th>
                        <th style="width: 120px;" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanPhams as $index => $sp)
                    <tr>
                        <td>{{ $sanPhams->firstItem() + $index }}</td>
                        <td>{{ $sp->ten_san_pham }}</td>
                        <td>{{ $sp->dvt }}</td>
                        <td class="text-end">{{ number_format($sp->gia_nhap, 0, ',', '.') }}đ</td>
                        <td class="text-end">{{ number_format($sp->gia_ban, 0, ',', '.') }}đ</td>
                        <td class="text-end">{{ number_format($sp->gia_ban_le, 0, ',', '.') }}đ</td>
                        <td class="text-center">{{ number_format($sp->so_luong ?? 0, 2, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($sp->ti_so_chuyen_doi ?? 1, 2, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($sp->so_luong_don_vi ?? 0, 2, ',', '.') }}</td>
                        <td>{{ $sp->ghi_chu ? Str::limit($sp->ghi_chu, 30) : '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('san-pham.edit', $sp) }}" class="btn btn-warning btn-action" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('san-pham.destroy', $sp) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-action" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">Chưa có sản phẩm nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $sanPhams->links() }}
        </div>
    </div>
</div>
@endsection
