@extends('partials.all')

@section('title', 'Edit User')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Edit User</h4>

            <div class="table-divider mb-4"></div>

            @if ($errors->any())
                <div class="alert alert-danger mx-3 mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('user.update', $user->id) }}" autocomplete="off" id="formSaveUser">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="name">
                            Nama User
                        </label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            style="background-color: #e9ecef;" value="{{ old('name', $user->name) }}" readonly>

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Username Login -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="username">
                            Username Login
                        </label>
                        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
                            style="background-color: #e9ecef;" value="{{ old('username', $user->username) }}" readonly>

                        @error('username')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <!-- Password Baru -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password">
                            Password Baru <small class="text-muted">(Kosongkan jika tidak diubah)</small>
                        </label>

                        <div class="input-group">
                            <input type="password" 
                                name="password" 
                                id="password" 
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Kosongkan jika tidak diubah" 
                                autocomplete="new-password">

                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', 'iconPassword')"
                                aria-label="Tampilkan atau sembunyikan password" tabindex="-1">
                                <i class="fas fa-eye" id="iconPassword"></i>
                            </button>
                        </div>
                            
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="password_confirmation">
                            Konfirmasi Password Baru
                        </label>

                        <div class="input-group">
                            <input type="password" 
                                name="password_confirmation" 
                                id="password_confirmation" 
                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                placeholder="Ulangi password baru" 
                                autocomplete="new-password">

                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', 'iconPasswordConfirmation')"
                                aria-label="Tampilkan atau sembunyikan konfirmasi password" tabindex="-1">
                                <i class="fas fa-eye" id="iconPasswordConfirmation"></i>
                            </button>
                        </div>

                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="row">    
                    <!-- Role -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="role">
                            Role
                        </label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('role', $user->id_role ?? $user->role_id ?? '') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('role')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="table-divider mt-3 mb-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
                    <a href="{{ route('user.index') }}" class="btn btn-back">
                        <i class="fas fa-window-close"></i> Cancel
                    </a>
                    <button type="button" class="btn btn-save" onclick="confirmSaveUser(event)">
                        <i class="fa fa-save"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

@endsection