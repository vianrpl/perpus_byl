@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Detail Penataan Buku</h2>
        <a href="{{ route('penataan_bukus.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <!-- Detail penataan -->
    <div class="card shadow-sm">
        <div class="card-body">
            <p><strong>ID Penataan:</strong> {{ $penataan->id_penataan }}</p>
            <p><strong>Nama Buku:</strong> {{ $penataan->bukus->nama_buku ?? 'Buku tidak ditemukan' }}</p>
            <p><strong>Nama Rak:</strong> {{ $penataan->raks->nama_rak ?? 'Rak tidak ditemukan' }}</p>
            <p><strong>Kolom:</strong> {{ $penataan->kolom }}</p>
            <p><strong>Baris:</strong> {{ $penataan->baris }}</p>
            <p><strong>Jumlah:</strong> {{ $penataan->jumlah }}</p>
            <p><strong>Petugas:</strong> {{ $penataan->user->name ?? '-' }}</p>
            <p><strong>Tanggal Dibuat:</strong> {{ $penataan->insert_date->format('d-m-Y H:i') }}</p>
            <p><strong>Tanggal Diperbarui:</strong> {{ $penataan->modified_date->format('d-m-Y H:i') }}</p>
        </div>
    </div>
@endsection
