@extends('layouts.app')
@section('content')
    <div class="container">
        <h2 class="mb-3">Tambah Kategori</h2>
        <form action="{{route('kategoris.store')}}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{route('kategoris.index')}}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
