@extends('layout.buyer')
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="mt-4">
            <p class="form-title">Categories</p>
                <div class="row">
                @if(!empty($catData))
                @foreach($catData as $category)
                    <div class="col-md-3 col-3 cat-list text-center">
                        <img class="rounded" src="{{ $category['image_link'] }}" alt="">
                        @if($category['isSubCategories']==true)
                        <p class="mt-2" ><a href="{{url('shop')}}/{{$category['id']}}" style="color:black;"> {{ $category['name'] }}</a></p>
                        @else
                        <p class="mt-2" ><a href="{{url('products')}}/{{$category['id']}}" style="color:black;">{{ $category['name'] }}</a></p>
                        @endif
                    </div>
                @endforeach
                @endif
                @if(!empty($subcatData))
                    @foreach($subcatData as $subcategory)
                    <div class="col-md-3 col-3 cat-list text-center">
                    <img class="rounded" src="{{ $subcategory['image_link'] }}" alt="">
                        @if($subcategory['isSubCategories']==true)
                        <p class="mt-2" ><a href="{{url('shop')}}/{{$subcategory['id']}}" style="color:black;"> {{ $subcategory['name'] }}</a></p>
                        @else
                        <p class="mt-2" ><a href="{{url('products')}}/{{$subcategory['id']}}" style="color:black;">{{ $subcategory['name'] }}</a></p>
                        @endif
                    </div>
                    @endforeach
                @endif


                    <!--  -->
                </div>
           
            <!-- <div class="form-group mt-4 ">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control form-input" name="phone">
                    <button class="btn mt-2 btn-primary blue-btn" style="width:100%;">Request OTP</button>
                </div>
                <div class="text-center">
                    <p class="para-footer">New to Trazoo ? <a href="#" class="action-link">Register Now</a></p>
                </div> -->
        </div>
    </div>
</div>
@endsection
