<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<style>
/* Header */
.JudulContent {
    font-weight: 600;
    font-size: 18px;
    padding: 10px 15px;
    color: #fff;
    background: linear-gradient(90deg, #4e73df, #1cc88a);
    height: auto;
}

/* Chat List */
.chat-item {
    border-radius: 10px;
    transition: all 0.2s ease;
}

.chat-item:hover {
    background-color: #e9ecef;
    transform: translateX(3px);
}

.chat-item.active {
    background-color: #dbeafe !important;
    border-left: 4px solid #4e73df;
}

.chat-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-preview {
    font-size: 13px;
    color: #6c757d;
}

.chat-time {
    font-size: 12px;
    color: #adb5bd;
}

/* Chat Content */
.chat-box {
    padding: 15px;
}

.message {
    max-width: 70%;
    padding: 10px 15px;
    border-radius: 15px;
    margin-bottom: 10px;
    font-size: 14px;
}

.message.me {
    background-color: #4e73df;
    color: #fff;
    margin-left: auto;
    border-bottom-right-radius: 5px;
}

.message.other {
    background-color: #e9ecef;
    color: #333;
    border-bottom-left-radius: 5px;
}

.chat-input {
    border-top: 1px solid #dee2e6;
    padding: 10px;
    background-color: #fff;
}
.list-group-item {
    border-radius: 10px !important;
}
</style>
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Chat/Announcement</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Chat List</span>
		</li>
	</ol>
</div>

<div>
    <div class="JudulContent">CHAT LIST</div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="row no-gutters">
                <div class="col-md-4" style="height: 480px; overflow-y: auto;">
                    <div class="list-group p-3" id="chatList" role="tablist">

                        @foreach($roomChat as $index => $conversation)

                            @php
                                $users = json_decode($conversation->Users);
                                $users = current(array_diff($users, ['ADMIN_USERS']));

                                // $lastMessage = $conversation->messages->first();
                            @endphp

                            <a class="list-group-item list-group-item-action chat-item mb-2 {{ $index==0 ? 'active':'' }}"
                            data-toggle="list"
                            href="#room-{{ $conversation->ID }}"
                            data-id="{{ $conversation->ID }}">

                                <div class="d-flex align-items-center">
                                    <img src="https://i.pravatar.cc/100?u={{ $users }}" class="chat-avatar mr-3">
                                    <div class="flex-grow-1">
                                        <strong>{{ $getUser[$users] }}</strong>
                                        <div class="chat-preview">
                                            {{-- {{ $lastMessage->message ?? 'Belum ada pesan' }} --}}
                                        </div>
                                    </div>
                                </div>

                            </a>

                        @endforeach

                    </div>
                </div>
                <div class="col-md-8" style="height: 480px;">

                    <div class="tab-content flex-grow-1 overflow-auto px-3 pt-3">

                        @foreach($roomChat as $index => $conversation)

                            <div class="tab-pane fade {{ $index==0 ? 'show active':'' }}"
                                id="room-{{ $conversation->ID }}">

                                <div class="chat-messages"
                                    id="chat-room-{{ $conversation->ID }}"
                                    style="overflow-y:auto; max-height:380px;">
                                </div>

                                <div class="position-absolute"
                                    style="bottom:0; left:0; right:0; padding:10px; background:#fff;">

                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control"
                                            id="input-{{ $conversation->ID }}"
                                            placeholder="Tulis pesan...">

                                        <div class="input-group-append">
                                            <button class="btn btn-primary btn-send"
                                                    data-conversation="{{ $conversation->ID }}">
                                                Kirim
                                            </button>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>
        </div>
    </div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
    $(document).ready(function(){

        let activeConversation = null;
        let lastMessageId = 0;
        let pollingInterval = null;

        function scrollToBottom($container){
            $container.scrollTop($container[0].scrollHeight);
        }

        function isNearBottom(container){
            return container.scrollHeight - container.scrollTop 
                <= container.clientHeight + 50;
        }

        function loadMessages(conversationId){

            $.get('partner-chat-messages-' + conversationId, function(data){

                let container = $('#chat-room-' + conversationId);
                container.html('');

                $.each(data, function(i, msg){

                    let className = msg.created_by == "{{ $Saya }}"
                        ? 'message me'
                        : 'message other';

                    container.append(
                        '<div class="'+className+'" data-id="'+msg.id+'">'+msg.message+'</div>'
                    );

                    lastMessageId = msg.id;
                });

                scrollToBottom(container);
            });
        }

        let intervalTime = 2000; // 2 detik

        function startPolling(){

            if(pollingInterval){
                clearInterval(pollingInterval);
            }

            pollingInterval = setInterval(function(){

                if(!activeConversation || !isWindowActive) return;

                $.post('partner-chat-fetch-new', {
                    _token: '{{ csrf_token() }}',
                    conversation_id: activeConversation,
                    last_id: lastMessageId
                }, function(data){
                    
                    if(data.length > 0){
                        intervalTime = 2000; // aktif → cepat

                        let container = $('#chat-room-' + activeConversation);
                        let shouldScroll = isNearBottom(container[0]);

                        $.each(data, function(i, msg){

                            let className = msg.created_by == "{{ $Saya }}"
                                ? 'message me'
                                : 'message other';

                            container.append(
                                '<div class="'+className+'" data-id="'+msg.id+'">'+msg.message+'</div>'
                            );

                            lastMessageId = msg.id;
                        });

                        if(shouldScroll){
                            scrollToBottom(container);
                        }
                    } else {
                        intervalTime = 5000; // idle → lambat
                    }

                });

            }, intervalTime); // polling tiap 2 detik
        }

        // =========================
        // AUTO DETECT ROOM AKTIF SAAT PAGE LOAD
        // =========================
        let firstActive = $('.chat-item.active').data('id');

        if(firstActive){
            activeConversation = firstActive;
            loadMessages(activeConversation);
            startPolling();
        }

        // =========================
        // AUTO SWITCH ROOM
        // =========================
        $('.chat-item').on('shown.bs.tab', function(){
            activeConversation = $(this).data('id');
            lastMessageId = 0;
            loadMessages(activeConversation);
            startPolling();
        });

        // =========================
        // AUTO SEND MESSAGE
        // =========================
        $('.btn-send').on('click', function(){

            let conversationId = $(this).data('conversation');
            let input = $('#input-' + conversationId);
            let message = input.val();

            if(message.trim() === '') return;

            $.post('partner-chat-send', {
                _token: '{{ csrf_token() }}',
                conversation_id: conversationId,
                message: message
            }, function(response){

                input.val('');

                let container = $('#chat-room-' + conversationId);

                container.append(
                    '<div class="message me" data-id="'+response.id+'">'+response.message+'</div>'
                );

                lastMessageId = response.id;

                scrollToBottom(container);
            });
        });

    });
</script>
@endsection