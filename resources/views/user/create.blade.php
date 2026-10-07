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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Nama User
                        </label>

                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name ?? '') }}" placeholder="Masukkan nama user" autocomplete="off" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">

                        @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Username Login
                        </label>

                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                            value="{{ old('username', $user->username ?? '') }}" placeholder="Masukkan username" autocomplete="new-password" required>

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

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jabatan</label>
                        <select name="jabatan" id="jabatan" class="form-select @error('jabatan') is-invalid @enderror" required>
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach(['Direktur', 'Manajer', 'A.Manajer', 'Staff'] as $jabatan)
                                <option value="{{ $jabatan }}"
                                    {{ old('jabatan', $user->jabatan ?? '') === $jabatan ? 'selected' : '' }}>
                                    {{ $jabatan === 'A.Manajer' ? 'Asisten Manajer' : $jabatan }}
                                </option>
                            @endforeach
                        </select>

                        @error('jabatan')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3 d-none" id="bagian-wrapper">
                        <label class="form-label">Bagian</label>
                        <select name="id_bag" id="id_bag" class="form-select @error('id_bag') is-invalid @enderror" data-selected-value="{{ old('id_bag') }}">
                            <option value="">-- Pilih Bagian --</option>
                            @foreach($bagian as $item)
                                <option value="{{ $item->id_bag }}" {{ old('id_bag') == $item->id_bag ? 'selected' : '' }}>{{ $item->bag }}</option>
                            @endforeach
                        </select>
                        @error('id_bag')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3 d-none" id="subbagian-wrapper">
                        <label class="form-label">Sub Bagian</label>
                        <select name="id_subag" id="id_subag" class="form-select @error('id_subag') is-invalid @enderror" data-selected-value="{{ old('id_subag') }}" disabled>
                            <option value="">-- Pilih Sub Bagian --</option>
                        </select>
                        @error('id_subag')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
        const jabatan = document.getElementById('jabatan');
        const bagianWrapper = document.getElementById('bagian-wrapper');
        const subbagianWrapper = document.getElementById('subbagian-wrapper');
        const bagian = document.getElementById('id_bag');
        const subbagian = document.getElementById('id_subag');

        function loadSubbagian(idBag) {
            subbagian.innerHTML = '<option value="">-- Pilih Sub Bagian --</option>';
            subbagian.disabled = !idBag;
            if (!idBag) return;

            fetch('{{ route('subbag.byBagian') }}?id_bag=' + encodeURIComponent(idBag))
                .then(response => response.json())
                .then(rows => {
                    rows.forEach(row => {
                        subbagian.insertAdjacentHTML('beforeend', '<option value="' + row.id_subag + '">' + row.sub_bag + '</option>');
                    });
                    subbagian.value = subbagian.dataset.selectedValue || '';
                    subbagian.disabled = false;
                });
        }

        function updateFields() {
            const needsBagian = ['Manajer', 'A.Manajer', 'Staff'].includes(jabatan.value);
            const needsSubbagian = ['A.Manajer', 'Staff'].includes(jabatan.value);
            bagianWrapper.classList.toggle('d-none', !needsBagian);
            subbagianWrapper.classList.toggle('d-none', !needsSubbagian);
            bagian.required = needsBagian;
            subbagian.required = needsSubbagian;
            if (!needsBagian) {
                bagian.value = '';
                subbagian.value = '';
                subbagian.disabled = true;
            } else if (needsSubbagian) {
                loadSubbagian(bagian.value);
            } else {
                subbagian.value = '';
                subbagian.disabled = true;
            }
        }

        jabatan.addEventListener('change', updateFields);
        bagian.addEventListener('change', function () {
            subbagian.dataset.selectedValue = '';
            if (['A.Manajer', 'Staff'].includes(jabatan.value)) loadSubbagian(this.value);
        });
        updateFields();
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