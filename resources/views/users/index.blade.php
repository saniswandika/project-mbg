@extends('layouts.masterTemplate')

@section('title', 'Management Akun')

@section('content')
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>

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
      <div class="tab-pane fade show table-responsive">
        <table id="userTable" class="display">
            <thead>
                <tr>
                  <th>no</th>
                  <th>name</th>
                  <th>email</th>
                  <th>action</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>no</th>
                    <th>name</th>
                    <th>email</th>
                    <th>action</th>
                </tr>
            </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

    <!-- Modal Show (Detail User) -->
    <div class="modal fade" id="ShowModal" tabindex="-1" role="dialog" aria-labelledby="ShowModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="ShowModalLabel">Akun Detail</h5>
          </div>
          <div class="modal-body">
            <div><strong>Name:</strong> <span id="userName"></span></div>
            <div><strong>Email:</strong> <span id="userEmail"></span></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>

  <!-- Modal Edit (Detail User) -->
    @foreach ($data as $key => $user)
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
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <strong>Role:</strong>

                  <div class="input-group input-group-outline mb-3">
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
    @endforeach
    
@endsection


