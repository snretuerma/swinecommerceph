{{--
    Displays all users
--}}

@extends('layouts.adminLayout')

@section('title')
    | Messenger
@endsection

@section('pageId')
    id="page-admin-view-users"
@endsection

@section('breadcrumbTitle')
    Users
@endsection

@section('breadcrumb')
    <a href="{{ route('home_path') }}" class="breadcrumb">Home</a>
    <a href="#!" class="breadcrumb">Users</a>
@endsection

@section('content')



<div class="row">
    <div class="input-field col s10">
        <textarea id="email" name="email" class="materialize-textarea" readonly></textarea>
        <label for="email" class="active to-label">To</label>
    </div>
    <div class="input-field col s2 valign-wrapper">
        <a class="waves-effect waves-light btn modal-trigger valign receipients"><i class="material-icons">add</i></a>
    </div>
    <!--div class="input-field col s12">
        <input autocomplete="off" id="subject" name="subject" type="text" class="validate">
        <label for="subject" class="active">Subject</label>
    </div-->
    <div class="input-field col s12">
        <textarea id="message" name="message" class="materialize-textarea"></textarea>
        <label for="message" class="active">Message</label>
    </div>
    <div class="input-field col s12 valign-wrapper">
        <a class="waves-effect waves-light btn valign" id="send-mail"><i class="material-icons left">send</i>Send as Email</a>&nbsp;
        <a class="waves-effect waves-light btn valign" id="send-sms"><i class="material-icons left">send</i>Send as SMS</a>&nbsp;
        <div class="preloader-wrapper small active" id="sending" style="display:none;">
            <div class="spinner-layer spinner-teal-only">
              <div class="circle-clipper left">
                <div class="circle"></div>
              </div><div class="gap-patch">
                <div class="circle"></div>
              </div><div class="circle-clipper right">
                <div class="circle"></div>
              </div>
            </div>
        </div>
    </div>
</div>


  <div id="modal1" class="modal modal-fixed-footer">
    <div class="modal-header center-align">
      <h5>Select your recipients</h5>
    </div>
    <div class="modal-content">
      <div class="row">
        @foreach ($users as $user)
            <div class="col s4">
              <input type="checkbox" class="filled-in receipient" id="userid-{{$user->id}}" username="{{$user->name}}" value="{{$user->id}}"/>
              <label for="userid-{{$user->id}}">{{$user->name}}</label>
            </div>
        @endforeach
      </div>
    </div>
    <div class="modal-footer">
      <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat add-receipients">Add Recepients</a>
    </div>
  </div>

   

@endsection

@section('customScript')
    <script>
        $(document).ready(function(){

            console.log('test');

            $('#email, .receipients').click(function(){
                $('#modal1').modal('open');
            });

            $('.add-receipients').click(function(){
                $('#email').val('');
                $('.to-label').removeClass('active');

                $( ".receipient" ).each(function( index ) {
                   if($(this).is(':checked') || $(this).prop('checked')){
                        if(!$('#email').val()){
                            $('#email').val($(this).attr('username'));
                        }else{
                            $('#email').val($('#email').val()+', '+$(this).attr('username'));
                        }
                   }
                });
                var str = $('#email').val();
                if(str.trim() != '') $('.to-label').addClass('active');

            });


            $('#send-mail').click(function(){
                send('mail');
            });

             $('#send-sms').click(function(){
                send('sms');
            });

            function send(type){
                $('#sending').show();
                rcpts = [];
                $( ".receipient" ).each(function( index ) {
                   if($(this).is(':checked') || $(this).prop('checked')){
                        rcpts.push( parseInt( $(this).attr('value') ) );
                   }
                });
                console.log(rcpts);

                $.ajax({
                    type: "POST",
                    url: "messenger/send",
                    data:{
                        type: type,
                        _token: "{{{ csrf_token() }}}",
                        receipients: JSON.stringify(rcpts),
                        message: $('#message').val(),
                    }, 
                    success: function(response){
                        console.log(response);
                        Materialize.toast('Message has been sent.', 3000);
                        $('#email').val('');
                        $('#message').val('');
                    },
                    error: function(e){
                        Materialize.toast('There was an error sending the message.', 3000);
                        console.error(e);
                    },
                    complete: function(){
                        $('#sending').hide();

                    }
                });
            }

        });
    </script>

@endsection

