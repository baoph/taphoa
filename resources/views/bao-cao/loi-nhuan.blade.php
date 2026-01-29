@extends('layouts.app')

@section('title', 'Báo cáo Lợi nhuận')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Báo cáo Lợi nhuận</h5>
        <a href="{{ route('bao-cao.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
    <div class="card-body">
        @include('bao-cao.partials.filter-form', ['route' => 'bao-cao.loi-nhuan'])
        
        <!-- Thống kê tổng quan -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Tổng doanh thu</h6>
                        <h4 class="text-primary mb-0">{{ number_format($tongDoanhThu, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Tổng giá vốn</h6>
                        <h4 class="text-warning mb-0">{{ number_format($tongGiaVon, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Tổng lợi nhuận</h6>
                        <h4 class="text-success mb-0">{{ number_format($tongLoiNhuan, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Tỷ lệ lợi nhuận</h6>
                        <h4 class="text-info mb-0">{{ number_format($tyLeLoiNhuan, 1) }}%</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Biểu đồ -->
        @if($baoCaoSanPham->count() > 0)
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Top 5 sản phẩm lợi nhuận cao</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Lợi nhuận theo ngày</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Bảng chi tiết theo sản phẩm -->
        <h5 class="mb-3"><i class="fas fa-list me-2"></i>Lợi nhuận theo sản phẩm</h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Sản phẩm</th>
                        <th class="text-center">SL bán</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-end">Giá vốn</th>
                        <th class="text-end">Lợi nhuận</th>
                        <th class="text-end">Tỷ lệ (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baoCaoSanPham as $index => $bc)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $bc->ten_san_pham }}</td>
                        <td class="text-center">{{ number_format($bc->tong_so_luong) }}</td>
                        <td class="text-end">{{ number_format($bc->doanh_thu, 0, ',', '.') }}</td>
                        <td class="text-end text-muted">{{ number_format($bc->gia_von, 0, ',', '.') }}</td>
                        <td class="text-end {{ $bc->loi_nhuan >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                            {{ number_format($bc->loi_nhuan, 0, ',', '.') }}
                        </td>
                        <td class="text-end">
                            <span class="badge {{ $bc->ty_le_loi_nhuan >= 20 ? 'bg-success' : ($bc->ty_le_loi_nhuan >= 10 ? 'bg-warning' : 'bg-danger') }}">
                                {{ number_format($bc->ty_le_loi_nhuan, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($baoCaoSanPham->count() > 0)
                <tfoot>
                    <tr class="table-primary fw-bold">
                        <th colspan="2">Tổng cộng</th>
                        <th class="text-center">{{ number_format($baoCaoSanPham->sum('tong_so_luong')) }}</th>
                        <th class="text-end">{{ number_format($tongDoanhThu, 0, ',', '.') }}</th>
                        <th class="text-end">{{ number_format($tongGiaVon, 0, ',', '.') }}</th>
                        <th class="text-end">{{ number_format($tongLoiNhuan, 0, ',', '.') }}</th>
                        <th class="text-end">{{ number_format($tyLeLoiNhuan, 1) }}%</th>
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
@if($baoCaoSanPham->count() > 0)
// Pie Chart
const pieCtx = document.getElementById('pieChart').getContext('2d');
new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            data: {!! json_encode($chartData) !!},
            backgroundColor: [
                'rgba(75, 192, 92, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(153, 102, 255, 0.7)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + new Intl.NumberFormat('vi-VN').format(context.raw) + 'đ';
                    }
                }
            }
        }
    }
});

// Line Chart
const lineCtx = document.getElementById('lineChart').getContext('2d');
new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($lineLabels) !!},
        datasets: [{
            label: 'Lợi nhuận',
            data: {!! json_encode($lineData) !!},
            borderColor: 'rgba(75, 192, 92, 1)',
            backgroundColor: 'rgba(75, 192, 92, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value);
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(context.raw) + 'đ';
                    }
                }
            }
        }
    }
});
@endif
</script>
@endpush