<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Sản Phẩm từ Excel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9ff;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #764ba2;
            background-color: #f0f0ff;
        }
        .upload-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .instruction-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .example-table {
            font-size: 0.85rem;
            margin-top: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold">
                        <i class="bi bi-file-earmark-excel text-success"></i>
                        Import Sản Phẩm từ Excel
                    </h2>
                    <p class="text-muted">Nhập dữ liệu hàng loạt từ file Excel, CSV</p>
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle-fill"></i> {{ session('warning') }}
                        @if(session('errors'))
                            <ul class="mb-0 mt-2">
                                @foreach(session('errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Có lỗi xảy ra:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Upload Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-upload"></i> Tải lên File Excel</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('san-pham.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf

                            <div class="upload-area" id="uploadArea">
                                <i class="bi bi-cloud-upload upload-icon"></i>
                                <h5>Kéo thả file vào đây hoặc click để chọn</h5>
                                <p class="text-muted mb-3">Hỗ trợ: .xlsx, .xls, .csv (Tối đa 5MB)</p>
                                <input type="file" name="file" id="fileInput" class="d-none" accept=".xlsx,.xls,.csv" required>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                    <i class="bi bi-folder2-open"></i> Chọn File
                                </button>
                            </div>

                            <div id="fileInfo" class="mt-3 d-none">
                                <div class="alert alert-info">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <strong>File đã chọn:</strong> <span id="fileName"></span>
                                    <span class="badge bg-primary ms-2" id="fileSize"></span>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                    <i class="bi bi-upload"></i> Bắt đầu Import
                                </button>
                                <a href="{{ route('san-pham.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Quay lại danh sách
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Hướng dẫn định dạng File Excel</h5>
                    </div>
                    <div class="card-body">
                        <div class="instruction-box">
                            <h6><i class="bi bi-lightbulb"></i> Lưu ý quan trọng:</h6>
                            <ul class="mb-0">
                                <li>Dòng đầu tiên phải là tiêu đề các cột</li>
                                <li>Tên cột phải chính xác theo mẫu bên dưới</li>
                                <li>Cột <strong>"Hàng"</strong> là bắt buộc, các cột khác có thể để trống</li>
                                <li>Số tiền có thể nhập với dấu phẩy (ví dụ: 50,000 hoặc 50000)</li>
                                <li>Số lượng có thể là số thập phân (ví dụ: 2.5, 0.75)</li>
                            </ul>
                        </div>

                        <h6 class="mt-4 mb-3"><i class="bi bi-table"></i> Cấu trúc file Excel mẫu:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered example-table">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Hàng</th>
                                        <th>Đơn vị</th>
                                        <th>Giá Nhập Vào</th>
                                        <th>Giá Bán Ra</th>
                                        <th>Giá Bán Lẻ</th>
                                        <th>Số Lượng Hàng</th>
                                        <th>Số lượng đơn vị</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Coca Cola</td>
                                        <td>Thùng</td>
                                        <td>120,000</td>
                                        <td>150,000</td>
                                        <td>7,000</td>
                                        <td>10</td>
                                        <td>240</td>
                                        <td>Hàng mới về</td>
                                    </tr>
                                    <tr>
                                        <td>Mì Hảo Hảo</td>
                                        <td>Thùng</td>
                                        <td>85,000</td>
                                        <td>100,000</td>
                                        <td>3,500</td>
                                        <td>5.5</td>
                                        <td>165</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Nước suối Lavie</td>
                                        <td>Thùng</td>
                                        <td>45000</td>
                                        <td>55000</td>
                                        <td>3000</td>
                                        <td>8</td>
                                        <td>192</td>
                                        <td>Chai 500ml</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="bi bi-download"></i>
                            <strong>Mẹo:</strong> Bạn có thể xuất danh sách sản phẩm hiện tại ra Excel để làm mẫu,
                            sau đó chỉnh sửa và import lại.
                        </div>

                        <h6 class="mt-4"><i class="bi bi-gear"></i> Các cột được hỗ trợ:</h6>
                        <ul>
                            <li><strong>Hàng</strong> hoặc <strong>ten_san_pham</strong>: Tên sản phẩm (bắt buộc)</li>
                            <li><strong>Đơn vị</strong> hoặc <strong>don_vi</strong> hoặc <strong>dv_nhap_hang</strong>: Đơn vị tính</li>
                            <li><strong>Giá Nhập Vào</strong> hoặc <strong>gia_nhap</strong>: Giá nhập</li>
                            <li><strong>Giá Bán Ra</strong> hoặc <strong>gia_ban</strong>: Giá bán sỉ</li>
                            <li><strong>Giá Bán Lẻ</strong> hoặc <strong>gia_ban_le</strong>: Giá bán lẻ</li>
                            <li><strong>Số Lượng Hàng</strong> hoặc <strong>so_luong</strong>: Số lượng (có thể là số thập phân)</li>
                            <li><strong>Số lượng đơn vị</strong> hoặc <strong>so_luong_don_vi</strong>: Số lượng đơn vị nhỏ</li>
                            <li><strong>Ghi chú</strong> hoặc <strong>ghi_chu</strong>: Ghi chú thêm</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const submitBtn = document.getElementById('submitBtn');
        const uploadArea = document.getElementById('uploadArea');

        // Handle file selection
        fileInput.addEventListener('change', function(e) {
            if (this.files.length > 0) {
                const file = this.files[0];
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.classList.remove('d-none');
                submitBtn.disabled = false;
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#764ba2';
            this.style.backgroundColor = '#f0f0ff';
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#667eea';
            this.style.backgroundColor = '#f8f9ff';
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#667eea';
            this.style.backgroundColor = '#f8f9ff';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        });

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Form submission
        document.getElementById('importForm').addEventListener('submit', function() {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
