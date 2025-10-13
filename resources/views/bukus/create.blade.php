@extends('layouts.app')

@section('content')
    <h2 class="mb-3">Tambah Buku</h2>

    <form action="{{ route('bukus.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="judul" class="form-label">Judul</label>
            <input type="text" name="judul" id="judul" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="id_penerbit" class="form-label">Penerbit</label>
            <select name="id_penerbit" id="id_penerbit" class="form-control">
                <option value="">-- Pilih Penerbit --</option>
                @foreach($penerbits as $p)
                    <option value="{{ $p->id_penerbit }}">{{ $p->nama_penerbit }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="pengarang" class="form-label">Pengarang</label>
            <input type="text" name="pengarang" id="pengarang" class="form-control">
        </div>

        <div class="mb-3">
            <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
            <input type="number" name="tahun_terbit" id="tahun_terbit" class="form-control">
        </div>

        <div class="mb-3">
            <label for="id_kategori" class="form-label">Kategori</label>
            <select name="id_kategori" id="id_kategori" class="form-control">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategoris as $k)
                    <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="id_sub" class="form-label">Sub Kategori</label>
            <select name="id_sub" id="id_sub" class="form-control">
                <option value="">-- Pilih Sub Kategori --</option>
                @foreach($sub_kategoris as $sk)
                    <option value="{{ $sk->id_sub }}">{{ $sk->sub_kategori }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="isbn" class="form-label">ISBN</label>
            <input type="text" name="isbn" id="isbn" class="form-control">
        </div>

        <div class="mb-3">
            <label for="jumlah" class="form-label">Jumlah</label>
            <input type="number" name="jumlah" id="jumlah" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
@endsection
