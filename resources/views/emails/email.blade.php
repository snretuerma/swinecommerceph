{{--
    Message template
--}}

@extends('layouts.messageOneColumn')

@section('title')
    - Message
@endsection

@section('page-id')
    id="page-message"
@endsection

{{-- @section('email-css')
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,500,700" rel="stylesheet" type="text/css">
    <style media="screen">

        html{
            line-height: 1.5;
            font-family: "Roboto", sans-serif;
            font-weight: normal;
            color: rgba(0,0,0,0.87);
            box-sizing: border-box;
            display: block;
        }

        div{
            display: block;
        }

        nav{
            color: #fff;
            background-color: #ee6e73;
            width: 100%;
        }

        .navbar-fixed{
            position: relative;
            height: 56px;
            z-index: 998;
        }

        .navbar-fixed nav {
            position: fixed;
        }

        .teal.darken-3{
            background-color: #00695c !important;
        }

    </style>
@endsection --}}

@section('content')
    <div class="row">
        <div class="col s12 m6 offset-m3">
            <div class="card">
                <div class="card-content">
                  <p>
                     {!! $message_body !!}
                  </p>
                </div>
            </div>
        </div>
    </div>

@endsection
