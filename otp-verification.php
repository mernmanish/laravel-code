@extends('layout.buyer')
@section('content')
<div class="container" >
    <div class="row">
        <div class="col-md-12">
            <div class="login-section" >
                <p class="form-title text-center">REGISTER</p>
                   @if (session('message'))
                        <div class="alert alert-warning">
                            {{ session('message') }}
                        </div>
                    @endif 
                <form action="{{route('otpverification')}}" method="post">
                {{ csrf_field() }}
                    <div class="form-group mt-4 ">
                        <label class="form-label">OTP</label>
                        <input type="hidden" name="number" value="{{session('user_number')}}">
                        <input type="number" class="form-control form-input" name="otp" id="otp" onkeypress="if(this.value.length==4) return false;" onkeydown="return event.keyCode !== 69">
                        @if ($errors->has('otp'))
                        <span class="help-block errorText">
                        {{ $errors->first('otp') }}
                        </span>
                        @endif
                        <button type="submit" class="btn mt-2 btn-primary blue-btn" style="width:100%;">Complete Registration</button>
                    </div>
                </form>
            </div> 
        </div>
    </div>
</div>
@endsection