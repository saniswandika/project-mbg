@extends('layouts.masterTemplate')
@section('content')
        <div class="row p-3 d-flex justify-content-center">
            <div class="card card-primary card-outline col-xl-6 col-md-6 col-sm-6 p-3 " style="margin-right: 20px;">
                <div class="card-body box-profile ">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                        src="{{asset('images/pp.png')}}"
                        alt="User profile picture">
                    </div>
                    <h3 class="profile-username text-center"> Role {{ $role->name }}</h3>

                    <ul class="list-group list-group-unbordered mb-3 center">
                        {{-- @foreach($akses_user as $value) --}}
                            {{-- <li class="list-group-item"> --}}
                                @if(!empty($rolePermissions))
                                    @foreach($rolePermissions as $v)
                                        <li class="list-group-item">{{ $v->name }},</li>
                                    @endforeach
                                @endif
                            {{-- </li> --}}
                        {{-- @endforeach --}}
                 
                    </ul>
                    <ul class="list-group list-group-unbordered mb-3 center">
                        <a class="btn btn-primary" href="{{ route('roles.index') }}"> kembali</a>
                    </ul>
                </div>
            </div>
        </div>

{{-- <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Name:</strong>
            {{ $role->name }}
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-12">
        <div class="form-group">
            <strong>Permissions:</strong>
            @if(!empty($rolePermissions))
                @foreach($rolePermissions as $v)
                    <label class="label label-success">{{ $v->name }},</label>
                @endforeach
            @endif
        </div>
    </div>
</div> --}}
@endsection