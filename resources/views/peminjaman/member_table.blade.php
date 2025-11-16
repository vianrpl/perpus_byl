<table class="table table-sm table-hover">
    <thead>
    <tr>
        <th>Nama</th>
        <th>Email</th>
        <th>Status</th>
        <th>kuota</th>
        <th>Aksi</th>
    </tr>
    </thead>
    <tbody>
    @forelse($members as $m)
        <tr>
            <td>{{ $m->name }}</td>
            <td>{{ $m->email }}</td>
            <td><span class="badge bg-success">{{ $m->status }}</span></td>
            <td><span class="badge bg-info">{{ $m->kuota }}/2</span></td>
            <td>
                <button class="btn btn-sm btn-success pilih-member"
                        data-id="{{ $m->id_user }}"
                        data-nama="{{ $m->name }}">
                        @if($m->kuota <= 0) disabled @endif>
                    Pilih
                </button>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted">Tidak ada member</td></tr>
    @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-center mt-3">
    {{ $members->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
