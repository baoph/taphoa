@extends('layouts.app')

@section('title', 'Quản lý Sản phẩm')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-box me-2"></i>Danh sách Sản phẩm</h5>
        <div>
            <a href="{{ route('san-pham.import.form') }}" class="btn btn-success btn-sm me-2">
                <i class="fas fa-file-excel me-1"></i>Import Excel
            </a>
            <a href="{{ route('san-pham.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Thêm sản phẩm
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Search Box -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text"
                           class="form-control"
                           id="searchInput"
                           placeholder="Tìm kiếm tên sản phẩm..."
                           value="{{ $search ?? '' }}">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th>Tên sản phẩm</th>
                        <th style="width: 80px;">dv_nhap_hang</th>
                        <th style="width: 100px;" class="text-end">Giá nhập</th>
                        <th style="width: 100px;" class="text-end">Giá bán</th>
                        <th style="width: 100px;" class="text-end">Giá bán lẻ</th>
                        <th style="width: 80px;" class="text-center">Số lượng</th>
                        <th style="width: 80px;" class="text-center">Tỉ số</th>
                        <th style="width: 80px;" class="text-center">SL đơn vị</th>
                        <th style="width: 100px;" class="text-center">Đơn vị bán</th>
                        <th style="width: 150px;">Ghi chú</th>
                        <th style="width: 150px;" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="sanPhamTableBody">
                    @include('san-pham.partials.table')
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center" id="paginationContainer">
            @include('san-pham.partials.pagination')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let searchTimeout;

    // AJAX Search
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const searchValue = $(this).val();

        searchTimeout = setTimeout(function() {
            performSearch(searchValue);
        }, 300); // Debounce 300ms
    });

    function performSearch(search) {
        $.ajax({
            url: '{{ route("san-pham.search.ajax") }}',
            type: 'GET',
            data: { search: search },
            success: function(response) {
                if (response.success) {
                    $('#sanPhamTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);
                }
            },
            error: function() {
                toastr.error('Có lỗi xảy ra khi tìm kiếm');
            }
        });
    }

    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const search = $('#searchInput').val();

        $.ajax({
            url: url,
            type: 'GET',
            data: { search: search },
            success: function(response) {
                if (response.success) {
                    $('#sanPhamTableBody').html(response.html);
                    $('#paginationContainer').html(response.pagination);

                    // Scroll to top of table
                    $('html, body').animate({
                        scrollTop: $('.card').offset().top - 20
                    }, 300);
                }
            }
        });
    });
});
</script>
@endpush
