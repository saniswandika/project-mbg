@extends('layouts.masterTemplate')


@section('content')
<div class="card" >
     <div class="card-header ">
      
        <div class="pull-left">
            <h2>Buat Akun Baru User</h2>
        </div>
        <div class="text-end" style="margin-bottom: -50px;">
            <a class="btn btn-primary ml-2" href="{{ route('users.index') }}">Kembali</a>
        </div>
        {{-- <a href="{{ route('rekomendasi_terdaftar_yayasans.index') }}" class="btn btn-primary ml-2">Kembali</a> --}}
    </div>
    
    @if (count($errors) > 0)
      <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
           @foreach ($errors->all() as $error)
             <li>{{ $error }}</li>
           @endforeach
        </ul>
      </div>
    @endif
    <div class="card-body">
        {!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12">
                    <strong>Name:</strong>

                <div class="input-group input-group-outline mb-3">
                    {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <strong>Email:</strong>

                <div class="input-group input-group-outline mb-3">
                    {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <strong>Password:</strong>

                <div class="input-group input-group-outline mb-3">
                    {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <strong>Confirm Password:</strong>

                <div class="input-group input-group-outline mb-3">
                    {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
               <div class="input-group input-group-outline mb-3">
                    <select class="form-control" name="roles" id="roles">
                        <option disabled selected>Pilih Role</option>
                        @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
         
        </div>
        {!! Form::close() !!}
    </div>
  </div>







{{-- <p class="text-center text-primary"><small>Tutorial by ItSolutionStuff.com</small></p> --}}
@endsection