@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-users"></i> Kelola Member Perpustakaan</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahMemberModal">
                <i class="fas fa-user-plus"></i> Daftar Member Baru
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Form Search --}}
        <form action="{{ route('admin.member.kelola') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width:500px">
                <input type="text" name="search" class="form-control"
                       placeholder="Cari member (nama, email, no member, no HP)"
                       value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.member.kelola') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </div>
        </form>


                <div class="table-responsive">
                    <table class="table table-modern table-bordered table-striped">
                        <thead class="table-dark">
                        <tr class="text-center">
                            <th class="text-center">No</th>
                            <th class="text-center">No. Member</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Profesi</th>
                            <th class="text-center">Tanggal Daftar</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td class="text-center">
                                    {{ ($members->currentPage() - 1) * $members->perPage() + $loop->iteration }}
                                </td>
                                <td class="text-center">
                                    <strong class="text-primary">{{ $member->no_member }}</strong>
                                </td>
                                <td>{{ $member->nama_lengkap }}</td>
                                <td>{{ $member->user->email }}</td>
                                <td>{{ $member->no_hp }}</td>
                                <td>{{ $member->profesi ?? '-' }}</td>
                                <td class="text-center">{{ $member->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.member.profile', $member->user_id) }}"
                                       class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    @if(request('search'))
                                        Tidak ada member yang sesuai dengan pencarian "{{ request('search') }}"
                                    @else
                                        Belum ada member terdaftar
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $members->links('pagination::bootstrap-5') }}
                </div>
    </div>

    {{-- Modal Tambah Member --}}
    @include('admin.member.partials.tambah_modal')

@endsection
