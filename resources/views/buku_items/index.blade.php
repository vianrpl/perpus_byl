@extends('layouts.app')

@section('content')
    <h2 class="mb-3">Daftar Item Buku: {{ $buku->judul }}</h2>

    {{-- 🔍 Baris Atas: Search + Navigasi + Filter --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">

        {{-- 🔍 Form Search --}}
        <form action="{{ route('bukus.items.index', $buku->id_buku) }}" method="GET" class="flex-grow-1" style="max-width:420px;">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari Barcode"
                       value="{{ request('search') }}">
                <button class="btn btn-dark" type="submit">Cari</button>
                @if(request('search'))
                    <a href="{{ route('bukus.items.index', $buku->id_buku) }}" class="btn btn-dark">Reset</a>
                @endif
                {{-- 🧮 Tombol Filter --}}
                <button type="button" class="btn btn-outline-secondary d-flex align-items-center"
                        data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="bi bi-funnel me-2"></i> Filter
                </button>
            </div>
        </form>
    {{-- 🔹 Tentukan ID rak asal --}}
    @php
        // Jika datang dari show rak, ambil dari query string ?from_rak=...
        // Jika tidak ada, fallback ke id_rak dari item pertama (kalau ada)
        $rakId = request('from_rak') ?? (isset($items) && $items->isNotEmpty() ? $items->first()->id_rak : null);
    @endphp


    {{-- 🔹 Tombol navigasi --}}
        <div class="d-flex align-items-center" style="gap:8px;">
    <a href="{{ route('bukus.index') }}" class="btn btn-primary mb-1">Daftar Buku</a>
    @if($rakId)
        {{-- 🔹 Jika ada rakId, maka tombol Rak akan kembali ke show rak asal --}}
        <a href="{{ route('raks.show', $rakId) }}" class="btn btn-primary mb-1">Rak</a>
    @else
        {{-- 🔹 Jika tidak ada rakId, fallback ke index semua rak --}}
        <a href="{{ route('raks.index') }}" class="btn btn-primary mb-1">Rak</a>
    @endif
    </div>
    </div>
    </div>


    {{-- 🗑️ Tombol Hapus Terpilih --}}
    <form id="bulkDeleteForm"
          action="{{ route('bukus.items.bulkDelete', $buku->id_buku) }}"
          method="POST" class="mb-3">
        @csrf
        <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
            <i class="bi bi-trash me-1"></i> Hapus Terpilih
        </button>



    {{-- Tabel daftar item --}}
    <div class="table table-responsive">
        <table class="table table-bordered table-striped fade-in">
            <thead class="table-dark">
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>ID</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Sumber</th>
                <th>Rak</th>
                <th>Barcode</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td><input type="checkbox" name="ids[]" class="rowCheckbox" value="{{ $item->id_item }}"></td>
                    <td>{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                    <td>{{ $item->kondisi }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->sumber }}</td>
                    <td>{{ $item->rak->nama ?? $item->id_rak }}</td>
                    <td>{{ $item->barcode }}</td>
                    <td>
                        <!-- Tombol LIHAT -->
                        <button type="button" class="btn btn-info btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalShowItem{{ $item->id_item }}">
                            Lihat
                        </button>

                        <!-- Tombol EDIT -->
                        @unless($item->kondisi === 'hilang' || $item->status === 'hilang' || Auth::user()->role === 'konsumen')
                            <button type="button" class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditItem{{ $item->id_item }}">
                                Edit
                            </button>
                        @endunless

                        <!-- Tombol HAPUS -->
                        @unless(Auth::user()->role === 'konsumen')
                            <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalHapusItem{{ $item->id_item }}">
                                Hapus
                            </button>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada item untuk buku ini</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </form>
    {{-- 🔹 Pagination tetap membawa query from_rak agar tidak hilang saat pindah halaman --}}
    <div class="d-flex justify-content-center">
        {{ $items->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    <!-- ============================= -->
    <!-- Modal Tambah Item -->
    <!-- ============================= -->
    <div class="modal fade" id="modalTambahBuku" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Item Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('bukus.items.store', $buku->id_buku) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kondisi</label>
                            <select name="kondisi" class="form-control">
                                <option value="baik">Baik</option>
                                <option value="rusak">Rusak</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="tersedia">Tersedia</option>
                                <option value="dipinjam">Dipinjam</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sumber</label>
                            <input type="text" name="sumber" value="{{ old('sumber') }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rak</label>
                            <select name="id_rak" class="form-control">
                                @foreach($raks as $rak)
                                    <option value="{{ $rak->id_rak }}">{{ $rak->nama ?? $rak->nama_rak }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================= -->
    <!-- Modal Lihat/Edit/Hapus per Item -->
    <!-- ============================= -->
    @foreach ($items as $item)
        <!-- Modal Lihat -->
        <div class="modal fade" id="modalShowItem{{ $item->id_item }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Detail Item </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><b>Barcode:</b> {{ $item->barcode ?? '-' }}</p>
                        <p><b>Kondisi:</b> {{ $item->kondisi }}</p>
                        <p><b>Status:</b> {{ $item->status }}</p>
                        <p><b>Sumber:</b> {{ $item->sumber ?? '-' }}</p>
                        <p><b>Rak:</b> {{ $item->rak->nama ?? $item->id_rak }}</p>
                        <hr>
                        <p><b>Insert Date:</b> {{ $item->insert_date ? $item->insert_date->format('d M Y H:i') : '-' }}</p>
                        <p><b>Modified Date:</b> {{ $item->modified_date ? $item->modified_date->format('d M Y H:i') : '-' }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditItem{{ $item->id_item }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Item </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('bukus.items.update', [$buku->id_buku, $item->id_item]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Kondisi</label>
                                <select name="kondisi" class="form-control">
                                    <option value="baik" {{ $item->kondisi=='baik'?'selected':'' }}>Baik</option>
                                    <option value="rusak" {{ $item->kondisi=='rusak'?'selected':'' }}>Rusak</option>
                                    <option value="hilang" {{ $item->kondisi=='hilang'?'selected':'' }}>Hilang</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="tersedia" {{ $item->status=='tersedia'?'selected':'' }}>Tersedia</option>
                                    <option value="dipinjam" {{ $item->status=='dipinjam'?'selected':'' }}>Dipinjam</option>
                                    <option value="hilang" {{ $item->status=='hilang'?'selected':'' }}>Hilang</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Sumber</label>
                                <input type="text" name="sumber" value="{{ $item->sumber }}" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rak</label>
                                <select name="id_rak" class="form-control">
                                    @foreach($raks as $rak)
                                        <option value="{{ $rak->id_rak }}" {{ $item->id_rak==$rak->id_rak?'selected':'' }}>
                                            {{ $rak->nama ?? $rak->nama_rak }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div class="modal fade" id="modalHapusItem{{ $item->id_item }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus item buku <b>{{ $buku->judul }}</b>?
                    </div>
                    <form action="{{ route('bukus.items.destroy', [$buku->id_buku, $item->id_item]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach


    <!-- Modal Konfirmasi Bulk Delete -->
    <div class="modal fade" id="confirmBulkDeleteModal" tabindex="-1" aria-labelledby="confirmBulkDeleteLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmBulkDeleteLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <p id="confirmText">Yakin ingin menghapus data terpilih?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="confirmBulkDeleteBtn" class="btn btn-danger">Hapus</button>
                </div>
            </div>
        </div>
    </div>


    <!-- 🧮 Modal Filter -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="filterModalLabel">Filter Data Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('bukus.items.index',$buku->id_buku) }}" method="GET">
                    <div class="modal-body">
                        {{-- Role --}}
                        <div class="mb-3">
                            <label for="filter_kondisi" class="form-label">Kondisi</label>
                            <select name="filter_kondisi" id="filter_kondisi" class="form-select">
                                <option value="">-- Semua --</option>
                                <option value="baik" {{ request('filter_kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak" {{ request('filter_kondisi') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="hilang" {{ request('filter_kondisi') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label for="filter_status" class="form-label">Status</label>
                            <select name="filter_status" id="filter_status" class="form-select">
                                <option value="">-- Semua --</option>
                                <option value="tersedia" {{ request('filter_status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="dipinjam" {{ request('filter_status') == 'dipinjam' ? 'selected' : '' }}>Di pinjam</option>
                                <option value="hilang" {{ request('filter_status') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('bukus.items.index',$buku->id_buku) }}" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const bulkForm = document.getElementById('bulkDeleteForm');
            const modal = new bootstrap.Modal(document.getElementById('confirmBulkDeleteModal'));
            const confirmBtn = document.getElementById('confirmBulkDeleteBtn');

            function updateButton() {
                const checked = document.querySelectorAll('.rowCheckbox:checked').length;
                bulkDeleteBtn.disabled = checked === 0;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
                    updateButton();
                });
            }

            document.addEventListener('change', function (e) {
                if (e.target.matches('.rowCheckbox')) updateButton();
            });

            bulkDeleteBtn.addEventListener('click', function () {
                const selected = document.querySelectorAll('.rowCheckbox:checked').length;
                if (selected === 0) return;
                document.getElementById('confirmText').textContent = `Yakin ingin menghapus ${selected} item?`;
                modal.show();
            });

            confirmBtn.addEventListener('click', async function () {
                const checkedBoxes = Array.from(document.querySelectorAll('.rowCheckbox:checked'));
                const ids = checkedBoxes.map(cb => cb.value);

                if (ids.length === 0) {
                    modal.hide();
                    return;
                }

                const action = bulkForm.getAttribute('action');
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                confirmBtn.disabled = true;

                try {
                    const formData = new FormData();
                    ids.forEach(i => formData.append('ids[]', i));

                    const res = await fetch(action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    let data;
                    try {
                        data = await res.json();
                    } catch {
                        alert('Respon server tidak valid.');
                        console.error('Respon bukan JSON valid');
                        confirmBtn.disabled = false;
                        return;
                    }

                    // ⚠️ Handle jika gagal / ada blocked
                    if (!res.ok || data.status === 'error' || data.status === 'warning' || (data.blocked && data.blocked.length)) {
                        modal.hide();
                        confirmBtn.disabled = false;

                        if (data.blocked && data.blocked.length) {
                            // tampilkan modal error
                            const modalEl = document.getElementById('cannotDeleteModal');
                            const msgEl = document.getElementById('cannotDeleteMessage');
                            const listEl = document.getElementById('cannotDeleteList');

                            msgEl.textContent = data.message || 'Beberapa eksemplar tidak bisa dihapus karena sedang dipinjam / diperpanjang.';
                            listEl.innerHTML = '';

                            data.blocked.forEach(id => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item small';
                                li.textContent = 'ID Item: ' + id;
                                listEl.appendChild(li);
                            });

                            listEl.style.display = 'block';
                            new bootstrap.Modal(modalEl).show();
                        } else {
                            alert(data.message || 'Beberapa item tidak dapat dihapus.');
                        }
                        return;
                    }


                    // ✅ Hapus baris item yang berhasil dihapus
                    if (Array.isArray(data.deleted)) {
                        data.deleted.forEach(idItem => {
                            const row = document.querySelector(`tr[data-item-id="${idItem}"]`);
                            if (row) row.remove();
                        });
                    }

                    // ✅ Update jumlah_tata tiap buku langsung dari JSON
                    if (Array.isArray(data.updated_books)) {
                        data.updated_books.forEach(book => {
                            const jumlahEl = document.querySelector(`[data-buku-id="${book.id}"] .jumlah_tata`);
                            if (jumlahEl) jumlahEl.textContent = book.jumlah_tata;
                        });
                    }

                    // ✅ Notifikasi sukses Bootstrap
                    const alertBox = document.createElement('div');
                    alertBox.className = 'alert alert-success alert-dismissible fade show mt-3';
                    alertBox.role = 'alert';
                    alertBox.innerHTML = `
                ${data.message || 'Berhasil menghapus item.'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
                    bulkForm.parentNode.prepend(alertBox);

                    modal.hide();
                    confirmBtn.disabled = false;
                    selectAll.checked = false;
                    updateButton();

                    // hilang otomatis setelah 3 detik
                    setTimeout(() => alertBox.remove(), 3000);

                    // 🔥🔁 [TAMBAHAN PENTING]
                    // biar jumlah_tata di index buku ikut update setelah hapus item
                    if (data.success && data.deleted && data.deleted.length > 0) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }

                    // 🔥 selesai tambahan

                } catch (err) {
                    console.error(err);
                    alert('Terjadi kesalahan saat menghubungi server.');
                } finally {
                    confirmBtn.disabled = false;
                }
            });

            updateButton();
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // single delete
            document.querySelectorAll('.deleteItemForm').forEach(form => {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    if (!confirm('Yakin hapus eksemplar ini?')) return;
                    const idBuku = form.dataset.idBuku;
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            body: new FormData(form)
                        });
                        const json = await res.json().catch(()=>null);
                        if (res.ok && (!json || json.success)) {
                            form.closest('tr').remove();
                            const cell = document.getElementById('jumlahText-' + idBuku);
                            if (cell) {
                                const parts = cell.textContent.trim().split('/');
                                const max = parseInt(parts[0]);
                                const tata = Math.max(0, parseInt(parts[1]) - 1);
                                cell.textContent = `${max} / ${tata}`;
                            }
                            alert('Eksemplar dihapus');
                        }
// 🆕 TAMBAHAN: jika tidak bisa dihapus (dipinjam/diperpanjang)
                        else if (json && json.blocked && json.blocked.length) {
                            const modalEl = document.getElementById('cannotDeleteModal');
                            const msgEl = document.getElementById('cannotDeleteMessage');
                            const listEl = document.getElementById('cannotDeleteList');

                            msgEl.textContent = json.message || 'Eksemplar tidak bisa dihapus karena sedang dipinjam / diperpanjang.';
                            listEl.innerHTML = '';

                            json.blocked.forEach(id => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item small';
                                li.textContent = 'ID Item: ' + id;
                                listEl.appendChild(li);
                            });

                            listEl.style.display = 'block';
                            new bootstrap.Modal(modalEl).show();
                        }
// 🧱 akhir tambahan
                        else {
                            alert((json && json.message) ? json.message : 'Gagal hapus');
                        }

                    } catch (err) {
                        console.error(err); alert('Error hapus');
                    }
                });
            });
        });
    </script>
@endsection
