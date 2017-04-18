{{--
    Displays products of all Breeder users
--}}

@extends('user.breeder.home')

@section('title')
    | Customers
@endsection

@section('pageId')
    id="page-breeder-view-customers"
@endsection

@section('breadcrumbTitle')
    Customers
@endsection

@section('breadcrumb')
    <a href="{{ route('home_path') }}" class="breadcrumb">Home</a>
    <a href="#!" class="breadcrumb">Customers</a>
@endsection

@section('content')
    <div id="map-container">
    <div id="map-canvas" style="height:80vh;"></div>
  </div>




@endsection

@section('customScript')

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBjcVoiwU44Pj1fdY8JyxjORa8RElQQlGY"></script>
    <script src="/js/markerclusterer.js"></script>
    <script src="/js/Mapster.js"></script>
    <script src="/js/map-options.js"></script>
    <script>
        (function(window, google, mapster){

            var options = mapster.MAP_OPTIONS;

            element = document.getElementById('map-canvas');

            var geocoder = new google.maps.Geocoder();
            map = new Mapster.create(element, options);

            var loadingtimeout;
            function geocode(opts){
                clearTimeout(loadingtimeout);
                $('.geocoding').show();
                geocoder.geocode({
                    address: opts.address
                }, function(results, status){
                    if(status === google.maps.GeocoderStatus.OK){
                        var result = results[0];
                        map.addMarker({
                            lat : result.geometry.location.lat(),
                            lng : result.geometry.location.lng(),
                            draggable : true,
                            content: opts.content,
                            icon: '/images/maps/customer.png',
                            //link: '/breeder/view-customer/'+opts.id
                        });
                        loadingtimeout = setTimeout(function(){
                            $('.geocoding').hide();
                        }, 500);

                    }else{
                        console.error(status);
                    }
                });
            }

            @foreach($customers as $customer)
                geocode({
                    address : '{{ $customer->address_province }}, Philippines',
                    content : '{{ $customer->users()->first()->name }}',
                    id : '{{ $customer->id }}'
                });
            @endforeach




        }(window, google, window.Mapster || (window.Mapster = {})))



    </script>

@endsection
