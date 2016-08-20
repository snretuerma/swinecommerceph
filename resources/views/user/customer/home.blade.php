{{--
    Displays Home page of Customer User
--}}

@extends('layouts.default')

@section('title')
    | Customer
@endsection

@section('pageId')
    id="page-customer-home"
@endsection

@section('breadcrumbTitle')
    Home
@endsection

@section('navbarHead')
    <li><a href="{{ route('products.view') }}"> Products </a></li>
    <li><a href="{{ route('home_path') }}"> <i class="material-icons">message</i></a></li>
    @if(!Auth::user()->update_profile)
        <li><a href="{{ route('view.cart') }}" id="cart-icon" class="dropdown-button" data-beloworigin="true" data-hover="true" data-alignment="right" data-activates="cart-dropdown">
                <i class="material-icons">shopping_cart</i>
                <span></span>
            </a>
            <ul id="cart-dropdown" class="dropdown-content collection">
                <div id="preloader-circular" class="row">
                    <div class="center-align">
                        <div class="preloader-wrapper small active">
                            <div class="spinner-layer spinner-blue-only">
                                <div class="circle-clipper left">
                                    <div class="circle"></div>
                                </div>
                                <div class="gap-patch">
                                    <div class="circle"></div>
                                </div>
                                <div class="circle-clipper right">
                                    <div class="circle"></div>
                                </div>
                            </div>

                            {{-- <div class="spinner-layer spinner-red">
                                <div class="circle-clipper left">
                                    <div class="circle"></div>
                                </div><div class="gap-patch">
                                    <div class="circle"></div>
                                </div><div class="circle-clipper right">
                                    <div class="circle"></div>
                                </div>
                            </div>

                            <div class="spinner-layer spinner-yellow">
                                <div class="circle-clipper left">
                                    <div class="circle"></div>
                                </div><div class="gap-patch">
                                    <div class="circle"></div>
                                </div><div class="circle-clipper right">
                                    <div class="circle"></div>
                                </div>
                            </div>

                            <div class="spinner-layer spinner-green">
                                <div class="circle-clipper left">
                                    <div class="circle"></div>
                                </div><div class="gap-patch">
                                    <div class="circle"></div>
                                </div><div class="circle-clipper right">
                                    <div class="circle"></div>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
                <li>
                    <ul id="item-container" class="collection">
                    </ul>
                </li>

                <li>
                    <a href="{{ route('view.cart') }}" class="left">Go to Cart</a>
                    <a href="{{ route('view.cart') }}" class="right">Request items</a>
                </li>
            </ul>
        </li>
    @endif
@endsection

@section('navbarDropdown')
    <li><a href="{{ route('customer.edit') }}"> <i class="material-icons left">people</i> Update Profile</a></li>
    <li class="divider"></li>
    <li><a href="{{ route('view.cart') }}"> <i class="material-icons left">shopping_cart</i> Swine Cart </a> </li>
@endsection

@section('static')
    <div class="fixed-action-btn" style="bottom: 30px; right: 24px;">
      <a id="back-to-top" class="btn-floating btn-large red tooltipped" style="display:none;" data-position="left" data-delay="50" data-tooltip="Back To Top">
        <i class="material-icons">keyboard_arrow_up</i>
      </a>
    </div>
@endsection

@section('content')
    <div class="row">
    </div>

    {{-- Search bar --}}
    <nav id="search-container">
        <div id="search-field" class="nav-wrapper white">
            <form>
                <div class="input-field">
                    <input id="search" type="search" placeholder="Search for a product" required>
                    <label for="search"><i class="material-icons teal-text">search</i></label>
                    <i class="material-icons">close</i>
                </div>
            </form>
        </div>
    </nav>

    <div class="row">
    </div>

    {{-- Slider --}}
    <div class="slider home-slider">
        <ul class="slides">
          <li>
            <img src="/images/demo/HP1.jpg">
            <div class="caption center-align">
              <h3>Efficiency</h3>
              <h5 class="light grey-text text-lighten-3">Through the internet, the
system aims for faster and
hassle-free transaction between
consumers and retailers.</h5>
            </div>
          </li>
          <li>
            <img src="/images/demo/HP2.jpg">
            <div class="caption left-align">
              <h3>Security</h3>
              <h5 class="light grey-text text-lighten-3">security and legitimacy of
both customers and
breeders is ensured
through establishing a set
of criteria/qualifications.</h5>
            </div>
          </li>
          <li>
            <img src="/images/demo/HP3.jpg">
            <div class="caption right-align">
              <h3>Variety</h3>
              <h5 class="light grey-text text-lighten-3">security and legitimacy of both customers and
breeders is ensured through establishing a set
of criteria/qualifications.</h5>
            </div>
          </li>
          <li>
            <img src="/images/demo/HP4.jpg">
            <div class="caption center-align">
              <h3>Swine Security</h3>
              <h5 class="light grey-text text-lighten-3">security and legitimacy of both customers and
breeders is ensured through establishing a set
of criteria/qualifications.</h5>
            </div>
          </li>
        </ul>
    </div>

@endsection

@section('initScript')
    <script src="/js/customer/swinecart.js"> </script>
    <script src="/js/customer/customer_custom.js"> </script>
@endsection
