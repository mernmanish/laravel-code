@extends('layout.buyer')
@section('title', $var)
<style>
    .loader-product {
        margin-bottom: 5px;
        margin-top: 5px;
    }

</style>
@section('content')
<input type="hidden" name="nextPage" id="nextPage" value="{{$nextPage}}" />
<input type="hidden" name="hasMore" id="hasMore" value="{{$hasMore}}" />
<input type="hidden" name="cat_id" id="cat_id" value="{{$cat_ID}}">
<div class="mt-5" id="itemlist">
    @foreach($productData as $product)
    <div class="row" onclick="window.location = '{{url('product')}}/{!! base64_encode(Crypt::encryptString($product['id'])); !!}'">
        <div class="col-md-12 d-flex flex-row product-card">
            <div class="col-md-4" style="width: 30%;">
                <img class="" src="{{$currUrl}}{{$product['product_images']}}" alt="" style="height:120px; width: 100%;">
            </div>
            <div class="col-md-8" style="width: 70%;">
                <p class="product-card-title"><a href="{{url('product')}}/{!! base64_encode(Crypt::encryptString($product['id'])); !!}">{{$product['product_name']}}</a></p>
                <p class="product-card-seller"><a href="{{url('user-profile')}}/{!! base64_encode(Crypt::encryptString($product['user_id'])); !!}">{{$product['user_businessname']}}</a></p>
                <p class="product-card-moq">MOQ: {{$product['moq']}} {{$product['selling_format']}}</p>
                <p class="product-card-price-margin"><span class="product-card-price">₹{{$product['whole_sale_price']}}/pc</span> <span class="product-card-margin">Margin {{(int)$product['margin']}}%</span></p>
                <p class="product-card-variant">{{$product['variantStripText']}}</p>
            </div>
        </div>
    </div>
    @endforeach


    <!--  -->
</div>
<div class="row loader-product">
    <div class="col-md-12 text-center">
        <div class="spinner-grow spinner-grow-sm text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="spinner-grow spinner-grow-sm text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="spinner-grow spinner-grow-sm text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>
<!-- <script type="text/javascript">
    $(document).ready(function() {
        windowOnScroll();
    });

    function windowOnScroll() {

        console.log("Herer");
        $("#itemlist").height();
        $('.loader-product').height();
        console.log($('#itemlist')[0].scrollHeight);
    }
    function getMoreData(lastId) {
        $(window).off("scroll");
        $.ajax({
            url: '/loadMoreProducts'
            , type: "get"
            , beforeSend: function() {
                $('.loader-product').show();
            }
            , success: function(data) {
                setTimeout(function() {
                    $('.loader-product').hide();
                    $("#itemlist").append(data);
                    windowOnScroll();
                }, 1000);
            }
        });
    }

</script> -->
<script>
    var headerDynamicHome = document.querySelector(".header-dynamic-home");
    headerDynamicHome.style.display = "none";
    var headerDynamicHome = document.querySelector(".header-dynamic-product-listing");
    headerDynamicHome.style.display = "block";
    {{-- var headerDynamicUserProfile = document.querySelector(".header-dynamic-user-profile");
    headerDynamicUserProfile.style.display = "none"; --}}

         const observer = new IntersectionObserver((entries) => {
             entries.forEach((entry) => {
                 if (entry.isIntersecting) {
                     $.ajaxSetup({
                         headers: {
                             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                         }
                     });
                    //  var catId = $('#cat_id').val();
                    //  console.log();
                    if($('#hasMore').val()==true)
                    {
     	            var productData={"nextPage":$('#nextPage').val(),"catId":$('#cat_id').val()};
                     $.ajax({
                         type: 'POST',
                         url: '/loadMoreProducts',
                         dataType: 'json',
                         data: productData,
                         followRedirects: false,  
                         success: function(data) {
                            if(data.has_more==true)
                            {
                                $('#itemlist').append(data.output);
                                $('#nextPage').val(data.nextPage);
                            }
                            else{
                                $('.loader-product').hide();
                                return false;
                            }
                         },
                         error: function(xhr, status, error) {
                             console.log('error');
                             console.log(xhr.status);
                         }
                     });
                    }
                    else{
                        $('.loader-product').hide();
                        return false;
                    }
                 console.log('Div is in view');
                 } else {
                 console.log('Div is out of view');
                 }
             });
             });

    const target = document.querySelector('.loader-product');

    observer.observe(target);
    // $(function() {

    //     var stickyHeaderTop = $('.loader-product').offset().top - 1000;


    //     $('#id-center-col-desktop').scroll(function() {
    //         if ($('#id-center-col-desktop').scrollTop() > stickyHeaderTop) {
    //             console.log("call ajax");

    //         }
    //     });
    // });

</script>
@endsection
