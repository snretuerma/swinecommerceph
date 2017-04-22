
{{--
    Displays Customer profile form upon profile edit
--}}

@extends('user.customer.home')

@section('title')
    | Customer - Messages
@endsection

@section('pageId')
    id="page-customer-messages"
@endsection

@section('breadcrumbTitle')
    Messages
@endsection

@section('breadcrumb')
    <a href="{{ route('home_path') }}" class="breadcrumb">Home</a>
    <a href="#!" class="breadcrumb">Messages</a>
@endsection

@section('content')
<style>
 	#chatMessages{ width: 100%; border: 1px solid #ddd; min-height: 100px; list-style: none; padding-left: 0px; height: 400px; overflow-y: auto;}
 	#chatMessages li { width: 100%; padding: 10px;}

 	li.message.system span.who { color: red; }
 	li.message.user span.who   { color: blue; }
 	li.message.mine span.who   { font-weight: bold; }
</style>

<div class="row">

	<div class="col m3 row">
	  <ul class="collection">
	  	@foreach($threads as $thread)
	  		@if($userType == 'Customer')
	  			<a href="/customer/messages/{{ $thread->breeder_id }}">
	  		@else
	  			<a href="/breeder/messages/{{ $thread->customer_id }}">
	  		@endif

	  		@if(($threadId == $thread->breeder_id && $userType == 'Customer') || ($threadId == $thread->customer_id && $userType == 'Breeder'))
		    	<li class="collection-item avatar green lighten-4">
	  		@else
		    	<li class="collection-item avatar">
	  		@endif

		      <i class="material-icons circle small">chat</i>
		      <span class="title">{{ $thread->otherparty() }}</span>

		    </li>
		    </a>
	    @endforeach
	  </ul>
	</div>

	<div class="col m9 row">

		<div>
			
			<div class="panel panel-default">
				
				<div id="threadname" class="panel-heading">{{ Auth::user()->name }}</div>

				<div class="panel-body" id="chat">
					
					<ul id="chatMessages">

						@foreach($messages as $message)
							@if (($message->direction == 0 && $userType == 'Customer') || ($message->direction == 1 && $userType == 'Breeder'))
								<li class="message" :class="mine">
									<span class="who">
										Me: 
									</span>
									{{ $message->message }}
								</li>
							@else
								<li class="message" :class="user">
									<span class="who">
							    		{{ $message->sender() }}: 
									</span>
									{{ $message->message }}
								</li>
							@endif
						@endforeach

						<li v-for="message in messages" class="message" :class="message.class">
							<span class="who">@{{ message.who }}: </span>@{{ message.msg }}
						</li>

					</ul>

					<div style="display:table; width: 100%;">
						
						<input placeholder="Enter your message here."
						 		style="display:table-cell; width: 100%;"
							   type="text" 
							   v-model="newMessage"
							   @keyup.enter="sendMessage"/>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>


@endsection

@section('customScript')
    <!-- Latest Vue JS CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.16/vue.min.js"></script>
<script> 
	var username = "{{ $userName }}";
	var userid = "{{ $userId }}";
	var usertype = "{{ $userType }}";

	var chatport = "{{ $chatPort }}";
	var url = "{{ explode(':', str_replace('http://', '', str_replace('https://', '', App::make('url')->to('/'))))[0] }}";
	var threadid = "{{ $threadId }}";
	
</script>
<script type="text/javascript" src="/js/chat.js"></script>
@endsection
