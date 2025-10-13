@extends('layouts.app')

@section('content')
    <h2>Tambah Rak</h2>

    <form action="{{ route('raks.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Barcode</label>
            <input type="text" name="barcode" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control">
        </div>
        <div class="mb-3">
            <label>Kolom</label>
            <input type="text" name="kolom" class="form-control">
        </div>
        <div class="mb-3">
            <label>Baris</label>
            <input type="text" name="baris" class="form-control">
        </div>
        <div class="mb-3">
            <label>Kapasitas</label>
            <input type="text" name="kapasitas" class="form-control">
        </div>
        <div class="mb-3">
            <label>Lokasi</label>
            <input type="text" name="id_lokasi" class="form-control">
        </div>
        <div class="mb-3">
            <label>Kategori</label>
            <input type="text" name="id_kategori" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('raks.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection
