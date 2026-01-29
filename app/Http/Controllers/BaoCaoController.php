<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\NhapHang;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BaoCaoController extends Controller
{
    /**
     * Trang tổng quan báo cáo
     */
    public function index(Request $request)
    {
        $loaiThoiGian = $request->get('loai_thoi_gian', 'ngay');
        $tuNgay = $request->get('tu_ngay', Carbon::today()->format('Y-m-d'));
        $denNgay = $request->get('den_ngay', Carbon::today()->format('Y-m-d'));
        $thang = $request->get('thang', Carbon::today()->format('Y-m'));
        $nam = $request->get('nam', Carbon::today()->format('Y'));
        
        // Xác định khoảng thời gian
        list($startDate, $endDate) = $this->getDateRange($loaiThoiGian, $tuNgay, $denNgay, $thang, $nam);
        
        // Lấy dữ liệu đơn hàng
        $donHangs = DonHang::whereBetween('ngay_ban', [$startDate, $endDate])->get();
        
        // Thống kê tổng quan
        $tongDoanhThu = $donHangs->sum(fn($dh) => $dh->so_luong * $dh->gia);
        $tongGiaVon = $donHangs->sum(fn($dh) => $dh->so_luong * $dh->gia_nhap);
        $tongLoiNhuan = $tongDoanhThu - $tongGiaVon;
        $tyLeLoiNhuan = $tongDoanhThu > 0 ? ($tongLoiNhuan / $tongDoanhThu) * 100 : 0;
        $tongSoLuong = $donHangs->sum('so_luong');
        $soDon = $donHangs->count();
        
        // Thống kê nhập hàng
        $nhapHangs = NhapHang::whereBetween('ngay_nhap', [$startDate, $endDate])->get();
        $tongTienNhap = $nhapHangs->sum(fn($nh) => $nh->so_luong * $nh->gia_nhap);
        
        // Thống kê theo ngày
        $thongKeTheoNgay = $donHangs->groupBy(fn($item) => $item->ngay_ban->format('Y-m-d'))
            ->map(function ($items, $date) {
                $doanhThu = $items->sum(fn($i) => $i->so_luong * $i->gia);
                $giaVon = $items->sum(fn($i) => $i->so_luong * $i->gia_nhap);
                return [
                    'ngay' => $date,
                    'so_don' => $items->count(),
                    'so_luong' => $items->sum('so_luong'),
                    'doanh_thu' => $doanhThu,
                    'gia_von' => $giaVon,
                    'loi_nhuan' => $doanhThu - $giaVon,
                ];
            })->sortByDesc('ngay')->values();

        return view('bao-cao.index', compact(
            'donHangs', 'loaiThoiGian', 'tuNgay', 'denNgay', 'thang', 'nam',
            'tongDoanhThu', 'tongGiaVon', 'tongLoiNhuan', 'tyLeLoiNhuan', 
            'tongSoLuong', 'soDon', 'tongTienNhap', 'thongKeTheoNgay',
            'startDate', 'endDate'
        ));
    }

    /**
     * Báo cáo doanh thu
     */
    public function doanhThu(Request $request)
    {
        $loaiThoiGian = $request->get('loai_thoi_gian', 'ngay');
        $tuNgay = $request->get('tu_ngay', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $denNgay = $request->get('den_ngay', Carbon::today()->format('Y-m-d'));
        $thang = $request->get('thang', Carbon::today()->format('Y-m'));
        $nam = $request->get('nam', Carbon::today()->format('Y'));
        
        list($startDate, $endDate) = $this->getDateRange($loaiThoiGian, $tuNgay, $denNgay, $thang, $nam);

        $baoCao = DonHang::whereBetween('ngay_ban', [$startDate, $endDate])
            ->select(
                'ngay_ban',
                DB::raw('COUNT(*) as so_don'),
                DB::raw('SUM(so_luong) as tong_so_luong'),
                DB::raw('SUM(so_luong * gia) as doanh_thu'),
                DB::raw('SUM(so_luong * gia_nhap) as gia_von'),
                DB::raw('SUM(so_luong * gia) - SUM(so_luong * gia_nhap) as loi_nhuan')
            )
            ->groupBy('ngay_ban')
            ->orderBy('ngay_ban', 'desc')
            ->get();

        $tongDoanhThu = $baoCao->sum('doanh_thu');
        $tongGiaVon = $baoCao->sum('gia_von');
        $tongLoiNhuan = $baoCao->sum('loi_nhuan');
        $tyLeLoiNhuan = $tongDoanhThu > 0 ? ($tongLoiNhuan / $tongDoanhThu) * 100 : 0;
        
        // Data cho biểu đồ
        $chartLabels = $baoCao->pluck('ngay_ban')->map(fn($d) => Carbon::parse($d)->format('d/m'))->reverse()->values();
        $chartDoanhThu = $baoCao->pluck('doanh_thu')->reverse()->values();
        $chartLoiNhuan = $baoCao->pluck('loi_nhuan')->reverse()->values();

        return view('bao-cao.doanh-thu', compact(
            'baoCao', 'loaiThoiGian', 'tuNgay', 'denNgay', 'thang', 'nam',
            'tongDoanhThu', 'tongGiaVon', 'tongLoiNhuan', 'tyLeLoiNhuan',
            'chartLabels', 'chartDoanhThu', 'chartLoiNhuan', 'startDate', 'endDate'
        ));
    }

    /**
     * Báo cáo lợi nhuận
     */
    public function loiNhuan(Request $request)
    {
        $loaiThoiGian = $request->get('loai_thoi_gian', 'thang');
        $tuNgay = $request->get('tu_ngay', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $denNgay = $request->get('den_ngay', Carbon::today()->format('Y-m-d'));
        $thang = $request->get('thang', Carbon::today()->format('Y-m'));
        $nam = $request->get('nam', Carbon::today()->format('Y'));
        
        list($startDate, $endDate) = $this->getDateRange($loaiThoiGian, $tuNgay, $denNgay, $thang, $nam);

        // Báo cáo theo sản phẩm
        $baoCaoSanPham = DonHang::whereBetween('ngay_ban', [$startDate, $endDate])
            ->select(
                'san_pham_id',
                'ten_san_pham',
                DB::raw('SUM(so_luong) as tong_so_luong'),
                DB::raw('SUM(so_luong * gia) as doanh_thu'),
                DB::raw('SUM(so_luong * gia_nhap) as gia_von'),
                DB::raw('SUM(so_luong * gia) - SUM(so_luong * gia_nhap) as loi_nhuan')
            )
            ->groupBy('san_pham_id', 'ten_san_pham')
            ->orderBy('loi_nhuan', 'desc')
            ->get()
            ->map(function ($item) {
                $item->ty_le_loi_nhuan = $item->doanh_thu > 0 ? ($item->loi_nhuan / $item->doanh_thu) * 100 : 0;
                return $item;
            });

        // Báo cáo theo ngày
        $baoCaoNgay = DonHang::whereBetween('ngay_ban', [$startDate, $endDate])
            ->select(
                'ngay_ban',
                DB::raw('SUM(so_luong * gia) as doanh_thu'),
                DB::raw('SUM(so_luong * gia_nhap) as gia_von'),
                DB::raw('SUM(so_luong * gia) - SUM(so_luong * gia_nhap) as loi_nhuan')
            )
            ->groupBy('ngay_ban')
            ->orderBy('ngay_ban', 'desc')
            ->get();

        $tongDoanhThu = $baoCaoSanPham->sum('doanh_thu');
        $tongGiaVon = $baoCaoSanPham->sum('gia_von');
        $tongLoiNhuan = $baoCaoSanPham->sum('loi_nhuan');
        $tyLeLoiNhuan = $tongDoanhThu > 0 ? ($tongLoiNhuan / $tongDoanhThu) * 100 : 0;
        
        // Data cho biểu đồ pie
        $top5SanPham = $baoCaoSanPham->take(5);
        $chartLabels = $top5SanPham->pluck('ten_san_pham');
        $chartData = $top5SanPham->pluck('loi_nhuan');
        
        // Data cho biểu đồ line
        $lineLabels = $baoCaoNgay->pluck('ngay_ban')->map(fn($d) => Carbon::parse($d)->format('d/m'))->reverse()->values();
        $lineData = $baoCaoNgay->pluck('loi_nhuan')->reverse()->values();

        return view('bao-cao.loi-nhuan', compact(
            'baoCaoSanPham', 'baoCaoNgay', 'loaiThoiGian', 'tuNgay', 'denNgay', 'thang', 'nam',
            'tongDoanhThu', 'tongGiaVon', 'tongLoiNhuan', 'tyLeLoiNhuan',
            'chartLabels', 'chartData', 'lineLabels', 'lineData', 'startDate', 'endDate'
        ));
    }

    /**
     * Báo cáo sản phẩm bán chạy
     */
    public function sanPhamBanChay(Request $request)
    {
        $loaiThoiGian = $request->get('loai_thoi_gian', 'thang');
        $tuNgay = $request->get('tu_ngay', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $denNgay = $request->get('den_ngay', Carbon::today()->format('Y-m-d'));
        $thang = $request->get('thang', Carbon::today()->format('Y-m'));
        $nam = $request->get('nam', Carbon::today()->format('Y'));
        $limit = $request->get('limit', 20);
        
        list($startDate, $endDate) = $this->getDateRange($loaiThoiGian, $tuNgay, $denNgay, $thang, $nam);

        $baoCao = DonHang::whereBetween('ngay_ban', [$startDate, $endDate])
            ->select(
                'san_pham_id',
                'ten_san_pham',
                DB::raw('COUNT(*) as so_lan_ban'),
                DB::raw('SUM(so_luong) as tong_so_luong'),
                DB::raw('SUM(so_luong * gia) as doanh_thu'),
                DB::raw('SUM(so_luong * gia) - SUM(so_luong * gia_nhap) as loi_nhuan')
            )
            ->groupBy('san_pham_id', 'ten_san_pham')
            ->orderBy('tong_so_luong', 'desc')
            ->limit($limit)
            ->get();

        $tongDoanhThu = $baoCao->sum('doanh_thu');
        $tongSoLuong = $baoCao->sum('tong_so_luong');
        
        // Data cho biểu đồ
        $top10 = $baoCao->take(10);
        $chartLabels = $top10->pluck('ten_san_pham');
        $chartSoLuong = $top10->pluck('tong_so_luong');
        $chartDoanhThu = $top10->pluck('doanh_thu');

        return view('bao-cao.san-pham-ban-chay', compact(
            'baoCao', 'loaiThoiGian', 'tuNgay', 'denNgay', 'thang', 'nam', 'limit',
            'tongDoanhThu', 'tongSoLuong',
            'chartLabels', 'chartSoLuong', 'chartDoanhThu', 'startDate', 'endDate'
        ));
    }

    /**
     * Báo cáo sản phẩm nhập hàng
     */
    public function sanPhamNhap(Request $request)
    {
        $loaiThoiGian = $request->get('loai_thoi_gian', 'thang');
        $tuNgay = $request->get('tu_ngay', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $denNgay = $request->get('den_ngay', Carbon::today()->format('Y-m-d'));
        $thang = $request->get('thang', Carbon::today()->format('Y-m'));
        $nam = $request->get('nam', Carbon::today()->format('Y'));
        
        list($startDate, $endDate) = $this->getDateRange($loaiThoiGian, $tuNgay, $denNgay, $thang, $nam);

        $baoCao = NhapHang::whereBetween('ngay_nhap', [$startDate, $endDate])
            ->select(
                'san_pham_id',
                'ten_san_pham',
                DB::raw('COUNT(*) as so_lan_nhap'),
                DB::raw('SUM(so_luong) as tong_so_luong'),
                DB::raw('SUM(so_luong * gia_nhap) as tong_tien_nhap'),
                DB::raw('AVG(gia_nhap) as gia_nhap_tb')
            )
            ->groupBy('san_pham_id', 'ten_san_pham')
            ->orderBy('tong_tien_nhap', 'desc')
            ->get();

        $tongTienNhap = $baoCao->sum('tong_tien_nhap');
        $tongSoLuong = $baoCao->sum('tong_so_luong');
        
        // Data cho biểu đồ
        $top10 = $baoCao->take(10);
        $chartLabels = $top10->pluck('ten_san_pham');
        $chartData = $top10->pluck('tong_tien_nhap');

        return view('bao-cao.san-pham-nhap', compact(
            'baoCao', 'loaiThoiGian', 'tuNgay', 'denNgay', 'thang', 'nam',
            'tongTienNhap', 'tongSoLuong',
            'chartLabels', 'chartData', 'startDate', 'endDate'
        ));
    }

    /**
     * Báo cáo số liệu nhập hàng
     */
    public function soLieuNhap(Request $request)
    {
        $loaiThoiGian = $request->get('loai_thoi_gian', 'thang');
        $tuNgay = $request->get('tu_ngay', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $denNgay = $request->get('den_ngay', Carbon::today()->format('Y-m-d'));
        $thang = $request->get('thang', Carbon::today()->format('Y-m'));
        $nam = $request->get('nam', Carbon::today()->format('Y'));
        
        list($startDate, $endDate) = $this->getDateRange($loaiThoiGian, $tuNgay, $denNgay, $thang, $nam);

        // Báo cáo theo ngày
        $baoCaoNgay = NhapHang::whereBetween('ngay_nhap', [$startDate, $endDate])
            ->select(
                'ngay_nhap',
                DB::raw('COUNT(*) as so_phieu'),
                DB::raw('SUM(so_luong) as tong_so_luong'),
                DB::raw('SUM(so_luong * gia_nhap) as tong_tien_nhap')
            )
            ->groupBy('ngay_nhap')
            ->orderBy('ngay_nhap', 'desc')
            ->get();

        // Chi tiết nhập hàng
        $chiTiet = NhapHang::with(['sanPham', 'donViBan'])
            ->whereBetween('ngay_nhap', [$startDate, $endDate])
            ->orderBy('ngay_nhap', 'desc')
            ->get();

        $tongTienNhap = $baoCaoNgay->sum('tong_tien_nhap');
        $tongSoLuong = $baoCaoNgay->sum('tong_so_luong');
        $soPhieu = $baoCaoNgay->sum('so_phieu');
        
        // Data cho biểu đồ
        $chartLabels = $baoCaoNgay->pluck('ngay_nhap')->map(fn($d) => Carbon::parse($d)->format('d/m'))->reverse()->values();
        $chartData = $baoCaoNgay->pluck('tong_tien_nhap')->reverse()->values();

        return view('bao-cao.so-lieu-nhap', compact(
            'baoCaoNgay', 'chiTiet', 'loaiThoiGian', 'tuNgay', 'denNgay', 'thang', 'nam',
            'tongTienNhap', 'tongSoLuong', 'soPhieu',
            'chartLabels', 'chartData', 'startDate', 'endDate'
        ));
    }

    /**
     * Hàm helper xác định khoảng thời gian
     */
    private function getDateRange($loaiThoiGian, $tuNgay, $denNgay, $thang, $nam)
    {
        switch ($loaiThoiGian) {
            case 'tuan':
                $startDate = Carbon::today()->startOfWeek();
                $endDate = Carbon::today()->endOfWeek();
                break;
            case 'thang':
                $startDate = Carbon::parse($thang . '-01')->startOfMonth();
                $endDate = Carbon::parse($thang . '-01')->endOfMonth();
                break;
            case 'nam':
                $startDate = Carbon::parse($nam . '-01-01')->startOfYear();
                $endDate = Carbon::parse($nam . '-12-31')->endOfYear();
                break;
            case 'ngay':
            default:
                $startDate = Carbon::parse($tuNgay);
                $endDate = Carbon::parse($denNgay);
                break;
        }
        return [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')];
    }
}
