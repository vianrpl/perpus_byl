@extends('layouts.app')
@section('content')
    <div class="container">
        <h3 class="mb-3">Pendaftaran Member</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Status Verifikasi:</strong> {{ $user->is_verified_member ? 'Sudah Diverifikasi' : 'Belum Diverifikasi' }}</p>

                @if(!$user->is_verified_member)
                    <form action="{{ route('member.send_code') }}" method="POST">@csrf
                        <button class="btn btn-outline-primary mb-3">Kirim Kode ke Email</button>
                    </form>

                    <form action="{{ route('member.verify_code') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label>Kode Verifikasi</label>
                            <input type="text" name="code" class="form-control" placeholder="Masukkan kode dari email" required>
                        </div>
                        <button class="btn btn-primary">Verifikasi Kode</button>
                    </form>
                @elseif(!$user->is_member)
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#daftarMemberModal">Daftar Member</button>
                @else
                    <div class="alert alert-success mt-3">Anda sudah menjadi member aktif.</div>
                @endif
            </div>
        </div>
    </div>

    @include('member.partials.daftar_modal')
@endsection
