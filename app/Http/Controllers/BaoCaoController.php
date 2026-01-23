<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BaoCaoController extends Controller
{
    public function index(Request $request)
    {
        $loai = $request->get('loai', 'ngay');
        $tuNgay = $request->get('tu_ngay', Carbon::today()->format('Y-m-d'));
        $denNgay = $request->get('den_ngay', Carbon::today()->format('Y-m-d'));
        $thang = $request->get('thang', Carbon::today()->format('Y-m'));

        $query = DonHang::query();

        if ($loai === 'ngay') {
            $query->whereBetween('ngay_ban', [$tuNgay, $denNgay]);
        } else {
            $startOfMonth = Carbon::parse($thang . '-01')->startOfMonth();
            $endOfMonth = Carbon::parse($thang . '-01')->endOfMonth();
            $query->whereBetween('ngay_ban', [$startOfMonth, $endOfMonth]);
        }

        $donHangs = $query->orderBy('ngay_ban', 'desc')->get();

        $tongDoanhThu = $donHangs->sum(function ($item) {
            return $item->so_luong * $item->gia;
        });

        $tongSoLuong = $donHangs->sum('so_luong');

        // Thống kê theo ngày
        $thongKeTheoNgay = $donHangs->groupBy(function ($item) {
            return $item->ngay_ban->format('Y-m-d');
        })->map(function ($items, $date) {
            return [
                'ngay' => $date,
                'so_don' => $items->count(),
                'so_luong' => $items->sum('so_luong'),
                'doanh_thu' => $items->sum(function ($item) {
                    return $item->so_luong * $item->gia;
                })
            ];
        })->values();

        return view('bao-cao.index', compact(
            'donHangs', 'loai', 'tuNgay', 'denNgay', 'thang',
            'tongDoanhThu', 'tongSoLuong', 'thongKeTheoNgay'

}
    /**
     * Báo cáo doanh thu
     */
    public function doanhThu(Request $request)
    {
        $loai = $request->get('loai', 'ngay'); // ngay, thang
        $tuNgay = $request->get('tu_ngay', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $denNgay = $request->get('den_ngay', Carbon::today()->format('Y-m-d'));
        $thang = $request->get('thang', Carbon::today()->format('Y-m'));

        if ($loai === 'ngay') {
            // Báo cáo theo khoảng ngày
            $baoCao = DonHang::whereBetween('ngay_ban', [$tuNgay, $denNgay])
                ->select(
                    'ngay_ban',
                    DB::raw('COUNT(*) as so_don'),
                    DB::raw('SUM(so_luong) as tong_so_luong'),
                    DB::raw('SUM(so_luong * gia) as doanh_thu')
                )
                ->groupBy('ngay_ban')
                ->orderBy('ngay_ban', 'desc')
                ->get();

            $tongDoanhThu = $baoCao->sum('doanh_thu');
        } else {
            // Báo cáo theo tháng
            $year = substr($thang, 0, 4);
            $month = substr($thang, 5, 2);
            
            $baoCao = DonHang::whereYear('ngay_ban', $year)
                ->whereMonth('ngay_ban', $month)
                ->select(
                    'ngay_ban',
                    DB::raw('COUNT(*) as so_don'),
                    DB::raw('SUM(so_luong) as tong_so_luong'),
                    DB::raw('SUM(so_luong * gia) as doanh_thu')
                )
                ->groupBy('ngay_ban')
                ->orderBy('ngay_ban', 'desc')
                ->get();

            $tongDoanhThu = $baoCao->sum('doanh_thu');
            $tuNgay = $year . '-' . $month . '-01';
            $denNgay = Carbon::parse($tuNgay)->endOfMonth()->format('Y-m-d');
        }

        return view('bao-cao.doanh-thu', compact(
            'baoCao', 'loai', 'tuNgay', 'denNgay', 'thang', 'tongDoanhThu'
        ));
    }
}
