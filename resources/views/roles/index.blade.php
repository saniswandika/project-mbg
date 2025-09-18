@extends('layouts.masterTemplate')

@section('title', 'management Role User')


@section('content')
@if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
@endif

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Table Role</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12 margin-tb">
                    <div class="pull-right">
                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                            <h1 class="h3 mb-0 text-gray-800"></h1>
                            {{-- @can('pemakaian-create') --}}
                                <a class="btn btn-success" href="{{ route('roles.create') }}"> Create New Role </a>
                            {{-- @endcan --}}
                        </div>
                    
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="example" class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th width="280px">Action</th>
                         </tr>
                    </thead>
                 
                      @foreach ($roles as $key => $role)
                      <tr>
                          <td>{{ ++$i }}</td>
                          <td>{{ $role->name }}</td>
                          <td>
                              <a class="btn btn-info" href="{{ route('roles.show',$role->id) }}">Show</a>
                              {{-- @can('role-edit') --}}
                                  <a class="btn btn-primary" href="{{ route('roles.edit',$role->id) }}">Edit</a>
                              {{-- @endcan --}}
                              {{-- @can('role-delete') --}}
                                  {!! Form::open(['method' => 'DELETE','route' => ['roles.destroy', $role->id],'style'=>'display:inline']) !!}
                                      {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
                                  {!! Form::close() !!}
                              {{-- @endcan --}}
                          </td>
                      </tr>
                      @endforeach
                </table>
                  
                  {!! $roles->render() !!}
            </div>
        </div>
    </div>

</div>


@endsection