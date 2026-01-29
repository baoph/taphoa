@extends('layouts.app')

@section('title', 'Sản phẩm nhập hàng')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Sản phẩm nhập hàng</h5>
        <a href="{{ route('bao-cao.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>
    <div class="card-body">
        @include('bao-cao.partials.filter-form', ['route' => 'bao-cao.san-pham-nhap'])
        
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
                        <h6 class="text-muted mb-1">Tổng số lượng nhập</h6>
                        <h4 class="text-info mb-0">{{ number_format($tongSoLuong) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center py-3">
                        <h6 class="text-muted mb-1">Số loại sản phẩm</h6>
                        <h4 class="text-success mb-0">{{ $baoCao->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Biểu đồ -->
        @if($baoCao->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Top 10 sản phẩm nhập nhiều nhất (theo tiền)</h6>
            </div>
            <div class="card-body">
                <canvas id="nhapHangChart" height="100"></canvas>
            </div>
        </div>
        @endif
        
        <!-- Bảng chi tiết -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Sản phẩm</th>
                        <th class="text-center">Số lần nhập</th>
                        <th class="text-center">Tổng SL nhập</th>
                        <th class="text-end">Giá nhập TB</th>
                        <th class="text-end">Tổng tiền nhập</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baoCao as $index => $bc)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $bc->ten_san_pham }}</strong></td>
                        <td class="text-center">{{ number_format($bc->so_lan_nhap) }}</td>
                        <td class="text-center">{{ number_format($bc->tong_so_luong) }}</td>
                        <td class="text-end">{{ number_format($bc->gia_nhap_tb, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($bc->tong_tien_nhap, 0, ',', '.') }}</td>
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
                        <th class="text-center">{{ number_format($baoCao->sum('so_lan_nhap')) }}</th>
                        <th class="text-center">{{ number_format($tongSoLuong) }}</th>
                        <th class="text-end">-</th>
                        <th class="text-end">{{ number_format($tongTienNhap, 0, ',', '.') }}</th>
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
const ctx = document.getElementById('nhapHangChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            data: {!! json_encode($chartData) !!},
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)',
                'rgba(199, 199, 199, 0.7)',
                'rgba(83, 102, 255, 0.7)',
                'rgba(255, 99, 255, 0.7)',
                'rgba(99, 255, 132, 0.7)'
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
@endif
</script>
@endpush