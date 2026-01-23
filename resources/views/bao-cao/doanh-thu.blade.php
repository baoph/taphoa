@extends('layouts.app')

@section('title', 'Báo cáo Doanh thu')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Báo cáo Doanh thu</h5>
    </div>
    <div class="card-body">
        <!-- Filter Form -->
        <form action="{{ route('bao-cao.doanh-thu') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-2">
                <label class="form-label">Loại báo cáo</label>
                <select name="loai" id="loaiBaoCao" class="form-select">
                    <option value="ngay" {{ $loai == 'ngay' ? 'selected' : '' }}>Theo ngày</option>
                    <option value="thang" {{ $loai == 'thang' ? 'selected' : '' }}>Theo tháng</option>
                </select>
            </div>

            <div class="col-md-3" id="filterNgay" style="{{ $loai == 'thang' ? 'display:none;' : '' }}">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="tu_ngay" class="form-control" value="{{ $tuNgay }}">
            </div>

            <div class="col-md-3" id="filterDenNgay" style="{{ $loai == 'thang' ? 'display:none;' : '' }}">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="den_ngay" class="form-control" value="{{ $denNgay }}">
            </div>

            <div class="col-md-3" id="filterThang" style="{{ $loai == 'ngay' ? 'display:none;' : '' }}">
                <label class="form-label">Tháng</label>
                <input type="month" name="thang" class="form-control" value="{{ $thang }}">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Xem báo cáo
                </button>
            </div>
        </form>

        <!-- Summary -->
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-6">
                    <strong><i class="fas fa-calendar me-2"></i>Thời gian:</strong>
                    @if($loai == 'ngay')
                        {{ \Carbon\Carbon::parse($tuNgay)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($denNgay)->format('d/m/Y') }}
                    @else
                        Tháng {{ \Carbon\Carbon::parse($thang)->format('m/Y') }}
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <strong><i class="fas fa-coins me-2"></i>Tổng doanh thu:</strong>
                    <span class="text-primary fs-5 fw-bold">{{ number_format($tongDoanhThu, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Ngày</th>
                        <th class="text-center">Số đơn</th>
                        <th class="text-center">Tổng SL</th>
                        <th class="text-end">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($baoCao as $index => $bc)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($bc->ngay_ban)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $bc->so_don }}</td>
                        <td class="text-center">{{ $bc->tong_so_luong }}</td>
                        <td class="text-end fw-bold">{{ number_format($bc->doanh_thu, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Không có dữ liệu trong khoảng thời gian này</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($baoCao->count() > 0)
                <tfoot>
                    <tr class="table-primary">
                        <th colspan="2">Tổng cộng</th>
                        <th class="text-center">{{ $baoCao->sum('so_don') }}</th>
                        <th class="text-center">{{ $baoCao->sum('tong_so_luong') }}</th>
                        <th class="text-end">{{ number_format($tongDoanhThu, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#loaiBaoCao').on('change', function() {
        if ($(this).val() === 'ngay') {
            $('#filterNgay, #filterDenNgay').show();
            $('#filterThang').hide();
        } else {
            $('#filterNgay, #filterDenNgay').hide();
            $('#filterThang').show();
        }
    });
</script>
@endpush
