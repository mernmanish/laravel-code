@extends('layout.buyer')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
{{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
<style>
    .swiper-main {
        width: 100%;
        height: auto;
    }

    .swiper-main .swiper-slide {
        text-align: center;
        font-size: 18px;
        background: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .swiper-main .swiper-slide img {
        display: block;
        width: 100%;
        height: 350px;
        object-fit: contain;
    }

    .swiper-pagination-bullets.swiper-pagination-horizontal {
        margin-top: 10px;
        top: auto !important;
        bottom: auto !important;
    }
    
    .swiper-full .swiper-wrapper {
        width: 100%;
        height:70%;
    }

    .product-image-gallery .swiper-slide {
        text-align: center;
        font-size: 18px;
        background: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .product-image-gallery .swiper-slide img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .product-thumb-gallery {
        height: 20%;
        box-sizing: border-box;
        padding: 10px 0;
    }

    .product-thumb-gallery .swiper-slide {
        width: 100px;
        height: 100px;
        opacity: 0.4;
    }

    .product-thumb-gallery .swiper-slide-thumb-active {
        text-align: -webkit-center;
    }

    .product-thumb-gallery .swiper-slide-thumb-active {
        opacity: 1;
        border: 2px solid rgb(0, 0, 0);
    }


    .product-thumb-gallery .swiper-slide img {
        display: block;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

</style>

@section('content')

<div class="product-image-view mt-3">
    <div id="close-icon" class="pull-right" style="font-size: 25px;">
        <i class="fas fa-times"></i>
    </div>
    <div class="swiper-full product-image-gallery">
        <div class="swiper-wrapper">
            @foreach($product['product']['images'] as $images)
            <div class="swiper-slide">
                <img src="{{$currUrl}}/{{$images}}" />
            </div>
            @endforeach
            <!-- <div class="swiper-slide">
                <img src="https://seller.trazoo.in/images/2/1657562902600.png" />
            </div>
            <div class="swiper-slide">
                <img src="https://seller.trazoo.in/images/2/1681902869165.png" />
            </div> -->
        </div>
    </div>

    <div thumbsSlider="" class="swiper-full-thumb product-thumb-gallery">
        <div class="swiper-wrapper">
        @foreach($product['product']['images'] as $images)
            <div class="swiper-slide">
                <img src="{{$currUrl}}/{{$images}}" />
            </div>
        @endforeach
            <!-- <div class="swiper-slide">
                <img src="https://seller.trazoo.in/images/2/1657562902600.png" />
            </div>
            <div class="swiper-slide">
                <img src="https://seller.trazoo.in/images/2/1681902869165.png" />
            </div> -->
        </div>
    </div>
</div>

<div class="product-info-view">
    {{-- <div class="row"> --}}
    <div id="product-images" class="card" style="border:none;">

        <div class="card-body mt-2" style="padding:0px;">
            <div class="swiper-main product-page-swiper">
                <div class="swiper-wrapper">
                @foreach($product['product']['images'] as $images)
                    <div class="swiper-slide">
                        <img id="product-image" class="product-image" src="{{$currUrl}}/{{$images}}" alt="">
                    </div>
                @endforeach
                    <!-- <div class="swiper-slide">
                        <img id="product-image" class="product-image" src="https://seller.trazoo.in/images/2/1657562902600.png" alt="">
                    </div>
                    <div class="swiper-slide">
                        <img id="product-image" class="product-image" src="https://seller.trazoo.in/images/2/1681902869165.png" alt="">
                    </div> -->
                </div>
            </div>
            <div id="swiper-pagination" class="swiper-pagination "></div>


            <div id="product-view-info" class="product-view-info mt-5">
                <p class="product-view-title">{{$product['product']['product_name']}}</p>
                <p class="product-view-code">Product Code: @if(!empty($product['product']['product_code'])) {{$product['product']['product_code']}} @else N/A @endif </p>
                <p class="product-del-price">₹<del>{{$product['product']['mrp']}} / {{$product['product']['selling_format']}} </del></p>
                <p class="product-view-price">₹{{$product['product']['whole_sale_price']}}/{{$product['product']['selling_format']}} <span class="product-view-pcs">({{$product['product']['no_of_prices']}} PCS)</span> <span class="pull-right product-view-margin">Margin {{(int)$product['product']['margin']}}%</span></p>
                <p class="product-view-tax">(₹{{$product['product']['whole_sale_price']/(1+($product['product']['gst']/100))}} + {{$product['product']['gst']}}% GST) </p>
            </div>
            <div id="product-view-moq" class="product-view-moq">
                <p>MOQ <span class="pull-right">{{$product['product']['moq']}} {{$product['product']['selling_format']}}</span></p>
            </div>
            <div id="product-view-stock" class="product-view-stock">
                <p>In Stock <span class="pull-right">{{$product['product']['in_stock']}}</span></p>
            </div>
        </div>
    </div>
    {{-- </div> --}}

    {{-- <div class="row"> --}}
    <div id="seller-info" class="card mt-5" style="border:none;">
        <div class="card-body" style="padding:0px;">
            <div class="seller-title">
                <p>Seller Info <a href="{{url('user-profile')}}/{{$product['sellerInfo']['id']}}" class="pull-right">View <i class="fa fa-angle-right" aria-hidden="true"></i></a></p>
            </div>
            <div class="row mt-2">
                <div class="col-md-3 col-3">
                    <img src="{{$currUrl}}/{{$product['sellerInfo']['business_photo']}}" alt="" style="width:100%; height:55px;">
                </div>
                <div class="col-md-9 col-9">
                    <p class="seller-name mt-2">{{$product['sellerInfo']['fullname']}}</p>
                    <p class="seller-compay-details">{{$product['sellerInfo']['businessname']}} • {{$product['sellerInfo']['city']}}</p>
                </div>
            </div>
            <div class="seller-chat">
                <a href="#" class="btn btn-light seller-chat-box"><i class="fa fa-comment-o" aria-hidden="true"></i> Chat with seller Now</a>
            </div>
        </div>
    </div>
    {{-- </div> --}}

    {{-- <div class="row"> --}}
    <div id="product-description" class="card mt-5 mb-5" style="border:none;">
        <div class="card-body" style="padding:0px;">
            <div class="seller-title">
                <p>Product Details</p>
            </div>
            <div class="product-view-moq">
                <p class="ml-2">{!! html_entity_decode($product['product']['description']) !!}</p>
                <!-- <p>{{$product['product']['description']}}</p> -->
            </div>
        </div>
    </div>
    {{-- </div> --}}

    <div class="row" id="buy-button">
        <div class="col-md-12 d-flex flex-row blue-button-fixed-bottom pt-2 pb-2">
            <a class="btn w-100">Buy Now</a>
        </div>
    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script>
    var headerRow = document.querySelector(".header-row");
    var afterHeaderRow = document.querySelector(".after-header-row");
    var headerDynamicHome = document.querySelector(".header-dynamic-home");
    var headerDynamicProductView = document.querySelector(".header-dynamic-product-view");

    var productImages = document.querySelectorAll('.product-image');
    var productImageView = document.querySelector(".product-image-view");
    var productInfoView = document.querySelector(".product-info-view");
   
    headerDynamicHome.style.display = "none";
    headerDynamicProductView.style.display = "block";
    productImageView.style.display = "none";
    productInfoView.style.display = "block";


    productImages.forEach(function(productImage, index) {

        productImage.addEventListener('click', function() {
                productImageView.style.display = "block";
                closeIcon.style.display = 'block';
                productInfoView.style.display = "none";
                headerRow.style.display = "none";
                afterHeaderRow.classList.remove("mt-5");

        })
    })




    var swiperProductPage = new Swiper(".product-page-swiper", {
        pagination: {
            el: ".swiper-pagination"
        , }
    , });

    var swiperFullThumb = new Swiper(".product-thumb-gallery", {
        spaceBetween: 10
        , slidesPerView: 4
        , freeMode: true
        , watchSlidesProgress: true
    , });

    var swiperFull = new Swiper(".product-image-gallery", {
        spaceBetween: 10
        , navigation: {
            nextEl: ".swiper-button-next"
            , prevEl: ".swiper-button-prev"
        , }
        , thumbs: {
            swiper: swiperFullThumb
        , }
    , });


    // Onclick the close button to close
    const closeIcon = document.getElementById('close-icon');
    closeIcon.addEventListener('click', function() {
        closeIcon.style.display = 'none';
        productImageView.style.display = 'none';
        productInfoView.style.display = 'block';
        headerDynamicProductView.style.display = "block";
        headerRow.style.display = "block";
        afterHeaderRow.classList.add("mt-5");


    })

</script>
@endsection
