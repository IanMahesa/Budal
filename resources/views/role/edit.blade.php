@extends('partials.all')

@section('title', 'Edit Role')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">

            <h4 class="ms-3">Edit Role</h4>

            <div class="table-divider mb-4"></div>

            <form action="{{ route('role.update', $role->id) }}" method="POST" id="formUpdateRole">
                @csrf
                @method('PUT')

                {{-- ROLE NAME --}}
                <div class="role-form">
                    <label for="name">Role Name</label>

                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="Enter role name"
                           value="{{ old('name', $role->name) }}"
                           autocomplete="off" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="table-divider mb-4"></div>

                {{-- PERMISSION TABLE --}}
                <div class="table-responsive">

                    <table id="tabelRole"
                           class="table table-striped table-hover mb-0"
                           width="100%">

                        <thead class="table-light text-center">
                            <tr>
                                <th>
                                    RULES
                                </th>

                                @foreach(['list', 'create', 'update', 'delete', 'manage'] as $action)
                                    <th>
                                        <div>{{ strtoupper($action) }}</div>

                                        <input type="checkbox"
                                               class="permission-select-all"
                                               data-action="{{ $action }}"
                                               aria-label="Pilih semua permission {{ $action }}">
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody id="permissionTableBody">

                            @forelse($permissionMatrix as $feature => $actions)

                                <tr class="permission-row"
                                    data-feature="{{ strtolower($feature) }}">

                                    <td>
                                        {{ ucwords($feature) }}
                                    </td>

                                    @foreach(['list', 'create', 'update', 'delete', 'manage'] as $action)

                                        <td class="text-center align-middle">

                                            @php
                                                $permissionName = $actions[$action] ?? null;

                                                /*
                                                 * Saat pertama kali membuka halaman edit,
                                                 * cek permission yang dimiliki role.
                                                 *
                                                 * Jika validasi gagal, gunakan old('permissions')
                                                 * agar pilihan user tidak hilang.
                                                 */
                                                $oldPermissions = old('permissions', $rolePermissions);

                                                $isChecked = $permissionName &&
                                                    in_array($permissionName, $oldPermissions);
                                            @endphp

                                            @if($permissionName)

                                                <input type="checkbox"
                                                       class="permission-checkbox {{ $action === 'manage' ? 'manage-checkbox' : '' }}"
                                                       data-action="{{ $action }}"
                                                       name="permissions[]"
                                                       value="{{ $permissionName }}"
                                                       {{ $isChecked ? 'checked' : '' }}>

                                            @endif

                                        </td>

                                    @endforeach

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        Belum ada permission.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="table-divider mt-3 mb-4"></div>

                {{-- BUTTON --}}
                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">

                    <a href="{{ route('role.index') }}" class="btn btn-back">
                        <i class="fas fa-window-close"></i>
                        Cancel
                    </a>

                    <button type="button"
                            class="btn btn-save"
                            onclick="confirmUpdateRole(event)">

                        <i class="fas fa-save"></i>
                        Update

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    window.confirmUpdateRole = function (event) {
        event.preventDefault();

        Swal.fire({
            title: 'Konfirmasi Perubahan',
            text: 'Apakah Anda yakin ingin menyimpan perubahan role?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (result.isConfirmed) {
                document.getElementById('formUpdateRole').submit();
            }
        });
    };

    const searchInput = document.getElementById('searchPermission');

    if (searchInput) {

        searchInput.addEventListener('keyup', function () {

            const keyword = this.value.toLowerCase();

            document.querySelectorAll('.permission-row').forEach(function (row) {

                const feature = row.dataset.feature.toLowerCase();

                if (feature.includes(keyword)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }

            });

        });

    }

    function updateSelectAll(action) {

        const selectAll = document.querySelector(
            `.permission-select-all[data-action="${action}"]`
        );

        const permissions = document.querySelectorAll(
            `.permission-checkbox[data-action="${action}"]`
        );

        const checkedCount = document.querySelectorAll(
            `.permission-checkbox[data-action="${action}"]:checked`
        ).length;

        if (!selectAll) {
            return;
        }

        selectAll.checked =
            permissions.length > 0 &&
            checkedCount === permissions.length;

        selectAll.indeterminate =
            checkedCount > 0 &&
            checkedCount < permissions.length;

    }

    document.querySelectorAll('.permission-select-all').forEach(function (selectAll) {

        selectAll.addEventListener('change', function () {

            const action = this.dataset.action;

            document.querySelectorAll(
                `.permission-checkbox[data-action="${action}"]`
            ).forEach(function (permission) {

                permission.checked = selectAll.checked;

            });

            updateSelectAll(action);

        });

    });

    document.querySelectorAll('.permission-checkbox').forEach(function (permission) {

        permission.addEventListener('change', function () {

            updateSelectAll(this.dataset.action);

        });

    });

    [
        'list',
        'create',
        'update',
        'delete',
        'manage'
    ].forEach(function (action) {

        updateSelectAll(action);

    });

});

</script>

@endpush

@endsection
