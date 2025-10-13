<table class="table table-striped table-bordered">
    <thead class="table-dark">
    <tr>
        <th>No</th>
        <th>Judul Buku</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    @forelse($bukus as $index => $buku)
        <tr>
            <td>{{ ($bukus->currentPage() - 1) * $bukus->perPage() + $loop->iteration }}</td>
            <td>{{ $buku->judul }}</td>
            <td>
                <button class="btn btn-sm btn-primary pilih-buku"
                        data-id="{{ $buku->id_buku }}"
                        data-judul="{{ $buku->judul }}">
                    Pilih
                </button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="text-center">Tidak ada buku ditemukan.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<!-- Pagination links -->
<div class="d-flex justify-content-center">
    {{ $bukus->links('pagination::bootstrap-5') }}
</div>
