@extends('partials.all')

@section('title', 'Tambah Ijin Pribadi')

@section('content')

<div class="container">
    <div class="card user-card">
        <div class="card-body table-responsive">
            <h4 class="ms-3">Tambah Ijin Pribadi</h4>

            <div class="table-divider mb-4"></div>
			
                <form action="{{ route('ijin.store') }}" method="POST" id="formSaveUser">
                    @csrf
					<div class="row">
						<div class="col-lg-8 mb-3">
							<label for="izin" class="form-label font-weight-bold">Jenis Ijin</label>
							<input type="text" name="izin" id="izin" 
								class="form-control @error('izin') is-invalid @enderror" value="{{ old('izin') }}" 
								placeholder="Masukkan jenis ijin (contoh: Ijin Sakit)" autocomplete="off" required 
								oninput="this.value = this.value.replace(/\b\w/g, c => c.toUpperCase());">
								
							@error('izin')
								<div class="invalid-feedback">
									{{ $message }}
								</div>
							@enderror
						</div>

						<div class="col-lg-4 mb-3">
							<label for="kode" class="form-label font-weight-bold">Kode</label>
							<input type="text" name="kode" id="kode"
								class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode') }}"
								placeholder="Masukkan kode unik (contoh: IS)" autocomplete="off" maxlength="5"
								style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase();" required>
							
							@error('kode')
								<div class="invalid-feedback">
									{{ $message }}
								</div>
							@enderror
						</div>
					</div>
					
					<div class="table-divider mt-4"></div>

                    <div class="d-flex justify-content-between align-items-center mt-3 ms-3 me-3">
                        <a href="{{ route('ijin.index') }}" class="btn btn-back">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="button" class="btn btn-save" onclick="confirmSaveUser(event)">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection