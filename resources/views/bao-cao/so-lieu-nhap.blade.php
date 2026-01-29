@extends('layouts.app')

@section('title', 'Số liệu nhập hàng')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Số liệu nhập hàng</h5>
        <a href="{{ route('bao-cao.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
    <div class="card-body">
        @include('bao-cao.partials.filter-form', ['route' => 'bao-cao.so-lieu-nhap'])
        
        <!-- Thống kê -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Tổng tiền nhập</h6>
                        <h4 class="text-danger mb-0">{{ number_format($tongTienNhap, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Tổng số lượng</h6>
                        <h4 class="text-info mb-0">{{ number_format($tongSoLuong) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Số phiếu nhập</h6>
                        <h4 class="text-success mb-0">{{ number_format($soPhieu) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Biểu đồ -->
        @if($baoCaoNgay->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Biểu đồ nhập hàng theo ngày</h6>
            </div>
            <div class="card-body">
                <canvas id="nhapHangChart" height="100"></canvas>
            </div>
        </div>
        @endif
        
        <!-- Bảng thống kê theo ngày -->
        <h5 class="mb-3"><i class="fas fa-calendar me-2"></i>Thống kê theo ngày</h5>
        <div class="table-responsive mb-4">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th class="text-center">Số phiếu</th>
                        <th class="text-center">Tổng SL</th>
                        <th class="text-end">Tổng tiền nhập</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baoCaoNgay as $bc)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($bc->ngay_nhap)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ number_format($bc->so_phieu) }}</td>
                        <td class="text-center">{{ number_format($bc->tong_so_luong) }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($bc->tong_tien_nhap, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($baoCaoNgay->count() > 0)
                <tfoot>
                    <tr class="table-primary fw-bold">
                        <th>Tổng cộng</th>
                        <th class="text-center">{{ number_format($soPhieu) }}</th>
                        <th class="text-center">{{ number_format($tongSoLuong) }}</th>
                        <th class="text-end">{{ number_format($tongTienNhap, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        
        <!-- Chi tiết nhập hàng -->
        <h5 class="mb-3"><i class="fas fa-list me-2"></i>Chi tiết phiếu nhập</h5>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">STT</th>
                        <th>Ngày</th>
                        <th>Sản phẩm</th>
                        <th class="text-center">SL</th>
                        <th class="text-end">Giá nhập</th>
                        <th class="text-end">Thành tiền</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chiTiet as $index => $nh)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $nh->ngay_nhap->format('d/m/Y') }}</td>
                        <td>{{ $nh->ten_san_pham }}</td>
                        <td class="text-center">{{ number_format($nh->so_luong) }} {{ $nh->donViBan->ten_don_vi ?? '' }}</td>
                        <td class="text-end">{{ number_format($nh->gia_nhap, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">{{ number_format($nh->thanh_tien, 0, ',', '.') }}</td>
                        <td class="text-muted small">{{ $nh->ghi_chu }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($baoCaoNgay->count() > 0)
const ctx = document.getElementById('nhapHangChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Tiền nhập hàng',
            data: {!! json_encode($chartData) !!},
            borderColor: 'rgba(220, 53, 69, 1)',
            backgroundColor: 'rgba(220, 53, 69, 0.2)',
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