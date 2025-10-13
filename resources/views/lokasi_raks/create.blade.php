@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-3">Tambah Lokasi Rak</h2>

        <form action="{{ route('lokasi_raks.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Lantai</label>
                <input type="text" name="lantai" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ruang</label>
                <input type="text" name="ruang" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Sisi</label>
                <input type="text" name="sisi" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('lokasi_raks.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection
