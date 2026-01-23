@extends('layouts.app')

@section('title', 'Danh sách sản phẩm')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📦 Danh sách sản phẩm</h2>
    <a href="{{ route('san-pham.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> Thêm sản phẩm
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên sản phẩm</th>
                    <th>ĐVT</th>
                    <th class="text-end">Giá nhập</th>
                    <th class="text-end">Giá bán</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sanPhams as $index => $sp)
                <tr>
                    <td>{{ $sanPhams->firstItem() + $index }}</td>
                    <td>{{ $sp->ten_san_pham }}</td>
                    <td>{{ $sp->dvt }}</td>
                    <td class="text-end">{{ number_format($sp->gia_nhap) }}đ</td>
                    <td class="text-end">{{ number_format($sp->gia_ban) }}đ</td>
                    <td class="text-center">
                        <a href="{{ route('san-pham.edit', $sp) }}" class="btn btn-sm btn-warning btn-action">Sửa</a>
                        <form action="{{ route('san-pham.destroy', $sp) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Chưa có sản phẩm nào</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="d-flex justify-content-center">
            {{ $sanPhams->links() }}
        </div>
    </div>
</div>
@endsection
