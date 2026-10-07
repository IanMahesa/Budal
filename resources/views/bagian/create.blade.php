@extends('partials.all')

@section('title','Tambah Bagian')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Tambah Bagian</h4>

            <div class="table-divider mb-4"></div>

            <form method="POST" action="{{ route('bagian.store') }}" autocomplete="off" id="formSaveUser">
                @csrf
                <div class="row">
                    <div class="col-lg-8 mb-3">
                        <label>Nama Bagian</label>
                        <input type="text" name="bag" class="form-control" value="{{ old('bag') }}" autocomplete="off" required oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
                    
                        @error('bag')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                    </div>

                    <div class="col-lg-4 mb-3">
                        <label>Kode Bagian</label>
                        <input type="text" name="kode_bag" class="form-control @error('kode_bag') is-invalid @enderror" style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase()" value="{{ old('kode_bag') }}" autocomplete="off" maxlength="10" required>
                    
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
                        <i class="fas fa-window-close"></i> Cancel
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
