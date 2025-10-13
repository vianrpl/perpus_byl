@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Role User</h1>

    <form action="{{ route('users.update', $user->id_user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="role" class="form-label">Pilih Role</label>
            <select name="role" id="role" class="form-select">
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="petugas" {{ $user->role == 'petugas' ? 'selected' : '' }}>Petugas</option>
                <option value="konsumen" {{ $user->role == 'konsumen' ? 'selected' : '' }}>Konsumen</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
