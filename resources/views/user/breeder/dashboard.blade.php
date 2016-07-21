{{--
    Displays products of the respective Breeder user
--}}

@extends('user.breeder.home')

@section('title')
    | Breeder - Dashboard
@endsection

@section('pageId')
    id="page-breeder-dashboard"
@endsection

@section('breadcrumbTitle')
    Dashboard
@endsection

@section('breadcrumb')
    <a href="{{ route('home_path') }}" class="breadcrumb">Home</a>
    <a href="#!" class="breadcrumb">Dashboard</a>
@endsection

@section('content')
    <div class="row">
        <div class="col s12">
            <p class="caption">
                Your Dashboard. <br>
            </p>
        </div>
    </div>
    <div id="card-status" class="row">
        {{-- Sold Products --}}
        <div class="col s12 m6">
            <div class="card">
                <div class="card-content teal white-text">
                    <span class="card-title">Sold Products</span>
                    <h3>{{ $dashboardCollection['soldProducts']['overall'] }}</h3>
                </div>
                <div class="card-action teal darken-4">
                    <a class="white-text">Boar: {{ $dashboardCollection['soldProducts']['boar'] }} </a>
                    <a class="white-text">Sow: {{ $dashboardCollection['soldProducts']['sow'] }} </a>
                    <a class="white-text">Semen: {{ $dashboardCollection['soldProducts']['semen'] }} </a>
                    {{-- <a href="#">This is a link</a>
                    <a href="#">This is a link</a> --}}
                </div>
            </div>
        </div>

        {{-- Available Products --}}
        <div class="col s12 m6">
            <div class="card">
                <div class="card-content pink white-text">
                    <span class="card-title">Available Products</span>
                    <h3>{{ $dashboardCollection['availableProducts']['overall'] }}</h3>
                </div>
                <div class="card-action pink darken-4 white-text">
                    <a class="white-text">Boar: {{ $dashboardCollection['availableProducts']['boar'] }}</a>
                    <a class="white-text">Sow: {{ $dashboardCollection['availableProducts']['sow'] }}</a>
                    <a class="white-text">Semen: {{ $dashboardCollection['availableProducts']['semen'] }}</a>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="col s12 m6">
            <div class="card">
                <div class="card-content grey darken-2 white-text">
                    <span class="card-title">
                        <a href="{{ route('dashboard.productStatus') }}" class="white-text">Status</a>
                    </span>
                    <p>
                        Hidden: <br>
                        Displayed: <br>
                        Requested: <br>
                        Reserved: <br>
                        Paid: <br>
                        On Delivery: <br>
                        Sold:
                    </p>
                </div>
                {{-- <div class="card-action blue-grey darken-2">
                    <a href="#">This is a link</a>
                    <a href="#">This is a link</a>
                </div> --}}
            </div>
        </div>

        {{-- Rating --}}
        <div class="col s12 m6">
            <div class="card">
                <div class="card-content pink darken-4 white-text">
                    <span class="card-title">Ratings</span>
                    <h3>67%</h3>
                </div>
                <div class="card-action pink">
                    <a class="white-text">Delivery: 4</a>
                    <a class="white-text">Transaction: 3.5</a>
                    <a class="white-text">Product Quality: 3.2</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Location --}}
        <div class="col s12">
            <div class="card">
                <div class="card-content teal darken-4 white-text">
                    <span class="card-title">Sales by Region</span>
                    <p>I am a very simple card. I am good at containing small bits of information.
                    I am convenient because I require little markup to use effectively.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customScript')
    <script src="/js/vendor/autobahn.min.js"></script>
    <script type="text/javascript">

        var onConnectCallback = function(session){
            console.log('Session is open!');
            session.subscribe('{{ $topic }}', function(topic, data) {
                // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
                console.log('New task name added "' + topic + '"');
                console.log(data);
            });
        };

        var onHangupCallback = function(code, reason, detail){
            console.warn('WebSocket connection closed');
            console.warn(code+': '+reason);
        };

        var conn = new ab.connect(
            config.breederWSServer,
            onConnectCallback,
            onHangupCallback,
            {
                'maxRetries': 30,
                'retryDelay': 2000,
                'skipSubprotocolCheck': true
            }
        );
    </script>

@endsection
