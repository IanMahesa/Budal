@extends('partials.all')

@section('title','Tambah SubBagian')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="mb-3">Tambah Sub Bagian</h4>

            <div class="table-divider mb-4"></div>

            <form method="POST" action="{{ route('subag.store') }}" autocomplete="off" id="formSaveUser">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Bagian</label>
                        <div class="form-control" style="background-color: #e9ecef;">
                            {{ $bagian->bag }}
                            <span class="badge bg-primary ms-2">
                                {{ $bagian->kode_bag }}
                            </span>
                        </div>
                    <input type="hidden" name="id_bag" value="{{ $bagian->id_bag }}">
                </div>               

                <div class="mb-3">
                    <label class="form-label">Nama Sub Bagian</label>
                    <input type="text" name="sub_bag" class="form-control" value="{{ old('sub_bag') }}" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">

                    @error('sub_bag')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Kode Sub Bagian</label>
                        <input type="text" name="kode_subag" class="form-control" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase()" maxlength="10" value="{{ old('kode_subag') }}" required>

                        @error('kode_subag')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror
                </div>

                <div class="table-divider mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">

                    <a href="{{ route('subag.index') }}" class="btn btn-back">
                        <i class="fas fa-long-arrow-alt-left"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-save" onclick="confirmSaveUser(event)">
                            <i class="fas fa-save"></i> Save
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>
@endsection