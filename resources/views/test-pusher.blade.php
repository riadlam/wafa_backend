<!DOCTYPE html>
<html>
<head>
    <title>Pusher Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Pusher Test Chat</span>
                        <span id="connection-status" class="badge bg-secondary">Status: Disconnected</span>
                    </div>
                    <div class="card-body">
                        <div id="messages" class="mb-3" style="height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                            <div class="text-muted text-center py-3">Connecting to chat...</div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="message" class="form-control" placeholder="Type your message...">
                            <button class="btn btn-primary" id="send-message">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Enable pusher logging
        Pusher.logToConsole = true;
        console.log('Pusher library loaded, version:', Pusher.VERSION);

        // Debug Pusher configuration
        const pusherConfig = {
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }
        };
        console.log('Initializing Pusher with config:', JSON.stringify(pusherConfig, null, 2));

        // Initialize Pusher
        const pusher = new Pusher(pusherConfig.key, pusherConfig);

        // Connection state logging
        pusher.connection.bind('state_change', function(states) {
            console.log('Pusher connection state changed:', states);
            $('#connection-status').text('Status: ' + states.current).removeClass().addClass('badge ' + 
                (states.current === 'connected' ? 'bg-success' : 
                 states.current === 'connecting' ? 'bg-warning' : 'bg-danger'));
        });

        // Connection established
        pusher.connection.bind('connected', function() {
            console.log('Pusher connected successfully');
            console.log('Socket ID:', pusher.connection.socket_id);
        });

        // Connection failed
        pusher.connection.bind('error', function(err) {
            console.error('Pusher connection error:', err);
        });

        console.log('Subscribing to channel: chat');
        const channel = pusher.subscribe('chat');
        
        // Log subscription events
        channel.bind('pusher:subscription_succeeded', function() {
            console.log('Successfully subscribed to channel: chat');
            console.log('Channel state:', channel.subscriptionPending, channel.subscriptionCancelled);
        });
        
        channel.bind('pusher:subscription_error', function(status) {
            console.error('Subscription error:', status);
            if (status.type === 'AuthError') {
                console.error('Authentication error - check your Pusher credentials and authentication endpoint');
            }
        });

        // Listen for our custom event
        channel.bind('message', function(data) {
            console.log('Message received:', data);
            if (data && data.message) {
                $('#messages').append('<div class="alert alert-info">' + data.message + ' <small>' + (data.time || '') + '</small></div>');
                $('#messages').scrollTop($('#messages')[0].scrollHeight);
            }
        });
        
        console.log('Pusher and channel binding initialized');

        $('#send-message').click(function() {
            var message = $('#message').val().trim();
            if (message !== '') {
                $.ajax({
                    url: '/send-message',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        message: message
                    },
                    success: function() {
                        $('#message').val('');
                    }
                });
            }
        });

        // Allow sending message with Enter key
        $('#message').keypress(function(e) {
            if (e.which == 13) {
                $('#send-message').click();
                return false;
            }
        });
    </script>
</body>
</html>
