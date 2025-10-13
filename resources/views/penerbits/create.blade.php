@extends('layouts.app')
@section('content')
    <div class="container">
        <h2 class="mb-3">Tambah Penerbit</h2>
        <form action="{{route('penerbits.store')}}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama_penerbit" class="form-control" required>
            </div>
           <div class="mb-3">
               <label class="form-label">Alamat</label>
               <input type="text" name="alamat" class="form-control" required>
           </div>
            <div class="mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="no_telepon" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" name="email" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{route('penerbits.index')}}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
