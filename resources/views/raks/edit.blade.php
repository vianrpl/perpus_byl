@extends('layouts.app')

@section('content')
    <h2>Edit Rak</h2>

    <form action="{{ route('raks.update', $rak->id_rak) }}" method="POST">
        @csrf
        @method('PATCH') {{-- metode untuk update --}}

        <div class="mb-3">
            <label>Barcode</label>
            <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $rak->barcode) }}" required>
        </div>
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ old('nama', $rak->nama) }}">
        </div>
        <div class="mb-3">
            <label>Kolom</label>
            <input type="text" name="kolom" class="form-control" value="{{ old('kolom', $rak->kolom) }}">
        </div>
        <div class="mb-3">
            <label>Baris</label>
            <input type="text" name="baris" class="form-control" value="{{ old('baris', $rak->baris) }}">
        </div>
        <div class="mb-3">
            <label>Kapasitas</label>
            <input type="text" name="kapasitas" class="form-control" value="{{ old('kapasitas', $rak->kapasitas) }}">
        </div>
        <div class="mb-3">
            <label>Lokasi</label>
            <input type="text" name="id_lokasi" class="form-control" value="{{ old('id_lokasi', $rak->id_lokasi) }}">
        </div>
        <div class="mb-3">
            <label>Kategori</label>
            <input type="text" name="id_kategori" class="form-control" value="{{ old('id_kategori', $rak->id_kategori) }}">
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('raks.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection
