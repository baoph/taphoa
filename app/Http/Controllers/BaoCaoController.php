<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BaoCaoController extends Controller
{
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
