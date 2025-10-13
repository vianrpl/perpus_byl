@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-3">Detail Lokasi Rak</h2>

        <div class="mb-3">
            <a href="{{ route('lokasi_raks.index') }}" class="btn btn-secondary">← Kembali ke Daftar</a>
        </div>

        <div class="card p-3">
            <p><strong>ID Lokasi:</strong> {{ $lokasi_rak->id_lokasi }}</p>
            <p><strong>Lantai:</strong> {{ $lokasi_rak->lantai }}</p>
            <p><strong>Ruang:</strong> {{ $lokasi_rak->ruang }}</p>
            <p><strong>Sisi:</strong> {{ $lokasi_rak->sisi ?? '-' }}</p>
        </div>
    </div>
@endsection
