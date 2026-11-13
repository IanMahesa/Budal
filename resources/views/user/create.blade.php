@extends('partials.all')

@section('title','Tambah User')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Tambah User</h4>

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

            <form method="POST" action="{{ isset($user) ? route('user.update', $user->id) : route('user.store') }}" autocomplete="{{ isset($user) ? 'off' : 'new-password' }}" id="formSaveUser">
                @csrf
                @isset($user)
                    @method('PUT')
                @endisset
                @unless(isset($user))
                    <input type="hidden" name="id_peg" id="id_peg" value="{{ old('id_peg') }}">
                @endunless
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Nama Pegawai
                        </label>

                        <input type="{{ isset($user) ? 'text' : 'search' }}"
                            name="name" id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name ?? '') }}"
                            placeholder="{{ isset($user) ? 'Masukkan nama user' : 'Cari nama atau NIK pegawai' }}"
                            autocomplete="off" required
                            @unless(isset($user))
                                list="pegawai-options"
                                aria-describedby="pegawai-search-error"
                            @endunless>

                        @unless(isset($user))
                            <datalist id="pegawai-options">
                                @foreach($pegawai as $item)
                                    <option value="{{ $item->nama }}"
                                        data-id="{{ $item->id_peg }}"
                                        data-name="{{ $item->nama }}"
                                        data-username="{{ $item->nik }}"></option>
                                @endforeach
                            </datalist>
                            <div id="pegawai-search-error" class="text-danger small mt-1" role="alert"></div>
                        @endunless

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        @unless(isset($user))
                            @error('id_peg')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endunless
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Username Login
                        </label>

                        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
                            value="{{ old('username', $user->username ?? '') }}" placeholder="NIK pegawai" autocomplete="new-password" required {{ isset($user) ? '' : 'readonly' }}>

                        @error('username')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Password
                        </label>

                        <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                            placeholder="{{ isset($user) ? 'Kosongkan jika tidak diubah' : 'Masukkan password' }}" autocomplete="new-password" {{ isset($user) ? '' : 'required' }}>

                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', 'iconPassword')"
                                aria-label="Tampilkan atau sembunyikan password" tabindex="-1">
                                <i class="fas fa-eye" id="iconPassword"></i>
                            </button>
                        </div>
                            
                        @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Konfirmasi Password
                        </label>

                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror"
                                placeholder="Ulangi password" autocomplete="new-password" {{ isset($user) ? '' : 'required' }}>

                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', 'iconPasswordConfirmation')"
                                aria-label="Tampilkan atau sembunyikan konfirmasi password" tabindex="-1">
                                <i class="fas fa-eye" id="iconPasswordConfirmation"></i>
                            </button>
                        </div>

                        @error('password_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                </div>

               <div class="row">    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Role
                        </label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror">
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('role', $user->id_role ?? '') == $item->id ? 'selected' : '' }}>
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
                    Save
                </button>
            </div>
        </div>

            </form>

        </div>

    </div>

</div>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pegawaiId = document.getElementById('id_peg');
        const name = document.getElementById('name');
        const username = document.getElementById('username');
        const pegawaiOptions = document.getElementById('pegawai-options');
        const pegawaiSearchError = document.getElementById('pegawai-search-error');

        if (pegawaiId && pegawaiOptions) {
            const options = Array.from(pegawaiOptions.options);

            function matchPegawai() {
                const query = name.value.trim().toLocaleLowerCase();
                const match = options.find(function (option) {
                    return option.value.toLocaleLowerCase() === query;
                });

                pegawaiId.value = match ? match.dataset.id : '';
                username.value = match ? match.dataset.username : '';
                name.classList.toggle('is-invalid', Boolean(query && !match));
                pegawaiSearchError.textContent = query && !match
                    ? 'Pilih pegawai dari daftar nama dan NIK yang tersedia.'
                    : '';
            }

            name.addEventListener('input', matchPegawai);
            name.addEventListener('change', matchPegawai);

            if (pegawaiId.value) {
                const selectedPegawai = options.find(function (option) {
                    return option.dataset.id === pegawaiId.value;
                });
                if (selectedPegawai) {
                    name.value = selectedPegawai.value;
                    username.value = selectedPegawai.dataset.username;
                }
            }
        }
    });

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