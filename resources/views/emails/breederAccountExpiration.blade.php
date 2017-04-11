@extends('layouts.adminNotificationsLayout')

@section('title')
    - SwineCart Breeder Account Expiration
@endsection

@section('header')
    <div class="row">
        <div class="col s12 m12 l12 xl12">
            @if ($type == 0)
                <h4>Breeder Accreditation Expiration within this Month<h4>
            @elseif ($type == 1)
                <h4>Breeder Accreditation Expiration within this Week<h4>
            @endif
        </div>
    </div>
    <div class="divider"></div>
@endsection

@section('content')
    <div class="row">
        <div class="col s12 m12 l12 xl12">
            @if ($type == 0)
                <p>Dear {{$username}},</p>
                <p>Your account <em><strong>{{$email}}</em></strong> accreditation will expire this within this month on <strong>{{$expiration}}</strong>.</p>
                <p>Please consider renewing your accreditation as soon as possible</p>
            @elseif ($type == 1)
                <p>Dear {{$username}},</p>
                <p>Your account <em><strong>{{$email}}</em></strong> accreditation will expire this within this week on <strong>{{$expiration}}</strong>.</p>
                <p>Please consider renewing your accreditation as soon as possible</p>
            @endif
        </div>
    </div>
@endsection
