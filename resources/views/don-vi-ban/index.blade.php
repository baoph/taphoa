@extends('layouts.app')

@section('title', 'Quản lý Đơn vị Bán')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Đơn vị Bán</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('don-vi-ban.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm Đơn vị
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Đơn vị</th>
                        <th>Mô tả</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($donViBans as $donViBan)
                        <tr>
                            <td>{{ $donViBan->id }}</td>
                            <td>{{ $donViBan->ten_don_vi }}</td>
                            <td>{{ $donViBan->mo_ta }}</td>
                            <td>
                                <a href="{{ route('don-vi-ban.edit', $donViBan) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Sửa
                                </a>
                                <form action="{{ route('don-vi-ban.destroy', $donViBan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa?')">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Chưa có đơn vị bán nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $donViBans->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
