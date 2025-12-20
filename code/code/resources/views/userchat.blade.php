<!-- Sidebar inner chat start-->
<div class="showChat_inner">
    <div class="media chat-inner-header">
        <a class="back_chatBox">
            <i class="fa fa-chevron-left"></i> {{ucwords(strtolower($receipent[key($receipent)]))}}
        </a>
    </div>
    <div class="chat-slim-scroll">
    @if(count($chatDetails) > 0)
        @foreach($chatDetails as $message)
            <?php $message = (array) $message; ?>            
            <div class="chat-messages">
                @if($senderId == $message['sender_id'])
                    <div class="media">
                        <div class="media-body chat-menu-reply">
                            <div class="">
                                <p class="chat-cont">{{$message['chat_text']}}</p>
                            </div>
                        </div>
                        <div class="media-right photo-table">
                            <a href="javascript:void(0);">
                               <div class="user-name-box">{{strtoupper($message['sender_name'][0])}}</div>
                            </a>
                        </div>
                    </div>
                    <p class="chat-time">{{\Carbon\Carbon::parse($message['created_at'])->format('M d,g:i A')}}</p>
                @else
                    <div class="media">
                        <a class="media-left photo-table" href="javascript:void(0);">
                            <div class="user-name-box">{{strtoupper($message['sender_name'][0])}}</div>
                        </a>
                        <div class="media-body chat-menu-content">
                            <div class="">
                                <p class="chat-cont">{{$message['chat_text']}}</p>
                            </div>
                        </div>
                    </div>
                    <p class="chat-time text-right">{{\Carbon\Carbon::parse($message['created_at'])->format('M d,g:i A')}}</p>
                @endif
            
            </div>
            
        @endforeach
            <div class="float-right mr-5 new-message">
                <input type="hidden" id="lastchat_id" name="" value="{{$message['chat_id']}}">
                <input type="hidden" id="lastchat_id_new" name="" value="">
               {{--  @if(Cache::get(Session::get('userId').'_has_message')) --}}   
				  <label id="toggleNewMessage" class="label label-primary badge bg-c-green has_message" style="display:none; margin-right:80px; cursor:pointer;">New Message</label>
                 {{-- <i class="fa fa-2x fa-commenting-o alert-message" aria-hidden="true"></i> --}} 
               {{--  @endif --}} 
                
            </div>
    @endif
</div>
    <div class="chat-reply-box p-b-20"> 
        <div class="right-icon-control">
            <input type="text" class="form-control search-text" id="message" name="message" placeholder="Share Your Thoughts">
            <div class="form-icon">
                <i class="fa fa-paper-plane saveMessage" id="{{key($receipent)}}"></i>
            </div>
        </div>
    </div>
</div>
<!-- Sidebar inner chat end-->
{{-- @push('scripts') --}}
<script type="text/javascript">
		
	$(document).ready(function(){
		
	});

</script>
{{-- @endpush --}}