@extends('vendor.includes.base')
<style>
    .container {
display: block;
position: relative;
padding-left: 35px;
margin-bottom: 12px;
cursor: pointer;
font-size: 22px;
-webkit-user-select: none;
-moz-user-select: none;
-ms-user-select: none;
user-select: none;
}
.buttons {
float: left;
margin: 0 5px 0 0;
width: 100%;
background-color: #b8cbc285;
border: 1px solid #beb9b9;
height: 40px;
position: relative;
}

.buttons label,
.buttons input {
display: block;
position: absolute;
top: 0;
left: 0;
right: 0;
bottom: 0;
}

.buttons input[type="radio"] {
opacity: 0.011;
z-index: 100;
}

.buttons input[type="radio"]:checked + label {
background: #3bb77e;
/* border-radius: 4px; */
color: white;
}

.buttons label {
cursor: pointer;
z-index: 90;
line-height: 1.0em;
font-size: 15px;
}

</style>
@section('content')
<section class="container-fluid">
    <div class="row d-flex justify-content-center align-items-center">
        <div class="col-md-7 mt-5">
            <div class="card-body">
                {{-- <a href="../../dashboard/">
                    <img src="{{ asset('images/favicon.png') }}" class="img-fluid logo-img"
                        alt="img4">
                </a> --}}
                {{-- <h2 class="mb-2 text-center">Registration Details</h2> --}}
                {{-- <p class="text-center">Sign in to stay connected.</p> --}}

                <form method="POST" action="{{route('vendor/addVendorPayment')}}"
                    enctype="multipart/form-data" class="d-flex align-items-center justify-content-center">
                    @csrf
                    <div class="row bg-white card w-75" style="height: 90%;">
                        <div class="card-body d-flex">

                            <div class="col-12 form-group d-flex flex-column align-items-center justify-content-center">
                                <div class="step-1 w-100" style="display: block;">
                                    <h4 class="text-center"> Merchant Onboarding
                                    </h4>
                                    <hr>
                                    <input type="hidden" name="bo_id" value="{{auth()->user()->payment->id ?? ''}}">
                                    <label for="vpassword" class="form-label">First Name</label>
                                    <input type="text" class="form-control" placeholder="Contact First Name"  name="first_name" value="{{auth()->user()->payment->first_name ?? auth()->user()->first_name}}">
                                    <label for="vpassword" class="form-label">Last Name</label>
                                    <input type="text" class="form-control"  placeholder="Contact Last Name"  name="last_name" value="{{auth()->user()->payment->last_name ?? auth()->user()->last_name}}">
                                    <label class="form-label">Business Email</label>
                                    <input type="email" class="form-control"  placeholder="Business Email"  name="business_email" value="{{auth()->user()->payment->email ?? auth()->user()->email}}" >
                                    <label class="form-label">Business Phone</label>
                                    <input type="text" class="form-control"  placeholder="Business Phone"  name="business_phone" value="{{auth()->user()->payment->business_phone ?? auth()->user()->phone}}" >
                                    <label class="form-label">Merchant Legal Name</label>
                                    <input type="text" class="form-control" placeholder="Merchant Legal Name" name="merchant_legal_name" value="{{ auth()->user()->payment->merchant_legal_name ?? "  "}}">
                                    <label class="form-label">Merchant DBA</label>
                                    <input type="text" class="form-control" placeholder="Merchant DBA" name="merchant_dba" value="{{ auth()->user()->payment->merchant_dba ?? "   "}}">
                                    <label class="form-label">Max Transcation Amount</label>
                                    <input type="text" class="form-control" placeholder="Max Transcation Amount" name="max_transaction_amount" value="{{ auth()->user()->payment->max_transaction_amount ?? "  "}}">
                                </div>
                                <div class="step-2 w-100" style="display: none;" >
                                <label class="form-label">Bank Account</label>
                                <input type="text" class="form-control" minlength="8"  placeholder="Bank Account" name="bank_account" value="{{ auth()->user()->payment->bank_account ?? ""}}">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control" minlength="8" maxlength="16" class="numonly" placeholder="Account Number" name="business_account_no" value="{{ auth()->user()->payment->business_account_no ?? ""}}">
                                <label class="form-label">Bank Code</label>
                                <input type="text" class="form-control"   placeholder="Bank Code" name="bank_code" value="{{ auth()->user()->payment->bank_code ?? ""}}">
                                <div class="row">
                                    <label class="form-label">Ownership Type</label>
                                    <div class="col-6">
                                        <div class="buttons">
                                            <input type="radio" id="a25" name="ownership_type" value="Corporation" @php if(!empty(auth()->user()->payment->ownership_type)){
                                                if(auth()->user()->payment->ownership_type=="Corporation"){ echo "checked"; } else { ""; }
                                            } @endphp />
                                            <label class="btn btn-default" for="a25"><button class="btn btn-dark btn-sm" style="adding: 0rem 1rem;
                                                float: left;line-height: 0.99;background-color: #4c5762;">A</button> Corporation</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="buttons">
                                            <input type="radio" id="a26"  name="ownership_type" value="Partnership" @php if(!empty(auth()->user()->payment->ownership_type)){
                                                if(auth()->user()->payment->ownership_type=="Partnership"){ echo "checked"; } else { ""; }
                                            } @endphp/>
                                            <label class="btn btn-default" for="a26"><button class="btn btn-dark btn-sm" style="adding: 0rem 1rem;
                                                float: left; line-height: 0.99;background-color: #4c5762;">B</button> Partnership</label>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="buttons">
                                            <input type="radio" id="a27" name="ownership_type" value="LLC" @php if(!empty(auth()->user()->payment->ownership_type)){
                                                if(auth()->user()->payment->ownership_type=="LLC"){ echo "checked"; } else { ""; }
                                            } @endphp />
                                            <label class="btn btn-default" for="a27"><button class="btn btn-dark btn-sm" style="adding: 0rem 1rem;
                                                float: left;line-height: 0.99;background-color: #4c5762;">C</button> LLC</label>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="buttons">
                                            <input type="radio" id="a28" name="ownership_type" value="Publicly Traded" @php if(!empty(auth()->user()->payment->ownership_type)){
                                                if(auth()->user()->payment->ownership_type=="Publicly Traded"){ echo "checked"; } else { ""; }
                                            } @endphp />
                                            <label class="btn btn-default" for="a28"><button class="btn btn-dark btn-sm" style="adding: 0rem 1rem;
                                                float: left;line-height: 0.99;background-color: #4c5762;">D</button> Publicly Traded</label>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="buttons">
                                            <input type="radio" id="a29" name="ownership_type" value="Government" @php if(!empty(auth()->user()->payment->ownership_type)){
                                                if(auth()->user()->payment->ownership_type=="Government"){ echo "checked"; } else { ""; }
                                            } @endphp />
                                            <label class="btn btn-default" for="a29"><button class="btn btn-dark btn-sm" style="adding: 0rem 1rem;
                                                float: left;line-height: 0.99;background-color: #4c5762;">E</button> Government</label>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="buttons">
                                            <input type="radio" id="a30" name="ownership_type" value="Sole Proprietor" @php if(!empty(auth()->user()->payment->ownership_type)){
                                                if(auth()->user()->payment->ownership_type=="Sole Proprietor"){ echo "checked"; } else { ""; }
                                            } @endphp />
                                            <label class="btn btn-default" for="a30"><button class="btn btn-dark btn-sm" style="adding: 0rem 1rem;
                                                float: left;line-height: 0.99;background-color: #4c5762;">F</button> Sole Proprietor</label>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="buttons">
                                            <input type="radio" id="a31" name="ownership_type" value="Non Profilt" @php if(!empty(auth()->user()->payment->ownership_type)){
                                                if(auth()->user()->payment->ownership_type=="Non Profilt"){ echo "checked"; } else { ""; }
                                            } @endphp />
                                            <label class="btn btn-default" for="a31"><button class="btn btn-dark btn-sm" style="adding: 0rem 1rem;
                                                float: left;line-height: 0.99;background-color: #4c5762;">G</button> Non Profilt</label>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                <div class="step-3 w-100" style="display: none;">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="owner_first_name" class="form-label">Federal Tax ID</label>
                                            {{-- <input type="text" name="owner_first_name"
                                                class="form-control form-control-sm" id="owner_first_name"
                                                aria-describedby="Full Name"
                                                placeholder="First Name" required> --}}
                                                <input type="text" class="form-control " placeholder="Federal Tax ID"  name="federal_tax_id" value="{{ auth()->user()->payment->federal_tax_id ?? "" }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="oemail" class="form-label">Incorporation Date</label>
                                            <input type="date" class="form-control" placeholder="Incorporation Date" name="incorporation_date" value="{{ auth()->user()->payment->incorporation_date ?? '' }}">
                                            {{-- <input type="text" name="owner_last_name"
                                                class="form-control form-control-sm" id="owner_last_name"
                                                aria-describedby="Last Name"
                                                placeholder="Last Name" required> --}}
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="ocontact" class="form-label">Business City</label>
                                            <input type="text" class="form-control" placeholder="Business City" name="business_city" value="{{ auth()->user()->payment->business_city ?? "" }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="ocontact" class="form-label">Business Country</label>
                                            <input type="text" class="form-control" placeholder="Business Country" name="business_country" value="{{ auth()->user()->payment->business_country ?? "" }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="ocontact" class="form-label">Business Region</label>
                                            <input type="text" class="form-control" placeholder="Business Region" name="region" value="{{ auth()->user()->payment->region ?? "" }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="ocontact" class="form-label">Business Address Line 1</label>
                                            <input type="text" class="form-control" placeholder="Business Address Line 1" name="business_address_line1" value="{{ auth()->user()->payment->business_address_line1 ?? "" }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="ocontact" class="form-label">Business Address Line 2</label>
                                            <input type="text" class="form-control" placeholder="Business Address Line 2" name="business_address_line2" value="{{ auth()->user()->payment->business_address_line2 ?? "" }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="vpassword" class="form-label">Business Zip</label>
                                            <input type="text" class="form-control" placeholder="Business Zip"  name="business_zip" value="{{ auth()->user()->payment->business_zip ?? "" }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="text-center">
                                        <h5 class="mb-2 mt-2 text-center" >Does your business support SNAP EBT?  </h5>
                                        <div class="form-check form-check-inline text-center">
                                            <input class="form-check-input sbt_confirm" name="is_snap_ebt" type="radio" id="yes" value="yes"  required>
                                            <label class="form-check-label" for="yes">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline text-center">
                                            <input class="form-check-input sbt_confirm" name="is_snap_ebt" type="radio" id="no" value="no" required>
                                            <label class="form-check-label" for="no">No</label>
                                        </div>
                                    </div>
                                    @if(!empty(auth()->user()->payment->fns_no))
                                    <div class="confirm_selection">
                                        <label  for="vpassword" class="form-label">FNS Number</label>
                                        <input class="form-control" type="text" placeholder="FNS Number" maxlength="7" minlength="7" name="fns_no" value="{{ auth()->user()->payment->fns_no ?? "" }}">
                                    </div>
                                    @else
                                    <div class="confirm_selection" style="display: none">
                                        <label  for="vpassword" class="form-label">FNS Number</label>
                                        <input class="form-control" type="text" placeholder="FNS Number" maxlength="7" minlength="7" name="fns_no" >
                                    </div>
                                    @endif
                                    </div>
                                </div>



                                <div class="d-flex justify-content-center mt-2">
                                    <button type="button" class="btn btn-sm btn-primary prevBtn" style="margin-right: 10px;display:none;">Previous</button>

                                    <button type="button"  class="btn btn-sm btn-primary subBtn">Next</button>
                                </div>
                            </div>


                        </div>


                        {{-- <div class="col-lg-12">
                                <div class="form-group">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control form-control-sm" id="password"
                                        aria-describedby="password" placeholder=" ">
                                </div>
                            </div> --}}

                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<script>
    $('.sbt_confirm').on('click',function(){
        var value = $(this).val();
        if(value==="yes")
        {
            $('.confirm_selection').show();
        }
        else{
            $('.confirm_selection').hide();
        }
    });
</script>
<script>
    let currentStep = 1;

    $('.subBtn').on('click',()=>{
        currentStep += 1;
        if(currentStep == 1){
            $('.step-1').show();
            $('.step-2').hide();
            $('.step-3').hide();
        }
        if(currentStep == 2){
            $('.prevBtn').show().attr('type','button');
            $('.step-1').hide();
            $('.step-2').show();
            $('.step-3').hide();
        }
        if(currentStep == 3){
            $('.step-1').hide();
            $('.step-2').hide();
            $('.step-3').show();
            $('.subBtn').text('Save').attr('type','submit').attr('name','finalsubmit');
        }
        // if(currentStep == 4){
        //     $('form').submit();
        // }
    })

    $('.prevBtn').on('click',()=>{
        currentStep -= 1;
        if(currentStep == 1){
            $('.step-1').show();
            $('.step-2').hide();
            $('.step-3').hide();
            $('.prevBtn').hide().attr('type','button');
        }
        if(currentStep == 2){
            $('.step-1').hide();
            $('.step-2').show();
            $('.step-3').hide();
            $('.subBtn').text('Next').attr('type','button');
            $('.sss').removeAttr('required');
        }
        if(currentStep == 3){
            $('.step-1').hide();
            $('.step-2').hide();
            $('.step-3').show();
        }

    })
//     $('button[name="finalsubmit"]').on('click',function(){
//      alert('hello');
//    });
</script>
@endsection
