@extends('layouts.app')

@section('content')
    <h2 class="mb-3">Detail Rak</h2>

    <div class="mb-3">
        <a href="{{ route('raks.index') }}" class="btn btn-secondary">← Kembali ke Daftar Rak</a>
    </div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="card shadow-sm p-3">
                <h5 class="card-title text-center mb-3">Informasi Rak</h5>
                <div class="row text-center">
                    <div class="col-md-3">
                        <strong>ID Rak:</strong> {{ $rak->id_rak }}
                    </div>
                    <div class="col-md-3">
                        <strong>Lantai:</strong> {{ $rak->lokasi_raks->lantai ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Ruang:</strong> {{ $rak->lokasi_raks->ruang ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Sisi:</strong> {{ $rak->lokasi_raks->sisi ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- Form pencarian -->
    <form method="GET" action="{{ route('raks.show', $rak->id_rak) }}" class="mb-3 d-flex">
        <div class="input-group" style="max-width:400px">
            <!-- Input untuk mencari berdasarkan nama buku atau rak -->
            <input type="text" name="search" class="form-control"
                   placeholder="Cari Judul"
                   value="{{ request('search') }}">
            <button class="btn btn-dark" type="submit">Cari</button>
        @if(request('search'))
            <a href="{{ route('raks.show', $rak->id_rak) }}" class="btn btn-dark">Reset</a>
        @endif
        </div>
    </form>



    <!-- Section Lihat Buku di Rak Ini -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm p-3">
                <h5 class="card-title">Daftar Buku di Rak Ini</h5>
                <hr>
                {{-- gunakan nama variabel bukusInRak (kecil) sesuai controller --}}
                @if ($bukusInRak->isEmpty())
                    <p>Tidak ada buku di rak ini.</p>
                @else
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Judul Buku</th>
                            <th>Total Eksemplar</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- setiap item adalah object: item->buku (model) dan item->total_jumlah --}}
                        @foreach ($bukusInRak as $item)
                            <tr>
                                <td>{{ $item->buku->judul }}</td>
                                <td>{{ $item->total_jumlah }}</td>
                                <td>
                                    <!-- ubah jadi: kirimkan id rak sekarang lewat query string "from_rak" -->
                                    <a href="{{ route('bukus.items.index', $item->buku->id_buku) }}?from_rak={{ $rak->id_rak }}" class="btn btn-info btn-sm">
                                        Lihat Eksemplar
                                    </a>

                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center">
        {{ $bukusInRak->links('pagination::bootstrap-5') }}
    </div>
@endsection
