@if(auth()->user()->Priv_ID == 'PARTNER')
@php
    $conversation = \App\RoomChat::where('Users', 'LIKE', "%".auth()->user()->username."%")->first();
@endphp
<!-- Chat Container -->
<div id="chatBox" class="chat-container1 shadow">
    
    <!-- Header -->
    <div class="chat-header1 d-flex justify-content-center align-items-center"
        onclick="toggleChat()">
        <span class="chat-title">CS - GEU</span>
    </div>

    <!-- Body -->
    <div id="chatBody1" class="chat-body1">

    </div>
    <!-- Input -->
    <form action="#" method="POST" class="chat-footer1">
        @csrf
        <input type="text"
            class="form-control"
            id="input-{{ $conversation->ID }}"
            name="message"
            placeholder="Tulis pesan...">
        <button class="btn btn-primary btn-send"
                data-conversation="{{ $conversation->ID }}">
            Kirim
        </button>
    </form>


</div>
<style>
    .chat-container1 {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 320px;
        border-radius: 10px;
        overflow: hidden;
        background: white;
        transition: all 0.3s ease;
        z-index: 9999;
    }

    .chat-header1 {
        background-color: #4e73df;
        color: white;
        padding: 10px;
        cursor: pointer;
        font-weight: 500;
    }

    .chat-body1 {
        height: 250px;
        overflow-y: auto;
        padding: 10px;
        background: #f8f9fc;
        display: flex;
        flex-direction: column;
    }

    .chat-message1 {
        padding: 8px 12px;
        border-radius: 15px;
        margin-bottom: 8px;
        max-width: 75%;
        font-size: 14px;
    }

    .chat-message1.send {
        background: #4e73df;
        color: white;
        align-self: flex-end;
    }

    .chat-message1.receive {
        background: #e2e6ea;
        color: black;
        align-self: flex-start;
    }

    .chat-footer1 {
        display: flex;
        gap: 5px;
        padding: 8px;
        border-top: 1px solid #ddd;
        background: #fff;
    }

    .chat-body1,
    .chat-footer1 {
        transition: all 0.3s ease;
    }

    .chat-body1 {
        max-height: 250px;
        overflow-y: auto;
        opacity: 1;
    }

    .chat-footer1 {
        opacity: 1;
    }

    .chat-minimized1 .chat-body1 {
        max-height: 0;
        opacity: 0;
        padding-top: 0;
        padding-bottom: 0;
    }

    .chat-minimized1 .chat-footer1 {
        opacity: 0;
        pointer-events: none;
    }

    /* ===============================
    MINIMIZED MODE (LINGKARAN)
    ================================= */

    .chat-container1 {
        transition: all 0.5s ease;
    }

    /* Mode Lingkaran */
    .chat-container1.chat-minimized1 {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        padding: 0;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    /* Sembunyikan body & footer saat minimize */
    .chat-container1.chat-minimized1 .chat-body1,
    .chat-container1.chat-minimized1 .chat-footer1 {
        display: none;
    }

    /* Header jadi icon di tengah */
    .chat-container1.chat-minimized1 .chat-header1 {
        background-color: #4e73df;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Sembunyikan text saat minimize */
    .chat-container1.chat-minimized1 .chat-title {
        display: none;
    }

    /* Tambahkan icon chat */
    .chat-container1.chat-minimized1 .chat-header1::before {
        content: "💬";
        font-size: 25px;
    }
    .chat-container1.chat-minimized1 {
        box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        transform: scale(0.95);
    }

    .chat-container1.chat-minimized1:hover {
        transform: scale(1);
    }
    </style>

    <script>
        function toggleChat() {
            // document.getElementById("chatBox")
            //     .classList.toggle("chat-minimized1");
            const box = document.getElementById("chatBox");
            box.classList.toggle("chat-minimized1");

            // 🔥 Jika sekarang terbuka → mark read
            if(!box.classList.contains("chat-minimized1")){
                setTimeout(function(){
                    markAsRead();
                    scrollToBottom();
                }, 300);
            }
        }

        // Auto scroll ke bawah saat load
        // document.addEventListener("DOMContentLoaded", function() {
        //     let chatBody = document.getElementById("chatBody1");
        //     chatBody.scrollTop = chatBody.scrollHeight;
        // });
        $(document).ready(function(){

            let conversationId = "{{ $conversation->ID ?? '' }}";
            let lastMessageId = 0;
            let pollingInterval = null;
            let firstMessageId = 0;
            let loadingOld = false;
            let noMoreOldData = false;
            setTimeout(() => {
                toggleChat();
            }, 500);

            function scrollToBottom(){
                const container = document.getElementById('chatBody1');
                if(!container) return;
                console.log(container.scrollHeight);
                container.scrollTop = container.scrollHeight;
            }
            
            function isChatOpen(){
                return !$('#chatBox').hasClass('chat-minimized1');
            }

            function markAsRead(){

                if(!conversationId) return;
                if(!isChatOpen()) return;

                $.post('partner-chat-mark-read', {
                    _token: '{{ csrf_token() }}',
                    conversation_id: conversationId
                });
            }

            function loadMessages(){

                if(!conversationId) return;

                $.get('partner-chat-messages-' + conversationId, function(data){

                    let container = $('#chatBody1');
                    container.html('');

                    $.each(data, function(i, msg){

                        let className = msg.created_by == "{{ auth()->user()->username }}"
                            ? 'chat-message1 send'
                            : 'chat-message1 receive';

                        container.append(
                            '<div class="'+className+'" data-id="'+msg.id+'">'+msg.message+'</div>'
                        );

                        if(i === 0){
                            firstMessageId = msg.id; // pesan paling atas
                        }

                        lastMessageId = msg.id;
                    });

                    scrollToBottom();
                });
            }

            function startPolling(){

                if(pollingInterval){
                    clearInterval(pollingInterval);
                }

                pollingInterval = setInterval(function(){

                    if(!conversationId) return;

                    $.post('partner-chat-fetch-new', {
                        _token: '{{ csrf_token() }}',
                        conversation_id: conversationId,
                        last_id: lastMessageId
                    }, function(data){

                        if(data.length > 0){

                            let container = $('#chatBody1');

                            $.each(data, function(i, msg){

                                let className = msg.created_by == "{{ auth()->user()->username }}"
                                    ? 'chat-message1 send'
                                    : 'chat-message1 receive';

                                container.append(
                                    '<div class="'+className+'" data-id="'+msg.id+'">'+msg.message+'</div>'
                                );

                                lastMessageId = msg.id;
                            });

                            scrollToBottom();
                            markAsRead();
                        }

                    });

                }, 5000); // polling tiap 2 detik
            }

            // =========================
            // LOAD SAAT PERTAMA
            // =========================
            loadMessages();
            startPolling();
            markAsRead();

            // =========================
            // KIRIM PESAN
            // =========================
            $('.chat-footer1').on('submit', function(e){

                e.preventDefault();

                let input = $(this).find('input[name="message"]');
                let message = input.val();

                if(message.trim() === '') return;

                $.post('partner-chat-send', {
                    _token: '{{ csrf_token() }}',
                    conversation_id: conversationId,
                    message: message
                }, function(response){

                    input.val('');

                    let container = $('#chatBody1');

                    container.append(
                        '<div class="chat-message1 send" data-id="'+response.id+'">'+response.message+'</div>'
                    );

                    lastMessageId = response.id;

                    scrollToBottom();
                });
            });

            $('#chatBody1').on('scroll', function(){
                
                let container = this;

                if(container.scrollTop === 0 && !loadingOld && !noMoreOldData){

                    loadingOld = true;

                    let previousHeight = container.scrollHeight;
                    $('#chatBody1').prepend('<div id="loadingOld" class="text-center">Loading...</div>');

                    $.post('partner-chat-load-older', {
                        _token: '{{ csrf_token() }}',
                        conversation_id: conversationId,
                        first_id: firstMessageId
                    }, function(data){

                        $('#loadingOld').remove();

                        if(data.length === 0){
                            noMoreOldData = true;
                            loadingOld = false;
                            return;
                        }

                        let html = '';

                        $.each(data, function(i, msg){

                            let className = msg.created_by == "{{ auth()->user()->username }}"
                                ? 'chat-message1 send'
                                : 'chat-message1 receive';

                            html += '<div class="'+className+'" data-id="'+msg.id+'">'+msg.message+'</div>';

                            if(i === 0){
                                firstMessageId = msg.id;
                            }
                        });

                        $('#chatBody1').prepend(html);

                        // 🔥 JAGA POSISI SCROLL AGAR TIDAK LONCAT
                        let newHeight = container.scrollHeight;
                        container.scrollTop = newHeight - previousHeight;

                        loadingOld = false;
                    });
                }
            });

        });
    </script>
@endif