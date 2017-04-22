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
    <li id="message-main-container">
        <a href="{{ route('customer.messages') }}" id="message-icon"
            data-alignment="right"
        >
            <i class="material-icons left">message</i>
            <span class="badge"
                v-if="unreadCount > 0  && unreadCount <= 99"
            >
                @{{ unreadCount }}
            </span>
            <span class="badge"
                v-if="unreadCount > 99"
            >
                99+
            </span>
        </a>
    </li>
    @if(!Auth::user()->update_profile)
        {{-- Swine Cart --}}
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
                    <a href="{{ route('view.cart') }}" class="center-align">Go to Cart</a>
                </li>
            </ul>
        </li>
        <li id="notification-main-container">
            <a href="#!" id="notification-icon"
                class="dropdown-button"
                data-beloworigin="true"
                data-hover="false"
                data-alignment="right"
                data-activates="notification-dropdown"
                @click.prevent="getNotificationInstances"
            >
                <i class="material-icons"
                    :class="notificationCount > 0 ? 'left' : '' "
                >
                    notifications
                </i>
                <span class="badge"
                    v-if="notificationCount > 0 && notificationCount <= 99"
                >
                    @{{ notificationCount }}
                </span>
                <span class="badge"
                    v-if="notificationCount > 99"
                >
                    99+
                </span>
            </a>
            <ul id="notification-dropdown" class="dropdown-content collection">
                <div id="notification-preloader-circular" class="row">
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
                        </div>
                    </div>
                </div>
                <li>
                    <ul id="notification-container" class="collection">
                        <li v-for="(notification,index) in notifications"
                            style="overflow:auto;"
                            class="collection-item"
                        >
                            <a class="black-text"
                                :href="notification.url"
                                @click.prevent="goToNotification(index)"
                            >
                                <span class="left" v-if="!notification.read_at">
                                    <i class="material-icons indigo-text text-darken-2" style="font-size:1rem;">radio_button_checked</i>
                                </span>
                                <span class="left" v-else >
                                    <i class="material-icons indigo-text text-darken-2" style="font-size:1rem;">radio_button_unchecked</i>
                                </span>
                                <p style="margin-left:1.5rem;" :class=" (notification.read_at) ? 'grey-text' : '' ">
                                    <span v-html="notification.data.description"></span>
                                </p>
                                <p class="right-align grey-text text-darken-1" style="margin-left:1.5rem; font-size:0.8rem;"> @{{ notification.data.time.date | transformToReadableDate }} </p>
                            </a>
                        </li>

                    </ul>
                </li>
                <li>
                    <a href="{{ route('cNotifs') }}" class="center-align">See all Notifications</a>
                </li>
            </ul>
        </li>
    @endif
@endsection

@section('navbarDropdown')
    <li><a href="{{ route('customer.edit') }}"> <i class="material-icons left">people</i> Update Profile</a></li>
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
            <div style="height:1px;">
            </div>
            <form>
                <div class="input-field">
                    <input id="search" type="search" name="q" placeholder="Search for a product" value="{{ request('q') }}" autocomplete="off">
                    <label class="label-icon" for="search"><i class="material-icons teal-text">search</i></label>
                    <i class="material-icons">close</i>
                </div>
            </form>
        </div>
    </nav>

    <div id="search-results" class="z-depth-2" style="display:none; position:absolute; background-color:white; z-index:9999;">
        <ul></ul>
    </div>

    <div class="row">
    </div>

  {{-- Slider --}}
  <div class="slider home-slider">
      <ul class="slides">
        @forelse($homeContent as $content)
            <li>
              <img src= {{$content->path.$content->name}}>
              <div class="caption center-align">
                <h3>{{$content->title}}</h3>
                <h5 class="light grey-text text-lighten-3 content-text">{{$content->text}}</h5>
              </div>
            </li>
        @empty
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
        @endforelse
      </ul>
  </div>

@endsection

@section('initScript')
    <script src="/js/vendor/VueJS/vue.js"></script>
    <script src="/js/vendor/VueJS/vue-resource.min.js"></script>
    <script src="/js/vendor/moment.min.js"></script>
    <script src="/js/customer/swinecart.js"> </script>
    <script src="/js/customer/customer_custom.js"> </script>
@endsection

@section('customScript')
    <script src="/js/vendor/elasticsearch.jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){

            // Setup Elasticsearch
            var client = new $.es.Client({
                hosts: 'http://localhost:9200'
            });

            document.querySelector('#search-results').style.width = document.querySelector('#search-field').offsetWidth+'px';

            $("input#search").keydown(function(e){
                // Perform GET request upon pressing the Enter key
                // or fetch suggestions from Elastic search
                // and output it on search results
                if(e.which == 13) {
                    e.preventDefault();
                    filter.apply();
                }
                else{
                    setTimeout(function(){
                        searchPhrase = document.querySelector('input#search').value;

                        // Execute of searchPhrase is not empty
                        if(searchPhrase){

                            // Query on Elasticsearch search engine
                            client.search({
                                index: 'swinecart',
                                type: 'products',
                                body:{
                                    "_source": "output",
                                    "suggest": {
                                        "productSuggest": {
                                            "prefix": searchPhrase,
                                            "completion": {
                                                "field": "suggest",
                                                "fuzzy": {
                                                	"fuzziness": 2
                                                }
                                            }
                                        }
                                    }
                                }
                            }).then(function(response){
                                var options = response.suggest.productSuggest[0].options;
                                var searchResultsTop = '';
                                var searchResultsBot = '';

                                if(options.length > 0){

                                    for (var i = 0; i < 3; i++) {
                                        searchResultsTop += '<li class="search-item">' +
                                            options[i]._source.output[1] +
                                            '</li>';

                                        searchResultsBot += '<li class="search-item">' +
                                            options[i]._source.output[0] +
                                            '</li>';
                                    }

                                    document.querySelector("#search-results ul").innerHTML = searchResultsTop + searchResultsBot;

                                    $("#search-results").show();
                                }

                            }, function(error){
                                console.trace(error.message);
                            });
                        }

                        else $("#search-results").hide();

                    }, 0);

                }
            });

            $('body').on('click', 'li.search-item', function(e){
                e.preventDefault();

                var searchInput = document.querySelector('input#search');
                searchInput.value = $(this).html();

                $("#search-results").hide();

                // Setup search query parameter
                var filter_parameters = '?';
                var search_query = document.getElementById('search').value;

                // Check if there is search query
                if(search_query){
                    filter_parameters += 'q=' + search_query + '&sort=none';
                }

                // Redirect to view products page with designated search query parameter
                window.location = config.viewProducts_url+filter_parameters;
            });
        });
    </script>
@endsection
