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
    <form action="{{ route('roles.store') }}" method="POST">
    @csrf
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div class="p-2 bd-highlight">Create Role</div>
                <div class="p-2 bd-highlight">
                    <ul class="list-group list-group-unbordered center">
                        <a class="btn btn-primary" href="{{ route('roles.index') }}">Kembali</a>
                    </ul>
                </div>
            </div>
        </div>

        <div class="accordion-1">
            <div class="row">
                <div class="col-md-10 mx-auto">
                    {{-- Nama Role --}}
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="Name" class="form-label">Nama Role</label>
                        <div class="input-group input-group-outline mb-3">
                            <input type="text" id="Name" name="Name" class="form-control" required>
                        </div>
                    </div>

                    {{-- Permissions --}}
                    <div class="accordion" id="accordionPermission">
                        @foreach($groupedPermissions as $group => $permissions)
                            <div class="accordion-item mb-3">
                                <h5 class="accordion-header" id="heading{{ $group }}">
                                    <button class="accordion-button border-bottom font-weight-bold collapsed" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse{{ $group }}" 
                                            aria-expanded="false" 
                                            aria-controls="collapse{{ $group }}">
                                        {{ ucfirst($group) }}
                                        <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3"></i>
                                        <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3"></i>
                                    </button>
                                </h5>

                                <div id="collapse{{ $group }}" 
                                    class="accordion-collapse collapse" 
                                    aria-labelledby="heading{{ $group }}" 
                                    data-bs-parent="#accordionPermission">
                                     <div class="accordion-body text-sm">
                                        <div class="row">
                                            @foreach($permissions as $permission)
                                                <div class="text-center col-sm-3 mt-2" style="border: 2px solid #000; border-radius: 6px; padding: 6px; margin-bottom: 8px;">
                                                    <label>
                                                        <input type="checkbox" 
                                                            name="permission[]" 
                                                            value="{{ $permission->id }}">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>


                </div>
            </div>

            {{-- Tombol Submit --}}
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</form>

@endsection