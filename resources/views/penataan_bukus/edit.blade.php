@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Edit Penataan Buku</h2>
        <a href="{{ route('penataan_bukus.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <!-- Pesan error validasi -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
    @endif

    <!-- Form edit penataan -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('penataan_bukus.update', $penataan->id_penataan) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="id_buku">Buku</label>
                    <select name="id_buku" id="id_buku" class="form-control" required>
                        <option value="">-- Pilih Buku --</option>
                        @foreach($bukus as $buku)
                            <option value="{{ $buku->id_buku }}"
                                {{ old('id_buku', $penataan->id_buku) == $buku->id_buku ? 'selected' : '' }}>
                                {{ $buku->nama_buku }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_buku')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="id_rak">Rak</label>
                    <select name="id_rak" id="id_rak" class="form-control" required>
                        <option value="">-- Pilih Rak --</option>
                        @foreach($raks as $rak)
                            <option value="{{ $rak->id_rak }}"
                                {{ old('id_rak', $penataan->id_rak) == $rak->id_rak ? 'selected' : '' }}>
                                {{ $rak->nama_rak }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_rak')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <!-- Petugas: Auto tampil nama login (readonly), nggak ada input -->
                <div class="mb-3">
                    <label>Petugas</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                    <input type="hidden" name="id_user" value="{{ Auth::id() }}">  <!-- Kirim ID auto -->
                </div>
                <div class="mb-3">
                    <label for="kolom">Kolom</label>
                    <input type="number" name="kolom" id="kolom" class="form-control"
                           value="{{ old('kolom', $penataan->kolom) }}" min="1" required>
                    @error('kolom')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="baris">Baris</label>
                    <input type="number" name="baris" id="baris" class="form-control"
                           value="{{ old('baris', $penataan->baris) }}" min="1" required>
                    @error('baris')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="jumlah">Jumlah</label>
                    <input type="number" name="jumlah" id="jumlah" class="form-control @error('jumlah') is-invalid @enderror"
                           value="{{ old('jumlah', $penataan->jumlah ?? '') }}" min="1" required>
                    @error('jumlah')
                    <div class="invalid-feedback">{{ $message }}</div>  <!-- Tampil pesan over-eksemplar -->
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="sumber">Sumber</label>
                    <input type="text" name="sumber" id="sumber" class="form-control @error('sumber') is-invalid @enderror"
                           value="{{ old('sumber', $penataan->sumber ?? '') }}" min="1" required>
                    @error('sumber')
                    <div class="invalid-feedback">{{ $message }}</div>  <!-- Tampil pesan over-eksemplar -->
                    @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection
