<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-3">
        @csrf
        @method('put')

        <div class="mb-1">
            <label for="current_password" class="form-label">Password Lama</label>
            <input id="current_password" name="current_password" type="password" class="form-control" required autocomplete="current-password" placeholder="-min 8 karakter-">
        </div>

        <div class="mb-1">
            <label for="password" class="form-label">Password Baru</label>
            <input id="password" name="password" type="password" class="form-control" required autocomplete="new-password" placeholder="-min 8 karakter-">
        </div>

        <div class="mb-1">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password" placeholder="-min 8 karakter-">
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-warning">
                Update Password
            </button>
        </div>
    </form>

</section>
