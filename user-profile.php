@extends('layout.buyer')
<style>
    #product-listing-header {
        width: 100%;
        height: 10px;
    }

    #sticky-products {
        display: none;
        height: 10px;
    }

    #user-info-section {
        margin-bottom: 15px;
    }

    #content {
        margin-top: 20px;
    }

    .profile-chat-button {
        padding: 0px 15px 0px 15px !important;
        color: #2350f5 !important;
        font-weight: 600 !important;
    }

    a {
        color: #CCC;
    }

    th,
    td {
        border: none !important;
        font-size: 13px !important;
        padding: 0.4rem !important;
    }

    product-show-more {
        font-weight: 600 !important;
        font-size: 15px !important;
    }

    .radio-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
        max-height: 4.25rem;
    }

    .radio-wrapper .radio-item {
        margin-right: 0.25rem;
    }

    .radio-wrapper .radio-item:last-child {
        margin-right: 0;
    }

    .radio-item span {
        display: block;
        background: #8b9bce;
        padding: .5rem;
        line-height: 1rem;
        font-family: "SFProText";
        font-size: 0.85rem;
        font-weight: 400;
        cursor: pointer;
        color: fff;
        white-space: nowrap;
        border-radius: 2px;
        
    }

    .radio-item input[type=radio]:checked+span {
        background: #2355f4;
        color: #ffffff;
    }

    .input-group-text {
        background-color: #fff !important;
    }

    @media screen and (max-width: 768px) {
        #product-listing-header {
        min-width: 92%;
    }
}

</style>
@section('content')
<div class="card mt-5 user-info-section" style="border:none;">
    <div class="card-body" style="padding:0px;">

        <div class="row mt-2">
            <div class="col-md-3 col-3">
                <img src="{{$cURI}}/{{$profile['userInfo']['business_photo']}}" alt="" style="width:100%; height:55px;">
            </div>
            <div class="col-md-9 col-9">
                <p class="seller-name mt-2">{{$profile['userInfo']['fullname']}} <span class="pull-right" style="border:1px solid #cdbbbb;"><a href="#" class="profile-chat-button btn-sm btn btn-light">Chat</a></span></p>
                <p class="seller-compay-details">{{$profile['userInfo']['businessname']}} • {{$profile['userInfo']['city']}}</p>
            </div>
            <div class="col-md-12">
                <table class="table" style="border:none;">
                    <tr>
                        <th>Business</th>
                        <td>{{$profile['userInfo']['businesstype']}}</td>
                        <th>Active</th>
                        <td>{{$profile['userInfo']['active_score']}}</td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>{{$profile['userInfo']['city']}}</td>
                        <th>Trade Rep</th>
                        <td>{{$profile['userInfo']['trade_score']}}</td>
                    </tr>
                    <tr>
                        <th>Industry</th>
                        <td>{{$profile['userInfo']['industry']}}</td>
                    </tr>
                    <tr>
                        <th>GST</th>
                        <td>{{$profile['userInfo']['GST']}}</td>
                    </tr>
                </table>
                <div>
                    <p class="pl-2"><b>Rating</b> <a class="pull-right product-show-more" href="#" style="font-weight:600;font-size:13px; text-decoration:none;"><i class="fa fa-chevron-up" aria-hidden="true"></i> View</a></p>
                    <div class="pl-1 rating-view" style="display:none;">
                        <table class="table">
                            <tr>
                                <th width="30">Product</th>
                                @if($profile['userInfo']['totalPro_R']==0)
                                <td>--</td>
                                @else
                                <td>@php for($i=1; $i < $profile['userInfo']['totalPro_R']; $i++ ) {    @endphp <i class="fa fa-star text-warning" aria-hidden="true"></i>@php } @endphp</td>
                                @endif
                            </tr>
                            <tr>
                                <th width="30">Service</th>
                                @if($profile['userInfo']['totalSR']==0)
                                <td>--</td>
                                @else
                                <td>@php for($i=1; $i < $profile['userInfo']['totalSR']; $i++ ) {    @endphp <i class="fa fa-star text-warning" aria-hidden="true"></i>@php } @endphp</td>
                                @endif
                            </tr>
                            <tr>
                                <th width="30">Packaging</th>
                                @if($profile['userInfo']['totalPR']==0)
                                <td>--</td>
                                @else
                                <td>@php for($i=1; $i < $profile['userInfo']['totalPR']; $i++ ) { @endphp <i class="fa fa-star text-warning" aria-hidden="true"></i>@php } @endphp</td>
                                @endif
                            </tr>
                            <tr>
                                <th width="30">Margin</th>
                                @if($profile['userInfo']['totalMR']==0)
                                <td>--</td>
                                @else
                                <td>@php for($i=1; $i < $profile['userInfo']['totalMR']; $i++ ) { @endphp <i class="fa fa-star text-warning" aria-hidden="true"></i>@php } @endphp</td>
                                @endif
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="seller-chat">
            <a href="#" class="btn btn-light seller-chat-box">Add Connection</a>
        </div>
    </div>
</div>

<div id="product-listing-header" class="mt-2 mb-5">
    <div class="card" style="border:none; margin-right:-15px;margin-left:-15px">
          <div class="card-content" style="margin-right:15px;margin-left:15px">
            <div class="input-group search-bar mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-search" aria-hidden="true"></i></span>
                </div>
                <input type="text" class="form-control form-input js_searchByProduct" placeholder="Search from all Products" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <div class="radio-wrapper d-flex flex-row">
                @php $i=1; @endphp
                @foreach($product['categories'] as $category)
                <label class="radio-item text-center"><input type="radio" value="{{$category['category_id']}}"   name="seller_category" class="d-none js_searchByCategory" id="{{$category['category_id']}}" @if($i==1) checked @endif><span for="{{$category['category_id']}}" >{{$category['category_name']}}</span></label>
                @php $i++; @endphp
                @endforeach
            </div>
         </div>
    </div>

</div>

<div id="sticky-products"></div>

<!-- <div class="product-list-title myHeader mt-5">
                  <p class="category-title mt-4">Tshirt</p>
                </div> -->
<!-- <div class="row">
                    <div class="col-md-12 d-flex flex-row product-list-title mt-0" >
                        <p class="category-title mt-3">Tshirt</p>
                    </div>
                </div> -->
    <div class="row filterProductData" style="margin-top: 6rem!important;">
   
    @foreach($product['products'] as $product)
        <div class="product-list" style="width:100%;" data-product-title="{{$product['product_name']}}" data-category="{{$product['category_id']}}">
            <div class="col-md-12 d-flex flex-row product-card">
                    <div class="col-md-4" style="width: 30%;">
                        <img class="" src="{{$cURI}}{{$product['product_images']}}" alt="" style="height:110px; width: 100%;">
                    </div>

                    <div class="col-md-8 mt-2" style="width: 70%;" >
                        <p class="product-card-title"><a href="{{url('product')}}/{{$product['id']}}">{{$product['product_name']}}</a></p>
                        <p class="product-card-moq">MOQ: {{$product['moq']}} {{$product['selling_format']}}</p>
                        <p class="product-card-price-margin"><span class="product-card-price">₹{{$product['whole_sale_price']}}/pc</span> <span class="product-card-margin">Margin {{(int)$product['margin']}}%</span></p>
                        <p class="product-card-variant">{{$product['variantStripText']}}</p>
                    </div>
            </div>
        </div>
    @endforeach
    </div>
    <script>
        // function searchProductItem(keys,user_id)
        // {
        //     $.ajaxSetup({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //             }
        //         });
        //     var userData={"keys":keys,"user_id":user_id};
		//     $.ajax({
		//         type: 'POST',
		//         url: '{{ route('searchProductByCategory') }}',
		//         dataType: 'json',
		//         data: userData,
        //         cache: false,
        //         headers: {
        //             Accept: "application/json"
        //         },
        //         followRedirects: true,
        //         success: function(data) {
        //             console.log(data);
        //             $('.filterProductData').html(data.output);
        //         },
        //         error: function(xhr) {
        //             Console. log (xhr. status );
        //         }
		//     });
        // }
    $(document).ready(function(){
        $(document).on('keyup','.js_searchByProduct',function(){
            var search_keys = $(this).val().toLowerCase();
            if(search_keys==0)
            {
                $('.product-list').removeClass('d-none');
            }
            else {
                $('.product-list').each(function(){
                    var product_title = $(this).data('product-title').toLowerCase();
                        if(product_title.search(search_keys) !=-1){
                            $(this).removeClass('d-none');
                        }else{
                            $(this).addClass('d-none');
                        }
                });
            }
        });
    });
    </script>
    <script type="text/javascript">
        $(document).ready(function(){
            $(document).on('click','.js_searchByCategory',function(){
                var category_id = $(this).val();
                if(category_id==0)
                {
                    $('.product-list').removeClass('d-none');
                }
                else{
                    $('.product-list').each(function(){
                        console.log($(this).data('category'));
                        if($(this).data('category')==category_id){
                            $(this).removeClass('d-none');
                        }else{
                            $(this).addClass('d-none');
                        }
                    });
                }
                // console.log($(this).val());
            });
        });
        //  function searchByCategory(category_name)
        //  {
        //     console.log(category_name);
        //  }
        // function getProductBySellerCategory(category_id,user_id)
        // {
        //     $.ajaxSetup({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //             }
        //         });
        //     var userData={"category_id":category_id,"user_id":user_id};
		//     $.ajax({
		//         type: 'POST',
		//         url: '{{ route('filterProductByCategory') }}',
		//         dataType: 'JSON',
		//         data: userData,
        //         cache: false,
        //         headers: {
        //             Accept: "application/json"
        //         },
        //         followRedirects: true,
        //         success: function(data) {
        //             $('.filterProductData').html(data.output);
        //         },
        //         error: function(xhr) {
        //             Console. log (xhr. status );
        //         }
		//     });
        // }
    </script>

<!--  -->


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.8.3/jquery.min.js" integrity="sha512-J9QfbPuFlqGD2CYVCa6zn8/7PEgZnGpM5qtFOBZgwujjDnG5w5Fjx46YzqvIh/ORstcj7luStvvIHkisQi5SKw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    var headerDynamicHome = document.querySelector(".header-dynamic-home");
    headerDynamicHome.style.display = "none";
    var headerDynamicHome = document.querySelector(".header-dynamic-product-listing");
    headerDynamicHome.style.display = "block";
    var navbarBrandElement = headerDynamicHome.querySelector(".navbar-brand");
    navbarBrandElement.textContent = "Seller Catalogue";

    $(function() {
        console.log("function");

        var stickyHeaderTop = $('#product-listing-header').offset().top;
        console.log(stickyHeaderTop);

        $('#id-center-col-desktop').scroll(function() {
            console.log("scrollfunction");
            if ($('#id-center-col-desktop').scrollTop() > stickyHeaderTop) {
                console.log("scrolltop" + $('#id-center-col-desktop').scrollTop());
                $('#product-listing-header').css({
                    position: 'fixed'
                    , top: '43px'
                    , zIndex: 9999
                    , width: '31%'
                });
                $('#sticky-products').css('display', 'block');
            } else {
                console.log("static");
                $('#product-listing-header').css({
                    position: 'static'
                    , top: '0px'
                    , width: '100%'
                });
                $('#sticky-products').css('display', 'none');
            }
        });

    });

</script>
<script>
    $(document).ready(function() {
        $('.product-show-more').on('click', function() {
            $('.rating-view').slideToggle("slow");
        });
    });

</script>
@endsection
