@extends('layout.buyer')
@section('content')
<div class="container" >
    <div class="row">
        <div class="col-md-12">
            <div class="login-section" >
                <p class="form-title">LOGIN</p>
                    @if (session('message'))
                        <div class="alert alert-warning">
                            {{ session('message') }}
                        </div>
                    @endif 
                 <!-- {{ session('message') }} -->
                <form action="{{route('buyer-login')}}" method="post">
                {{ csrf_field() }}
                <div class="form-group mt-4">
                    <label class="form-label">Phone Number</label>
                    <input type="number" autocomplete="off" class="form-control form-input" name="number" id="number" onkeypress="if(this.value.length==10) return false;" onkeydown="return event.keyCode !== 69">
                    @if ($errors->has('number'))
                    <span class="help-block errorText">
                    {{ $errors->first('number') }}
                    </span>
                    @endif
                </div>
                <div class="form-group mt-4">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control form-input" name="password" id="password">
                    @if ($errors->has('password'))
                    <span class="help-block errorText">
                    {{ $errors->first('password') }}
                    </span>
                    @endif
                    <button type="submit" class="btn mt-5 btn-primary blue-btn" style="width:100%;">Login</button>
                </div>
                </form>
                <div class="text-center">
                    <p class="para-footer">New to Trazoo ? <a href="{{route('registration')}}" class="action-link">Register Now</a></p>
                </div>
            </div> 
        </div>
    </div>
</div>
@endsection