@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-3">Edit Lokasi Rak</h2>

        <form action="{{ route('lokasi_raks.update', $lokasi_rak) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Lantai</label>
                <input type="text" name="lantai" value="{{ old('lantai', $lokasi_rak->lantai) }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Ruang</label>
                <input type="text" name="ruang" value="{{ old('ruang', $lokasi_rak->ruang) }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Sisi</label>
                <input type="text" name="sisi" value="{{ old('sisi', $lokasi_rak->sisi) }}" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('lokasi_raks.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection
