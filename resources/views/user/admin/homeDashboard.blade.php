{{-- Administrator Home Page/ Administrator Dashboard --}}

@extends('layouts.controlLayout')

@section('title')
    | Administrator Dashboard
@endsection

@section('pageId')
    id="admin-home-dashboard"
@endsection

@section('nav-title')
    Administrator Dashboard
@endsection

@section('content')
    <div class="row">
        <div class="col s12 m12 l12 xl12">

            <a href="{{route('admin.userlist')}}" id="total-user-summary">
              <div class="col s12 m12 l6 xl6" >
                <div id="admin-dashboard-total-card" class="card-panel card-summary hoverable">
                   <div class="center-align white-text row valign-wrapper">
                      <div class="col s4 m4 l4 xl4 label-wrapper valign">
                         <div class="left-align valign">
                            <i class="admin-dashboard-ecommerce-icon">p</i>
                         </div>
                         <div class="valign admin-dashboard-summary-title">
                            TOTAL USERS
                         </div>
                      </div>
                       <div class="col s8 m8 l8 xl8 valign center-align white-text admin-dashboard-summary-data truncate tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{$summary[0]}}">
                           {{$summary[0]}}
                       </div>
                   </div>

                </div>
              </div>
            </a>

            <a href="{{route('admin.blocked.users')}}" id="total-user-summary">
              <div class="col s12 m12 l6 xl6" >
                <div id="admin-dashboard-blocked-card" class="card-panel card-summary hoverable">
                   <div class="center-align white-text row valign-wrapper">
                      <div class="col s4 m4 l4 xl4 label-wrapper valign">
                         <div class="left-align valign">
                            <i class="admin-dashboard-ecommerce-icon">b</i>
                         </div>
                         <div class="valign admin-dashboard-summary-title">
                            BLOCKED USERS
                         </div>
                      </div>
                       <div class="col s8 m8 l8 xl8 valign center-align white-text admin-dashboard-summary-data truncate tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{$summary[1]}}">
                           {{$summary[1]}}
                       </div>
                   </div>

                </div>
              </div>
            </a>

            <a href="{{route('admin.pending.users')}}" id="total-user-summary">
              <div class="col s12 m12 l6 xl6" >
                <div id="admin-dashboard-pending-card" class="card-panel card-summary hoverable">
                   <div class="center-align white-text row valign-wrapper">
                      <div class="col s4 m4 l4 xl4 label-wrapper valign">
                         <div class="left-align valign">
                            <i class="admin-dashboard-ecommerce-icon">w</i>
                         </div>
                         <div class="valign admin-dashboard-summary-title">
                            PENDING ACCOUNTS
                         </div>
                      </div>
                       <div class="col s8 m8 l8 xl8 valign center-align white-text admin-dashboard-summary-data truncate tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{$summary[2]}}">
                            {{$summary[2]}}
                       </div>
                   </div>

                </div>
              </div>
            </a>

            <a href="{{route('admin.breeder.messages')}}" id="total-user-summary">
              <div class="col s12 m12 l6 xl6" >
                <div id="admin-dashboard-messages-card" class="card-panel card-summary hoverable">
                   <div class="center-align white-text row valign-wrapper">
                      <div class="col s4 m4 l4 xl4 label-wrapper valign">
                         <div class="left-align valign">
                            <i class="admin-dashboard-ecommerce-icon">y</i>
                         </div>
                         <div class="valign admin-dashboard-summary-title">
                            UNREAD MESSAGES
                         </div>
                      </div>
                       <div class="col s8 m8 l8 xl8 valign center-align white-text admin-dashboard-summary-data truncate tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{$summary[3]}}">
                           {{$summary[3]}}
                       </div>
                   </div>

                </div>
              </div>
            </a>

            <a href="{{route('admin.statistics.dashboard')}}" id="site-statistics-summary">
              <div class="col s12 m12 l12 xl12" >
                <div id="admin-dashboard-statistics-card" class="card-panel card-summary hoverable">
                   <div class="center-align row valign-wrapper">
                      <div class="col s4 m4 l4 xl4 label-wrapper valign">
                            <i id="admin-dashboard-statistics-icon" class="admin-dashboard-ecommerce-icon">x</i>
                      </div>
                       <div id="admin-dashboard-statistics-title" class="col s8 m8 l8 xl8 valign center-align">
                           SITE STATISTICS
                       </div>
                   </div>

                </div>
              </div>
            </a>

        </div>
    </div>
@endsection