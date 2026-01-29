@extends('layouts.app')

@section('title', 'Tổng quan báo cáo')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Tổng quan báo cáo</h5>
    </div>
    <div class="card-body">
        <!-- Bộ lọc thời gian -->
        @include('bao-cao.partials.filter-form', ['route' => 'bao-cao.index'])
        
        <!-- Thống kê tổng quan -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2"><i class="fas fa-money-bill-wave me-1"></i>Doanh thu</h6>
                        <h4 class="text-primary mb-0">{{ number_format($tongDoanhThu, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2"><i class="fas fa-box me-1"></i>Giá vốn</h6>
                        <h4 class="text-warning mb-0">{{ number_format($tongGiaVon, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2"><i class="fas fa-hand-holding-usd me-1"></i>Lợi nhuận</h6>
                        <h4 class="text-success mb-0">{{ number_format($tongLoiNhuan, 0, ',', '.') }}đ</h4>
                        <small class="text-muted">({{ number_format($tyLeLoiNhuan, 1) }}%)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2"><i class="fas fa-truck-loading me-1"></i>Tiền nhập hàng</h6>
                        <h4 class="text-danger mb-0">{{ number_format($tongTienNhap, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-light h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted"><i class="fas fa-shopping-cart me-1"></i>Số đơn hàng</h6>
                        <h3 class="mb-0">{{ number_format($soDon) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted"><i class="fas fa-cubes me-1"></i>Tổng số lượng bán</h6>
                        <h3 class="mb-0">{{ number_format($tongSoLuong) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted"><i class="fas fa-receipt me-1"></i>Trung bình/đơn</h6>
                        <h3 class="mb-0">{{ $soDon > 0 ? number_format($tongDoanhThu / $soDon, 0, ',', '.') : 0 }}đ</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu báo cáo chi tiết -->
        <h5 class="mb-3"><i class="fas fa-list me-2"></i>Báo cáo chi tiết</h5>
        <div class="row">
            <div class="col-md-4 mb-3">
                <a href="{{ route('bao-cao.doanh-thu') }}" class="card text-decoration-none h-100 border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Báo cáo Doanh thu</h5>
                        <p class="card-text text-muted small">Thống kê doanh thu theo ngày, tuần, tháng, năm</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('bao-cao.loi-nhuan') }}" class="card text-decoration-none h-100 border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-pie fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Báo cáo Lợi nhuận</h5>
                        <p class="card-text text-muted small">Phân tích lợi nhuận theo sản phẩm và thời gian</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('bao-cao.san-pham-ban-chay') }}" class="card text-decoration-none h-100 border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-fire fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Sản phẩm bán chạy</h5>
                        <p class="card-text text-muted small">Top sản phẩm bán chạy nhất</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('bao-cao.san-pham-nhap') }}" class="card text-decoration-none h-100 border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-boxes fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Sản phẩm nhập hàng</h5>
                        <p class="card-text text-muted small">Thống kê sản phẩm nhập hàng</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="{{ route('bao-cao.so-lieu-nhap') }}" class="card text-decoration-none h-100 border-danger">
                    <div class="card-body text-center">
                        <i class="fas fa-truck fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Số liệu nhập hàng</h5>
                        <p class="card-text text-muted small">Chi tiết số liệu nhập hàng theo thời gian</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

@if($thongKeTheoNgay->count() > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Thống kê theo ngày</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th class="text-center">Số đơn</th>
                        <th class="text-center">Số lượng</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-end">Giá vốn</th>
                        <th class="text-end">Lợi nhuận</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($thongKeTheoNgay as $tk)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($tk['ngay'])->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $tk['so_don'] }}</td>
                        <td class="text-center">{{ number_format($tk['so_luong']) }}</td>
                        <td class="text-end">{{ number_format($tk['doanh_thu'], 0, ',', '.') }}</td>
                        <td class="text-end text-muted">{{ number_format($tk['gia_von'], 0, ',', '.') }}</td>
                        <td class="text-end {{ $tk['loi_nhuan'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($tk['loi_nhuan'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-primary fw-bold">
                        <td>Tổng cộng</td>
                        <td class="text-center">{{ $thongKeTheoNgay->sum('so_don') }}</td>
                        <td class="text-center">{{ number_format($thongKeTheoNgay->sum('so_luong')) }}</td>
                        <td class="text-end">{{ number_format($thongKeTheoNgay->sum('doanh_thu'), 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($thongKeTheoNgay->sum('gia_von'), 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($thongKeTheoNgay->sum('loi_nhuan'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
