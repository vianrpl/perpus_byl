@extends('layouts.app')
@section('content')
    <div class="container">
        <h2 class="mb-3">Edit Sub Kategori</h2>

        <form action="{{route('sub_kategoris.update', $sub_kategori)}}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Sub Kategori</label>
                <input type="text" name="nama_sub_kategori" value="{{old('nama_sub_kategori',$sub_kategori->nama_sub_kategori)}}" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{route('sub_kategoris.index')}}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
@endsection
