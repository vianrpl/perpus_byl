@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manajemen User</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Form Search --}}
    <form action="{{ route('users.index') }}" method="GET" class="mb-3">
        <div class="input-group" style="max-width:400px">
            <input type="text" name="search" class="form-control" placeholder="Cari user (nama, email, role)"
                   value="{{ request('search') }}">
            <button class="btn btn-dark" type="submit">Cari</button>
            @if(request('search'))
                <a href="{{ route('users.index') }}" class="btn btn-dark">Reset</a>
            @endif
        </div>
    </form>

    <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr class="text-center">
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr class="text-center">
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ucfirst($user->role) }}</td>
                <td>
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
        {{ $users->links('pagination::bootstrap-5') }}
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
@endsection
