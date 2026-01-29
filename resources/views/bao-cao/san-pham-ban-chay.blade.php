@extends('layouts.app')

@section('title', 'Sản phẩm bán chạy')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Sản phẩm bán chạy</h5>
        <a href="{{ route('bao-cao.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
    <div class="card-body">
        @include('bao-cao.partials.filter-form', ['route' => 'bao-cao.san-pham-ban-chay', 'showLimit' => true])
        
        <!-- Thống kê -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Tổng doanh thu</h6>
                        <h4 class="text-primary mb-0">{{ number_format($tongDoanhThu, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Tổng số lượng bán</h6>
                        <h4 class="text-success mb-0">{{ number_format($tongSoLuong) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Số sản phẩm</h6>
                        <h4 class="text-info mb-0">{{ $baoCao->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Biểu đồ -->
        @if($baoCao->count() > 0)
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Top 10 số lượng bán</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="soLuongChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Top 10 doanh thu</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="doanhThuChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Bảng chi tiết -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px;">Hạng</th>
                        <th>Sản phẩm</th>
                        <th class="text-center">Số lần bán</th>
                        <th class="text-center">Tổng SL bán</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-end">Lợi nhuận</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baoCao as $index => $bc)
                    <tr>
                        <td>
                            @if($index < 3)
                                <span class="badge {{ $index == 0 ? 'bg-warning' : ($index == 1 ? 'bg-secondary' : 'bg-danger') }} fs-6">
                                    #{{ $index + 1 }}
                                </span>
                            @else
                                #{{ $index + 1 }}
                            @endif
                        </td>
                        <td><strong>{{ $bc->ten_san_pham }}</strong></td>
                        <td class="text-center">{{ number_format($bc->so_lan_ban) }}</td>
                        <td class="text-center"><strong>{{ number_format($bc->tong_so_luong) }}</strong></td>
                        <td class="text-end">{{ number_format($bc->doanh_thu, 0, ',', '.') }}</td>
                        <td class="text-end {{ $bc->loi_nhuan >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($bc->loi_nhuan, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($baoCao->count() > 0)
                <tfoot>
                    <tr class="table-primary fw-bold">
                        <th colspan="2">Tổng cộng</th>
                        <th class="text-center">{{ number_format($baoCao->sum('so_lan_ban')) }}</th>
                        <th class="text-center">{{ number_format($tongSoLuong) }}</th>
                        <th class="text-end">{{ number_format($tongDoanhThu, 0, ',', '.') }}</th>
                        <th class="text-end">{{ number_format($baoCao->sum('loi_nhuan'), 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($baoCao->count() > 0)
// Biểu đồ số lượng
const slCtx = document.getElementById('soLuongChart').getContext('2d');
new Chart(slCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Số lượng bán',
            data: {!! json_encode($chartSoLuong) !!},
            backgroundColor: 'rgba(255, 159, 64, 0.7)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Biểu đồ doanh thu
const dtCtx = document.getElementById('doanhThuChart').getContext('2d');
new Chart(dtCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Doanh thu',
            data: {!! json_encode($chartDoanhThu) !!},
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return new Intl.NumberFormat('vi-VN').format(context.raw) + 'đ';
                    }
                }
            }
        }
    }
});
@endif
</script>
@endpush