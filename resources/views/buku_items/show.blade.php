@extends('layouts.app')

@section('content')
    <h2 class="mb-3">Detail Item Buku: {{ $buku->judul }}</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Barcode: {{ $item->barcode }}</h5>
            <p class="card-text"><strong>Kondisi:</strong> {{ $item->kondisi }}</p>
            <p class="card-text"><strong>Status:</strong> {{ $item->status }}</p>
            <p class="card-text"><strong>Sumber:</strong> {{ $item->sumber }}</p>
            <p class="card-text"><strong>Rak:</strong> {{ $item->id_rak }}</p>
            <p class="card-text"><strong>Insert:</strong>
                {{ $item->insert_date ? \Carbon\Carbon::parse($item->insert_date)->format('d M Y H:i') : '-' }}
            </p>
            <p class="card-text"><strong>Update:</strong>
                {{ $item->modified_date ? \Carbon\Carbon::parse($item->modified_date)->format('d M Y H:i') : '-' }}
            </p>

        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('bukus.items.index', $buku->id_buku) }}" class="btn btn-secondary">← Kembali</a>
        <a href="{{ route('bukus.items.edit', [$buku->id_buku, $item->id_item]) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('bukus.items.destroy', [$buku->id_buku, $item->id_item]) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Yakin hapus item ini?')" class="btn btn-danger">Hapus</button>
        </form>
    </div>
@endsection
