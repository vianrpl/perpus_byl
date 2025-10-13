@extends('layouts.app')

@section('content')
<h1>✏️ Edit Buku</h1>

<form action="{{ route('bukus.update', $buku->id_buku) }}" method="POST" class="mt-3">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Judul</label>
        <input type="text" name="judul" class="form-control" value="{{ $buku->judul }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Penerbit</label>
        <select name="id_penerbit" class="form-select" required>
            @foreach($penerbits as $p)
                <option value="{{ $p->id_penerbit }}" {{ $buku->id_penerbit== $p->id_penerbit ? 'selected' : '' }}>
                    {{ $p->nama_penerbit }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Kategori</label>
        <select name="id_kategori" class="form-select" required>
            @foreach($kategoris as $k)
                <option value="{{ $k->id_kategori }}" {{ $buku->id_kategori == $k->id_kategori ? 'selected' : '' }}>
                    {{ $k->nama_kategori }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Sub Kategori</label>
        <select name="id_sub" class="form-select" required>
            @foreach($sub_kategoris as $s)
                <option value="{{ $s->id_sub }}" {{ $buku->id_sub == $s->id_sub ? 'selected' : '' }}>
                    {{ $s->nama_sub_kategori }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Pengarang</label>
        <input type="text" name="pengarang" class="form-control" value="{{ $buku->pengarang }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Tahun Terbit</label>
        <input type="text" name="tahun_terbit" class="form-control" placeholder="YYYY-MM-DD" value="{{ $buku->tahun_terbit }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">ISBN</label>
        <input type="text" name="isbn" class="form-control" value="{{ $buku->isbn }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Barcode</label>
        <input type="text" name="barcode" class="form-control" value="{{ $buku->barcode }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Jumlah</label>
        <input type="jumlah" name="jumlah" class="form-control" value="{{ $buku->jumlah }}" required>
    </div>

    <button type="submit" class="btn btn-warning">💾 Update</button>
    <a href="{{ route('bukus.index') }}" class="btn btn-secondary">↩️ Kembali</a>
</form>
@endsection
