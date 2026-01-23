@forelse($sanPhams as $index => $sp)
<tr>
    <td>{{ $sanPhams->firstItem() + $index }}</td>
    <td>{{ $sp->ten_san_pham }}</td>
    <td>{{ $sp->dvt }}</td>
    <td class="text-end">{{ number_format($sp->gia_nhap, 0, ',', '.') }}đ</td>
    <td class="text-end">{{ number_format($sp->gia_ban, 0, ',', '.') }}đ</td>
    <td class="text-end">{{ number_format($sp->gia_ban_le, 0, ',', '.') }}đ</td>
    <td class="text-center">{{ number_format($sp->so_luong ?? 0, 2, ',', '.') }}</td>
    <td class="text-center">{{ number_format($sp->ti_so_chuyen_doi ?? 1, 2, ',', '.') }}</td>
    <td class="text-center">{{ number_format($sp->so_luong_don_vi ?? 0, 2, ',', '.') }}</td>
    <td>{{ $sp->ghi_chu ? Str::limit($sp->ghi_chu, 30) : '-' }}</td>
    <td class="text-center">
        <a href="{{ route('san-pham.edit', $sp) }}" class="btn btn-warning btn-action" title="Sửa">
            <i class="fas fa-edit"></i>
        </a>
        <form action="{{ route('san-pham.destroy', $sp) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-action" title="Xóa">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </td>
</tr>
@empty
<tr>
    <td colspan="11" class="text-center text-muted">Không tìm thấy sản phẩm nào</td>
</tr>
@endforelse
