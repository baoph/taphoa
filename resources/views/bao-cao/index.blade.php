@extends('layouts.app')

@section('title', 'Báo cáo doanh thu')

@section('content')
<h2 class="mb-4">📊 Báo cáo doanh thu</h2>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('bao-cao.index') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Loại báo cáo</label>
                <select name="loai" id="loaiBaoCao" class="form-select">
                    <option value="ngay" {{ $loai == 'ngay' ? 'selected' : '' }}>Theo ngày</option>
                    <option value="thang" {{ $loai == 'thang' ? 'selected' : '' }}>Theo tháng</option>
                </select>
            </div>
            
            <div class="col-md-3" id="filterNgay" style="{{ $loai == 'thang' ? 'display:none' : '' }}">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="tu_ngay" class="form-control" value="{{ $tuNgay }}">
            </div>
            
            <div class="col-md-3" id="filterDenNgay" style="{{ $loai == 'thang' ? 'display:none' : '' }}">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="den_ngay" class="form-control" value="{{ $denNgay }}">
            </div>
            
            <div class="col-md-3" id="filterThang" style="{{ $loai == 'ngay' ? 'display:none' : '' }}">
                <label class="form-label">Tháng</label>
                <input type="month" name="thang" class="form-control" value="{{ $thang }}">
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Xem báo cáo</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5>Tổng doanh thu</h5>
                <h2>{{ number_format($tongDoanhThu) }}đ</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5>Tổng số lượng bán</h5>
                <h2>{{ number_format($tongSoLuong) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h5>Số đơn hàng</h5>
                <h2>{{ $donHangs->count() }}</h2>
            </div>
        </div>
    </div>
</div>

@if($thongKeTheoNgay->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Thống kê theo ngày</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th class="text-center">Số đơn</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($thongKeTheoNgay as $tk)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tk['ngay'])->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $tk['so_don'] }}</td>
                    <td class="text-center">{{ $tk['so_luong'] }}</td>
                    <td class="text-end">{{ number_format($tk['doanh_thu']) }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Chi tiết đơn hàng</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ngày bán</th>
                    <th>Tên sản phẩm</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Giá</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @forelse($donHangs as $index => $dh)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dh->ngay_ban->format('d/m/Y') }}</td>
                    <td>{{ $dh->ten_san_pham }}</td>
                    <td class="text-center">{{ $dh->so_luong }}</td>
                    <td class="text-end">{{ number_format($dh->gia) }}đ</td>
                    <td class="text-end">{{ number_format($dh->so_luong * $dh->gia) }}đ</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#loaiBaoCao').on('change', function() {
        if ($(this).val() === 'ngay') {
            $('#filterNgay, #filterDenNgay').show();
            $('#filterThang').hide();
        } else {
            $('#filterNgay, #filterDenNgay').hide();
            $('#filterThang').show();
        }
    });
});
</script>
@endpush
