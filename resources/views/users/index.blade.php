@extends('layouts.masterTemplate')

@section('title', 'Management Akun')


@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.css"/>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/js/C"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.js"></script>


<div class="container">
  @if ($message = Session::get('success'))
  <div class="alert alert-success">
    <p>{{ $message }}</p>
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
                                  <div class="form-group">
                                      <strong>Name:</strong>
                                      {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                                  </div>
                              </div>
                              <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Email:</strong>
                                      {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                                  </div>
                              </div>
                              <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Password:</strong>
                                      {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                                  </div>
                              </div>
                              <div class="col-xs-12 col-sm-12 col-md-12">
                                  <div class="form-group">
                                      <strong>Confirm Password:</strong>
                                      {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                                  </div>
                              </div>
                              <div class="col-xs-12 col-sm-12 col-md-12">
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
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
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
                          {{-- <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                              <button type="submit" class="btn btn-primary">Submit</button>
                          </div> --}}
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
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body text-left">
                      {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                      <div class="row">
                          <div class="col-xs-12 col-sm-12 col-md-12">
                              <div class="form-group">
                                  <strong>Name:</strong>
                                  {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                              </div>
                          </div>
                          <div class="col-xs-12 col-sm-12 col-md-12">
                              <div class="form-group">
                                  <strong>Email:</strong>
                                  {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                              </div>
                          </div>
                          <div class="col-xs-12 col-sm-12 col-md-12">
                              <div class="form-group">
                                  <strong>Password:</strong>
                                  {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                              </div>
                          </div>
                          <div class="col-xs-12 col-sm-12 col-md-12">
                              <div class="form-group">
                                  <strong>Confirm Password:</strong>
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
                              <button type="submit" class="btn btn-primary">Submit</button>
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
    <div class="card-footer text-muted">
      2 days ago
    </div>
    
  </div>
</div>




<p class="text-center text-primary"><small>Tutorial by ItSolutionStuff.com</small></p>
@endsection