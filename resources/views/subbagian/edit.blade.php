@extends('partials.all')

@section('title','Edit SubBagian')

@section('content')
<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="mb-3">Edit SubBagian</h4>

            <div class="table-divider mb-4"></div>

            <form method="POST" action="{{ route('subag.update', $subag->id_subag) }}" autocomplete="off" id="formSaveUser">
                @csrf
                @method('PUT')
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
                    <label class="form-label">Nama SubBagian</label>
                        <input type="text" name="sub_bag" class="form-control @error('sub_bag') is-invalid @enderror" value="{{ old('sub_bag', $subag->sub_bag) }}" autocomplete="off" required>
                        @error('sub_bag')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Kode SubBagian</label>
                        <input type="text" name="kode_subag" class="form-control @error('kode_subag') is-invalid @enderror" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase()" maxlength="10" value="{{ old('kode_subag', $subag->kode_subag) }}" autocomplete="off" required>
                        @error('kode_subag')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                </div>

                <div class="table-divider mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
                    <a href="{{ route('subag.index') }}" class="btn btn-back">
                        <i class="fas fa-long-arrow-alt-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
