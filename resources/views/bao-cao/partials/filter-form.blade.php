<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white py-2">
        <i class="fas fa-filter me-1"></i>Bộ lọc thời gian
    </div>
    <div class="card-body py-3">
        <form action="{{ route($route) }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">Loại thời gian</label>
                <select name="loai_thoi_gian" id="loaiThoiGian" class="form-select form-select-sm">
                    <option value="ngay" {{ ($loaiThoiGian ?? 'ngay') == 'ngay' ? 'selected' : '' }}>Theo ngày</option>
                    <option value="tuan" {{ ($loaiThoiGian ?? '') == 'tuan' ? 'selected' : '' }}>Tuần này</option>
                    <option value="thang" {{ ($loaiThoiGian ?? '') == 'thang' ? 'selected' : '' }}>Theo tháng</option>
                    <option value="nam" {{ ($loaiThoiGian ?? '') == 'nam' ? 'selected' : '' }}>Theo năm</option>
                </select>
            </div>
            
            <div class="col-md-2 filter-ngay" style="{{ ($loaiThoiGian ?? 'ngay') != 'ngay' ? 'display:none' : '' }}">
                <label class="form-label small mb-1">Từ ngày</label>
                <input type="date" name="tu_ngay" class="form-control form-control-sm" value="{{ $tuNgay ?? now()->format('Y-m-d') }}">
            </div>
            
            <div class="col-md-2 filter-ngay" style="{{ ($loaiThoiGian ?? 'ngay') != 'ngay' ? 'display:none' : '' }}">
                <label class="form-label small mb-1">Đến ngày</label>
                <input type="date" name="den_ngay" class="form-control form-control-sm" value="{{ $denNgay ?? now()->format('Y-m-d') }}">
            </div>
            
            <div class="col-md-2 filter-thang" style="{{ ($loaiThoiGian ?? 'ngay') != 'thang' ? 'display:none' : '' }}">
                <label class="form-label small mb-1">Tháng</label>
                <input type="month" name="thang" class="form-control form-control-sm" value="{{ $thang ?? now()->format('Y-m') }}">
            </div>
            
            <div class="col-md-2 filter-nam" style="{{ ($loaiThoiGian ?? 'ngay') != 'nam' ? 'display:none' : '' }}">
                <label class="form-label small mb-1">Năm</label>
                <select name="nam" class="form-select form-select-sm">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ ($nam ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            
            @if(isset($showLimit) && $showLimit)
            <div class="col-md-2">
                <label class="form-label small mb-1">Số lượng hiển thị</label>
                <select name="limit" class="form-select form-select-sm">
                    <option value="10" {{ ($limit ?? 20) == 10 ? 'selected' : '' }}>Top 10</option>
                    <option value="20" {{ ($limit ?? 20) == 20 ? 'selected' : '' }}>Top 20</option>
                    <option value="50" {{ ($limit ?? 20) == 50 ? 'selected' : '' }}>Top 50</option>
                    <option value="100" {{ ($limit ?? 20) == 100 ? 'selected' : '' }}>Top 100</option>
                </select>
            </div>
            @endif
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search me-1"></i>Xem báo cáo
                </button>
            </div>
        </form>
        
        @if(isset($startDate) && isset($endDate))
        <div class="mt-2">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Hiển thị dữ liệu từ <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</strong> 
                đến <strong>{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
            </small>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#loaiThoiGian').on('change', function() {
        const val = $(this).val();
        $('.filter-ngay, .filter-thang, .filter-nam').hide();
        
        if (val === 'ngay') {
            $('.filter-ngay').show();
        } else if (val === 'thang') {
            $('.filter-thang').show();
        } else if (val === 'nam') {
            $('.filter-nam').show();
        }
        // Tuần này không cần input thêm
    });
});
</script>
@endpush
