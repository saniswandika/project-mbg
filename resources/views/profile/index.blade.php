@extends('layouts.masterTemplate')

@section('title', 'Profile')

@section('content')
    <div class="container rounded bg-white shadow-lg card">
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
        
@endsection
