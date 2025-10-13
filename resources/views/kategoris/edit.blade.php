@extends('layouts.app')
@section('content')
    <div class="container">
        <h2 class="mb-3">Edit Kategori</h2>

        <form action="{{route('kategoris.update', $kategori)}}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <input type="text" name="nama_kategori" value="{{old('nama_kategori',$kategori->nama_kategori)}}" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{route('kategoris.index')}}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection
