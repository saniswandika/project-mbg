@extends('layouts.masterTemplate')

@section('title', 'Profile')

@section('content')
    {{-- <div class="container rounded bg-white shadow-lg card">
        <div class="row">
            <div class="col-md-2 border-right">
                <img src="{{ asset('assets/img/undraw_profile.svg') }}" class="rounded-circle mt-5" width="150">
            </div>
            <div class="col">
                <div class="p-3 py-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-right">Profile {{ Auth::user()->name }}</h4>
                    </div>
                    {!! Form::open(['route' => 'nama.action', 'method' => 'POST']) !!}
                    <div class="form-group">
                        <label>Nama:</label>
                        <div class="input-group mb-3">
                            <input name="new_name"type="text" class="form-control" placeholder="Nama Anda" aria-label="Recipient's username" aria-describedby="basic-addon2" value="{{ Auth::user()->name }}">
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary" type="submit">Ganti</button>
                            </div>
                          </div>
                    </div>
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'email.action', 'method' => 'POST']) !!}
                    <div class="form-group">
                        <label>Email:</label>
                        <div class="input-group mb-3">
                            <input name="new_email"type="text" class="form-control" placeholder="Email Anda" aria-label="Recipient's username" aria-describedby="basic-addon2" value="{{ Auth::user()->email }}">
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary" type="submit">Ganti</button>
                            </div>
                          </div>
                    </div>
                    <br>

                    {!! Form::close() !!}
                    {!! Form::open(['route' => 'password.action', 'method' => 'POST']) !!}

                    <label>Ganti Kata Sandi:</label>
                    <div class="form-group">
                        <label>Password Lama <span class="text-danger">*</span></label>
                        <input class="form-control" type="password" name="old_password" />
                    </div>
                    <div class="form-group">
                        <label>Password Baru <span class="text-danger">*</span></label>
                        <input class="form-control" type="password" name="new_password" />
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password <span class="text-danger">*</span></label>
                        <input class="form-control" type="password" name="new_password_confirmation" />
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary float-right mt-3">Ganti password</button>
                    </div>
                    <br>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div> --}}

    <div class="container-fluid px-2 px-md-4">
      <div class="page-header min-height-300 border-radius-xl mt-4" style="background-image: url('https://images.unsplash.com/photo-1531512073830-ba890ca4eba2?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');">
        <span class="mask  bg-gradient-dark  opacity-6"></span>
      </div>
      <div class="card card-body mx-2 mx-md-2 mt-n6">
        <div class="row gx-4 mb-2">
          <div class="col-auto">
            <div class="avatar avatar-xl position-relative">
              <img src="{{ asset('assets/img/bruce-mars.jpg') }}" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
            </div>
          </div>
          <div class="col-auto my-auto">
            <div class="h-100">
              <h5 class="mb-1">
                {{ Auth::user()->name }}
              </h5>
              <p class="mb-0 font-weight-normal text-sm">
                {{ $role_id }}
              </p>
            </div>
          </div>
       <!-- Tabs Header -->
        <div class="col-lg-4 col-md-6 my-sm-auto ms-sm-auto me-sm-0 mx-auto mt-3">
            <div class="nav-wrapper position-relative end-0">
                <ul class="nav nav-pills nav-fill p-1" id="settingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link mb-0 px-0 py-1 active" id="user-tab" data-bs-toggle="tab" href="#userSetting" role="tab" aria-controls="userSetting" aria-selected="true">
                        <i class="material-symbols-rounded text-lg position-relative">home</i>
                        <span class="ms-1">User Setting</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link mb-0 px-0 py-1" id="profile-tab" data-bs-toggle="tab" href="#profileSetting" role="tab" aria-controls="profileSetting" aria-selected="false">
                        <i class="material-symbols-rounded text-lg position-relative">email</i>
                        <span class="ms-1">Profile Setting</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Tabs Content -->
        <div class="col-12 mt-4">
            <div class="tab-content" id="settingTabsContent">
                <!-- User Setting Tab -->
                <div class="tab-pane fade show active" id="userSetting" role="tabpanel" aria-labelledby="user-tab">
                {!! Form::open(['route' => 'nama.action', 'method' => 'POST']) !!}
                    <div class="form-group">
                        <label>Nama:</label>
                        <div class="input-group mb-3">
                            <input name="new_name"type="text" class="form-control" placeholder="Nama Anda" aria-label="Recipient's username" aria-describedby="basic-addon2" value="{{ Auth::user()->name }}">
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary" type="submit">Ganti</button>
                            </div>
                          </div>
                    </div>
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'email.action', 'method' => 'POST']) !!}
                    <div class="form-group">
                        <label>Email:</label>
                        <div class="input-group mb-3">
                            <input name="new_email"type="text" class="form-control" placeholder="Email Anda" aria-label="Recipient's username" aria-describedby="basic-addon2" value="{{ Auth::user()->email }}">
                            <div class="input-group-append">
                              <button class="btn btn-outline-secondary" type="submit">Ganti</button>
                            </div>
                          </div>
                    </div>
                    <br>

                    {!! Form::close() !!}
                    {!! Form::open(['route' => 'password.action', 'method' => 'POST']) !!}

                    <label>Ganti Kata Sandi:</label>
                    <div class="form-group">
                        <label>Password Lama <span class="text-danger">*</span></label>
                        <input class="form-control" type="password" name="old_password" />
                    </div>
                    <div class="form-group">
                        <label>Password Baru <span class="text-danger">*</span></label>
                        <input class="form-control" type="password" name="new_password" />
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password <span class="text-danger">*</span></label>
                        <input class="form-control" type="password" name="new_password_confirmation" />
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary float-right mt-3">Ganti password</button>
                    </div>
                    <br>
                    {!! Form::close() !!}
                </div>

                <!-- Profile Setting Tab -->
<div class="tab-pane fade" id="profileSetting" role="tabpanel" aria-labelledby="profile-tab">
  <div class="card p-3">
    <h5>Profile Pegawai</h5>
    <p>Lengkapi data pegawai Anda di bawah ini.</p>
    <form action="{{ route('pegawai.update', Auth::user()->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="row">
        <!-- NIK -->
        <div class="col-md-6 mb-3">
          <label for="nik" class="form-label">NIK</label>
          <input type="text" name="nik" id="nik" class="form-control" placeholder="Masukkan NIK" value="{{ old('nik', $pegawai->nik ?? '') }}">
        </div>

        <!-- KK -->
        <div class="col-md-6 mb-3">
          <label for="kk" class="form-label">No. KK</label>
          <input type="text" name="kk" id="kk" class="form-control" placeholder="Masukkan No KK" value="{{ old('kk', $pegawai->kk ?? '') }}">
        </div>

        <!-- Foto KTP -->
        <div class="col-md-6 mb-3">
          <label for="foto_ktp" class="form-label">Foto KTP</label>
          <input type="file" name="foto_ktp" id="foto_ktp" class="form-control">
          @if(!empty($pegawai->foto_ktp))
            <small class="text-success">File saat ini: {{ $pegawai->foto_ktp }}</small>
          @endif
        </div>

        <!-- BPJS -->
        <div class="col-md-6 mb-3">
          <label for="bpjs" class="form-label">No. BPJS</label>
          <input type="text" name="bpjs" id="bpjs" class="form-control" placeholder="Masukkan No BPJS" value="{{ old('bpjs', $pegawai->bpjs ?? '') }}">
        </div>

        <!-- No Rekening -->
        <div class="col-md-6 mb-3">
          <label for="no_rekening" class="form-label">No. Rekening</label>
          <input type="text" name="no_rekening" id="no_rekening" class="form-control" placeholder="Masukkan No Rekening" value="{{ old('no_rekening', $pegawai->no_rekening ?? '') }}">
        </div>

        <!-- Bank -->
        <div class="col-md-6 mb-3">
          <label for="bank" class="form-label">Nama </label>
          <input type="text" name="bank" id="bank" class="form-control" placeholder="Contoh: BCA, BRI" value="{{ old('bank', $pegawai->bank ?? '') }}">
        </div>

        <!-- Atas Nama Rekening -->
        <div class="col-md-6 mb-3">
          <label for="atas_nama" class="form-label">Atas Nama Rekening</label>
          <input type="text" name="atas_nama" id="atas_nama" class="form-control" placeholder="Nama Pemilik Rekening" value="{{ old('atas_nama', $pegawai->atas_nama ?? '') }}">
        </div>

        <!-- Alamat -->
        <div class="col-md-12 mb-3">
          <label for="alamat" class="form-label">Alamat Lengkap</label>
          <textarea name="alamat" id="alamat" rows="3" class="form-control" placeholder="Masukkan alamat lengkap">{{ old('alamat', $pegawai->alamat ?? '') }}</textarea>
        </div>
      </div>

      <button type="submit" class="btn btn-success">Update Profil Pegawai</button>
    </form>
  </div>
</div>

            </div>
        </div>

        </div>
      </div>
    </div>
        
@endsection
