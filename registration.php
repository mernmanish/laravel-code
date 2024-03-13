@extends('layout.buyer')
@section('content')
<div class="container" >
    <div class="row">
        <div class="col-md-12">
            <form method="post" action="{{ route('registered') }}">
            {{ csrf_field() }}
            <div class="mt-5" >
                <p class="form-title text-center">REGISTER</p>
                   @if (session('message'))
                        <div class="alert alert-warning">
                            {{ session('message') }}
                        </div>
                    @endif 
                <div class="form-group mt-4 ">
                    <label class="form-label">Phone Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">+91 </span>
                        </div>
                        <input type="number" class="form-control form-input" autocomplete="off" name="number" id="number" onkeypress="if(this.value.length==10) return false;" onkeydown="return event.keyCode !== 69" >
                    </div>
                    <!-- <input type="text" class="form-control form-input" name="number"> -->
                    @if ($errors->has('number'))
                    <span class="help-block errorText">
                    {{ $errors->first('number') }}
                    </span>
                    @endif
                    
                </div>
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control form-input" name="firstname">
                    @if ($errors->has('firstname'))
                    <span class="help-block errorText">
                    {{ $errors->first('firstname') }}
                    </span>
                    @endif
                    
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control form-input" name="lastname">
                    @if ($errors->has('lastname'))
                    <span class="help-block errorText">
                    {{ $errors->first('lastname') }}
                    </span>
                    @endif
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control form-input" name="password">
                    @if ($errors->has('password'))
                    <span class="help-block errorText">
                    {{ $errors->first('password') }}
                    </span>
                    @endif
                </div>
                <div>
                   <button type="submit" class="btn mt-2 btn-primary blue-btn" style="width:100%;">Register</button>
                </div>
                
            </div> 
            </form>
            <div class="text-center mt-2">
                    <p class="para-footer">Already an User ? <a href="{{route('login')}}" class="action-link">Login Now</a></p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="registerd-user-alert" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document" style="margin-top: 320px;width: 388px;">
    <div class="modal-content">
      <div class="modal-body">
        <p class="registration-model-content" style="font-weight:bold; font-size:14px; margin-bottom: 0;">You are already registered with us, but your account is inactive. Please active it to continue</p>
        <a href="{{route('registration-otp')}}" class="action-link pull-right" style="font-weight:bold;">Active</a>
      </div>
    </div>
  </div>
</div>
@if(!empty($number))
<script>
    $(document).ready(function(){
        $('#registerd-user-alert').modal('show');
    });
</script>
@endif
@endsection