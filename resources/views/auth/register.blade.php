@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register-store') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                      
                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>
                        <div class="row mb-3">
                            {{-- <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label> --}}

                            <div class="col-md-6">
                                <input type="text" class="form-control" value="{{ $roles->name }}" name="roles" required hidden>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="Provinsi" class="col-md-4 col-form-label text-md-end">{{ __('Provinsi') }}</label>

                            <div class="col-md-6">
                                <select class="form-control selectOption " name="province_id" id="provinsi" required>
                                    <option>==Pilih Salah Satu==</option>
                                    @foreach ($province as $item)
                                        <option value="{{ $item->code ?? '' }}">{{ $item->name_prov ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="Provinsi" class="col-md-4 col-form-label text-md-end">{{ __('Kabupaten / Kota') }}</label>

                            <div class="col-md-6">
                                <select class="form-control selectOption" name="kota_id" id="kota" required>
                                    <option>==Pilih Salah Satu==</option>
                                    {{-- @foreach ($kota as $item) --}}
                                        {{-- <option value="{{ $item->id ?? '' }}">{{ $item->name ?? '' }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="Provinsi" class="col-md-4 col-form-label text-md-end">{{ __('Kecamatan') }}</label>

                            <div class="col-md-6">
                                <select class="form-control selectOption" name="kecamatan_id" id="kecamatan">
                                    <option value="">==Pilih Salah Satu==</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="Provinsi" class="col-md-4 col-form-label text-md-end">{{ __('Kelurahan') }}</label>

                            <div class="col-md-6">
                                <select class="form-control selectOption" name="kelurahan_id" id="Kelurahan" required>
                                    <option>==Pilih Salah Satu==</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
      $('.selectOption').select2();
  });
</script>
  <script>
     // Memuat data kota setelah provinsi dipilih
      $(document).ready(function() {
          $('#provinsi').on('change', function() {
              var provinsiId = $(this).val();
              if(provinsiId) {
                  $.ajax({
                          url: '{{ route("getKota") }}',
                          type: 'POST',
                          data: {
                          "_token": "{{ csrf_token() }}",
                          "provinsi": provinsiId
                      },
                      dataType: 'JSON',
                      success: function(data) {
                      $('#kota').empty();
                          $.each(data, function(key, value) {
                              $('#kota').append('<option value="'+ value.code +'">'+ value.name_cities +'</option>');
                             
                          });
                      }
              });
              } else {
              $('#kota').empty();
              }
          });
      });
      
  </script>
   <script>
      $('#kota').change(function () {
          var regencyId = $(this).val();
          
          $.ajax({
              url: '/kecamatan/getByRegency/' + regencyId,
              type: 'GET',
              dataType: 'json',
              success: function (data) {
                  // console.log(data);
                  $('#kecamatan').empty();

                  $.each(data, function (key, value) {
                      $('#kecamatan').append('<option value="' + value.code + '">' + value.name_districts + '</option>');
                  });
              }
          });
      });
   </script>
   <script>
      $('#kecamatan').change(function () {
          var kelurahanId = $(this).val();
          
          $.ajax({
              url: '/kelurahan/getByRegency/' + kelurahanId,
              type: 'GET',
              dataType: 'json',
              success: function (data) {
                  // console.log(data);
                  $('#Kelurahan').empty();

                  $.each(data, function (key, value) {
                      $('#Kelurahan').append('<option value="' + value.code + '">' + value.name_village + '</option>');
                  });
              }
          });
      });
   </script>
@endsection
