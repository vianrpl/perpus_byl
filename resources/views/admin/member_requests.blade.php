@extends('layouts.app')
@section('content')
    <div class="container">
        <h3>Permintaan Pendaftaran Member</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>KTP</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($profiles as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->user->name }}</td>
                    <td>{{ $p->user->email }}</td>
                    <td>{{ $p->nama_lengkap }}</td>
                    <td>{{ Str::limit($p->alamat, 50) }}</td>
                    <td>
                        @if($p->ktp_path)
                            <a href="{{ asset('storage/'.$p->ktp_path) }}" target="_blank">Lihat</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ ucfirst($p->request_status) }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.member.approve', $p->id) }}" class="d-inline">@csrf
                            <button class="btn btn-success btn-sm">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.member.reject', $p->id) }}" class="d-inline">@csrf
                            <button class="btn btn-danger btn-sm">Tolak</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">Belum ada permintaan</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
