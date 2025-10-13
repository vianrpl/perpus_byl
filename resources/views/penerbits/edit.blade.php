@extends('layouts.app')
@section('content')
    <div class="container">
        <h2 class="mb-3">Edit Penerbit</h2>

        <form action="{{route('penerbits.update', $penerbit)}}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama_penerbit" value="{{old('nama_penerbit',$penerbit->nama_penerbit)}}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <input type="text" name="alamat" value="{{old('alamat',$penerbit->alamat)}}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="no_telepon" value="{{old('no_telepon', $penerbit->no_telepon) }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="text" name="email" value="{{old('email',$penerbit->email)}}" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{route('penerbits.index')}}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection
