@extends('layouts.app')
@section('content')
    <div class="container">
        <h2 class="mb-3">Detail Penerbit</h2>
        <div class="mb-3">
            <a href="{{route('penerbits.index')}}" class="btn btn-secondary">← Kembali ke Daftar</a>
        </div>
        <div class="card p-3">
            <p><strong>ID:</strong>{{$penerbit->id_penerbit}}</p>
            <p><strong>Alamat:</strong>{{$penerbit->alamat}}</p>
            <p><strong>Telepon:</strong>{{$penerbit->no_telepon}}</p>
            <p><strong>Email:</strong>{{$penerbit->email}}</p>
        </div>
    </div>
@endsection
