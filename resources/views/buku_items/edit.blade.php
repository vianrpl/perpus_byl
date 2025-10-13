@extends('layouts.app')

@section('content')
    <h2 class="mb-3">Edit Item Buku: {{ $buku->judul }}</h2>

    <form action="{{ route('bukus.items.update', [$buku->id_buku, $item->id_item]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="barcode" class="form-label">Barcode</label>
            <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $item->barcode) }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="kondisi" class="form-label">Kondisi</label>
            <select name="kondisi" id="kondisi" class="form-control">
                <option value="baik" {{ $item->kondisi == 'baik' ? 'selected' : '' }}>Baik</option>
                <option value="rusak" {{ $item->kondisi == 'rusak' ? 'selected' : '' }}>Rusak</option>
                <option value="hilang" {{ $item->kondisi == 'hilang' ? 'selected' : '' }}>Hilang</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="tersedia" {{ $item->status == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="dipinjam" {{ $item->status == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="hilang" {{ $item->status == 'hilang' ? 'selected' : '' }}>Hilang</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="sumber" class="form-label">Sumber</label>
            <input type="text" name="sumber" id="sumber" value="{{ old('sumber', $item->sumber) }}" class="form-control">
        </div>

        <div class="mb-3">
            <label for="rak" class="form-label">Rak</label>
            <input type="text" name="rak" id="rak" value="{{ old('rak', $item->id_rak) }}" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('bukus.items.index', $buku->id_buku,$item->id_item) }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection
