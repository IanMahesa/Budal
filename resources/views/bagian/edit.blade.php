@extends('partials.all')

@section('title','Edit Bagian')

@section('content')
<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Edit Bagian</h4>
            <div class="table-divider"></div>

            <form method="POST" action="{{ route('bagian.update', $bagian->id_bag) }}" autocomplete="off" id="formSaveUser">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <label>Nama Bagian</label>
                        <input type="text" name="bag" class="form-control" value="{{ old('bag', $bagian->bag) }}" autocomplete="off" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    </div>

                    <div class="col-lg-4 mb-3">
                        <label>Kode Bagian</label>
                        <input type="text" name="kode_bag" class="form-control @error('kode_bag') is-invalid @enderror" style="text-transform: uppercase;" 
                            oninput="this.value = this.value.toUpperCase()" value="{{ old('kode_bag', $bagian->kode_bag) }}" autocomplete="off" maxlength="10" required>
                        @error('kode_bag')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="table-divider mt-4"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
                    <a href="{{ route('bagian.index') }}" class="btn btn-back">
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
