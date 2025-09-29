@extends('layouts.masterTemplate')

@section('title', 'Management Akun')


@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<div class="container">
  @if ($message = Session::get('masuk'))
    <div class="alert alert-success alert-dismissible text-white fade show" role="alert">
      <span class="alert-text"><strong>Sukses!</strong> {{ $message }}</span>
      <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <div class="card text-center">
    <div class="card-header">
      <h2>Users Management</h2>
    </div>
    
    <div class="card-body">
      <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-right text-left" style="margin-bottom: 20px;">
              <!-- Button trigger modal -->
              <a  class="btn btn-success" href="/users/create">Create</a>
              {{-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#buatakunuser">
                Buat Baru Akun
              </button> --}}

              <!-- Modal -->
              <div class="modal fade" id="buatakunuser" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      {!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}
                          <div class="row">
                            
                              <div class="col-xs-12 col-sm-12 col-md-12">

                                  <div class="input-group input-group-outline mb-3">
                                      {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                  </div>
                              </div>
                              <strong>Email:</strong>
                              <div class="col-xs-12 col-sm-12 col-md-12">

                                  <div class="input-group input-group-outline mb-3">
                                      {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                                  </div>
                              </div>
                              <strong>Password:</strong>

                              <div class="col-xs-12 col-sm-12 col-md-12">

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
                                    <strong>Role:</strong>

                                    <select class="form-control" name="roles" id="exampleFormControlSelect1">
                                      <option selected>
                                        {{-- @if(!empty($user->getRoleNames()))
                                          @foreach($user->getRoleNames() as $v)
                                              <label class="badge badge-success">{{ $v }}</label>
                                          @endforeach
                                        @endif --}}
                                      </option>
                                      @foreach ($roles as $role )
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                      @endforeach
                                  </select> 
                                    
                                    {{-- {!! Form::select('roles[]', $roles,[], array('class' => 'form-control','option')) !!} --}}
                                </div>
                            </div>
                              <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                  <button type="submit" class="btn btn-primary">Submit</button>
                              </div>
                          </div>
                        {!! Form::close() !!}
                    </div>
                    {{-- <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="button" class="btn btn-primary">Save changes</button>
                    </div> --}}
                  </div>
                </div>
              </div>
            </div>
        </div>
      </div>
      <div class="tab-pane fade show table-responsive">
      <table id="tableuser" class="table table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Email</th>
            <th width="280px">Action</th>
          </tr>
        </thead>
        @foreach ($data as $key => $user)
          <tr>
            <td>{{ ++$i }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
              <button type="button" class="btn btn-info" data-toggle="modal" data-target="#Show{{ $user->id }}">
                Show
              </button>
              <div class="modal fade" id="Show{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Akun {{ $user->name }}</h5>
               
                    </div>
                    <div class="modal-body text-left">
                      {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                      <div class="row">
                          <div class="col-xs-12 col-sm-12 col-md-12">
                              <div class="form-group">
                                  <strong>Name:</strong>
                                  <input type="email" class="form-control" id="exampleInputEmail1" value="{{ $user->name }}" aria-describedby="emailHelp" placeholder="Enter email" disabled>
                                  {{-- {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control disable')) !!} --}}
                              </div>
                          </div>
                          <div class="col-xs-12 col-sm-12 col-md-12">
                              <div class="form-group">
                                  <strong>Email:</strong>
                                  <input type="email" class="form-control" id="exampleInputEmail1" value="{{ $user->email }}" aria-describedby="emailHelp" placeholder="Enter email" disabled>
                                  {{-- {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!} --}}
                              </div>
                          </div>
                          <div class="col-xs-12 col-sm-12 col-md-12 text-center mt-4">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">tutup</span>
                              </button>
                          </div>
                      </div>
                      
                      {!! Form::close() !!}
                    </div>
                  </div>
                </div>
              </div>
              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editUser{{ $user->id }}">
                Edit
              </button>
              <div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Edit Akun {{ $user->name }}</h5>
                  
                    </div>
                  
                    <div class="modal-body text-left">
                      {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
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
                          <div class="form-group">
                            <strong>Role:</strong>
                            <select class="form-control" name="roles" id="exampleFormControlSelect1">
                              <option selected>
                                {{-- @if(!empty($user->getRoleNames()))
                                  @foreach($user->getRoleNames() as $v)
                                      <label class="badge badge-success">{{ $v }}</label>
                                  @endforeach
                                @endif --}}
                              </option>
                              @foreach ($roles as $role )
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                              @endforeach
                          </select> 
                            
                            {{-- {!! Form::select('roles[]', $roles,[], array('class' => 'form-control','option')) !!} --}}
                        </div>
                          <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                              <button type="submit" class="btn btn-primary">kirim</button>
                              <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">
                                      <span aria-hidden="true">tutup</span>
                              </button>
                          </div>
                      </div>
                      {!! Form::close() !!}
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        @endforeach
        </table>
      </div>
    </div>
    {!! $data->render() !!}
  </div>
</div>
@endsection