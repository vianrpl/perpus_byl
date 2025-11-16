@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manajemen User</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Form Search --}}
    <form action="{{ route('users.index') }}" method="GET" class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
        <div class="input-group" style="max-width:400px">
            <input type="text" name="search" class="form-control" placeholder="Cari user (nama, email, role)"
                   value="{{ request('search') }}">
            <button class="btn btn-dark" type="submit">Cari</button>
            @if(request('search'))
                <a href="{{ route('users.index') }}" class="btn btn-dark">Reset</a>
            @endif
        </div>


        {{-- 🧮 Tombol Filter --}}
        <button type="button" class="btn btn-outline-secondary d-flex align-items-center"
                data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="bi bi-funnel me-2"></i> Filter
        </button>
    </form>




    {{-- Tombol Hapus Terpilih --}}
    <form id="bulkDeleteForm" action="{{ route('users.bulkDestroy') }}" method="POST" class="mb-3">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
            Hapus Terpilih
        </button>
    </form>


    <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr class="text-center">
                <th><input type="checkbox" id="selectAll"></th>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr class="text-center">
                <td><input type="checkbox" name="ids[]" class="rowCheckbox" value="{{ $user->id_user }}"></td>
                <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>
                    @if($user->status == 'belom verifikasi')
                        <span class="badge badge-secondary">Belom Verifikasi</span>
                    @elseif($user->status == 'terverifikasi')
                        <span class="badge badge-success">Terverifikasi</span>
                    @else
                        <span class="badge badge-primary">Member</span>
                    @endif
                </td>
                <td>
                    @if(in_array(Auth::user()->role, ['admin', 'petugas']))
                        <a href="{{ route('admin.member.profile', $user->id_user) }}"
                           class="btn btn-info text-white btn-sm">
                            <i class="fas fa-id-card"></i> Data Diri
                        </a>
                    @endif

                    <button class="btn btn-sm btn-warning"
                            data-bs-toggle="modal" data-bs-target="#modalEditUser{{ $user->id_user}}">Edit</button>
                    <!-- Tombol hapus buka modal -->
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus{{ $user->id_user }}">
                        Hapus
                    </button>

                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>

    <!-- hapus-->
    @foreach($users as $user)
        <div class="modal fade" id="modalHapus{{ $user->id_user}}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus akun <b>{{ $user->name }}</b>?
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('users.destroy', $user->id_user) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <!-- end hapus -->

    <!-- modal edit -->
    @foreach($users as $user)
        <!-- Modal Edit -->
        <div class="modal fade" id="modalEditUser{{ $user->id_user }}" tabindex="-1" aria-labelledby="modalEditUserLabel{{ $user->id_user}}" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content shadow-sm">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalEditUserLabel{{ $user->id_user }}">Edit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>

                    <form action="{{ route('users.update', $user->id_user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="role" class="form-label">Pilih Role</label>
                            <select name="role" id="role" class="form-select">
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="petugas" {{ $user->role == 'petugas' ? 'selected' : '' }}>Petugas</option>
                                <option value="konsumen" {{ $user->role == 'konsumen' ? 'selected' : '' }}>Konsumen</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Simpan</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <!-- end edit-->
</div>

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
                <h5 class="modal-title" id="filterModalLabel">Filter Data User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('users.index') }}" method="GET">
                <div class="modal-body">
                    {{-- Role --}}
                    <div class="mb-3">
                        <label for="filter_role" class="form-label">Role</label>
                        <select name="filter_role" id="filter_role" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="admin" {{ request('filter_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="petugas" {{ request('filter_role') == 'petugas' ? 'selected' : '' }}>Petugas</option>
                            <option value="konsumen" {{ request('filter_role') == 'konsumen' ? 'selected' : '' }}>Konsumen</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label for="filter_status" class="form-label">Status</label>
                        <select name="filter_status" id="filter_status" class="form-select">
                            <option value="">-- Semua --</option>
                            <option value="member" {{ request('filter_status') == 'member' ? 'selected' : '' }}>Member</option>
                            <option value="terverifikasi" {{ request('filter_status') == 'terverifikasi' ? 'selected' : '' }}>Ter verifikasi</option>
                            <option value="belom verifikasi" {{ request('filter_status') == 'belom verifikasi' ? 'selected' : '' }}>Belom verifikasi</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Reset</a>
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.rowCheckbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkForm = document.getElementById('bulkDeleteForm');
        const modal = new bootstrap.Modal(document.getElementById('confirmBulkDeleteModal'));
        const confirmText = document.getElementById('confirmText');
        const confirmBtn = document.getElementById('confirmBulkDeleteBtn');

        function updateButton() {
            const checked = document.querySelectorAll('.rowCheckbox:checked');
            bulkDeleteBtn.disabled = checked.length === 0;
        }

        // ✅ checkbox logic
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateButton();
        });

        checkboxes.forEach(cb => cb.addEventListener('change', updateButton));

        // ✅ buka modal pas klik hapus terpilih
        bulkDeleteBtn.addEventListener('click', function () {
            const selected = document.querySelectorAll('.rowCheckbox:checked');
            if (selected.length === 0) return;
            confirmText.textContent = `Yakin ingin menghapus ${selected.length} data terpilih?`;
            modal.show();
        });

        // ✅ submit form pas klik “Hapus” di modal
        confirmBtn.addEventListener('click', function () {
            const selectedIds = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb.value);

            // hapus input hidden lama (biar gak dobel)
            document.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

            // tambahkan input hidden baru ke form
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                bulkForm.appendChild(input);
            });

            modal.hide();
            bulkForm.submit();
        });
    });
</script>


@endsection


