@extends('partials.all')

@section('title', 'Create Role')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Tambah Role</h4>

            <div class="table-divider mb-4"></div>

        <form action="{{ route('role.store') }}" method="POST">
            @csrf
            <div class="role-form">
                <label for="name"> Role Name </label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                    placeholder="Enter role name" value="{{ old('name') }}" autocomplete="off" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">

                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="table-divider mb-4"></div>

            <div class="table-responsive">
            <table id="tabelRole" class="table table-striped table-hover mb-0"
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
                            <tr class="permission-row" data-feature="{{ $feature }}">
                                <td>{{ ucwords($feature) }}</td>
                                @foreach(['list', 'create', 'update', 'delete', 'manage'] as $action)
                                    <td class="text-center align-middle">
                                        <input type="checkbox"
                                               class="permission-checkbox {{ $action === 'manage' ? 'manage-checkbox' : '' }}"
                                                                                             data-action="{{ $action }}"
                                               name="permissions[]"
                                               value="{{ $actions[$action] }}">
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

            <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
                <a href="{{ route('role.index') }}" class="btn btn-back">
                    <i class="fas fa-window-close"></i>Cancel
                </a>
                <button type="button" class="btn btn-save" onclick="confirmSaveUser(event)">
                    <i class="fas fa-save"></i> Save
                </button>

            </div>

        </form>

    </div>

</div>


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('searchPermission');

    if (searchInput) {
        searchInput.addEventListener('keyup', function () {

            const keyword = this.value.toLowerCase();

            document.querySelectorAll('.permission-row').forEach(function (row) {

                const feature = row.dataset.feature;

                if (feature.includes(keyword)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }

            });
        });
    }

    function updateSelectAll(action) {
        const selectAll = document.querySelector(`.permission-select-all[data-action="${action}"]`);
        const permissions = document.querySelectorAll(`.permission-checkbox[data-action="${action}"]`);
        const checkedCount = document.querySelectorAll(`.permission-checkbox[data-action="${action}"]:checked`).length;

        if (!selectAll) return;

        selectAll.checked = permissions.length > 0 && checkedCount === permissions.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < permissions.length;
    }

    document.querySelectorAll('.permission-select-all').forEach(function (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll(`.permission-checkbox[data-action="${this.dataset.action}"]`)
                .forEach(function (permission) {
                    permission.checked = selectAll.checked;
                });
        });
    });

    document.querySelectorAll('.permission-checkbox').forEach(function (permission) {
        permission.addEventListener('change', function () {
            updateSelectAll(this.dataset.action);
        });
    });

    ['list', 'create', 'update', 'delete', 'manage'].forEach(updateSelectAll);


});

</script>

@endpush

@endsection