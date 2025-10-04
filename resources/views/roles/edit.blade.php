@extends('layouts.masterTemplate')
@section('content')
    @if ($message = Session::get('masuk'))
    <div class="alert alert-success">
        <a class="close" data-dismiss="alert">×</a>
        <p>{{ $message }}</p>
        <img src="close.soon" style="display:none;" onerror="(function(el){ setTimeout(function(){ el.parentNode.parentNode.removeChild(el.parentNode); },2000 ); })(this);" />
    </div>
    @endif
    @if ($message = Session::get('deleted'))
    <div class="alert alert-danger">
        <a class="close" data-dismiss="alert">×</a>
        <p>{{ $message }}</p>
        <img src="close.soon" style="display:none;" onerror="(function(el){ setTimeout(function(){ el.parentNode.parentNode.removeChild(el.parentNode); },2000 ); })(this);" />
    </div>
    @endif
    {!! Form::model($role, ['method' => 'PATCH','route' => ['roles.update', $role->id]]) !!}
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div class="p-2 bd-highlight">Edit Role {{ $role->name }}</div>
                
                <div class="p-2 bd-highlight">
                    <ul class="list-group list-group-unbordered center">
                        <a class="btn btn-primary" href="{{ route('roles.index') }}">Kembali</a>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="text-center">
                <img class="w-10 border-radius-lg shadow-sm" src="{{ asset('assets/img/pp.jpeg') }}" alt="User profile picture">
            </div>
            <h3 class="profile-username text-center">Role {{ $role->name }}</h3>
            
            <label for="name" class="form-label d-block"><strong>Role :</strong></label>

            <div class="input-group input-group-outline mb-3">
                <input type="text" id="name" name="name" 
                    class="form-control" 
                    placeholder="Masukan Nama"
                    value="{{ $role->name ?? '' }}">
            </div>
            <div class="accordion" id="permissionAccordion">
                @foreach($groupedPermissions as $groupName => $permissions)
                    <div class="accordion-item mt-4">
                        <h2 class="accordion-header" id="heading{{ $loop->iteration }}">
                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->iteration }}" aria-expanded="false" aria-controls="collapse{{ $loop->iteration }}">
                                {{ $groupName }}
                            </button>
                        </h2>
                        <div id="collapse{{ $loop->iteration }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $loop->iteration }}" data-bs-parent="#permissionAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-sm-3 mt-4">
                                            <div class="content">
                                                <ul class="list-group">
                                                    <li class="list-group-item">
                                                        <label>
                                                            {{ Form::checkbox('permission[]', $permission->id, in_array($permission->id, $rolePermissions), ['class' => 'name']) }}
                                                            {{ $permission->name }}
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <ul class="list-group list-group-unbordered mb-3 center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </ul>
    </div>
{!! Form::close() !!}

@endsection