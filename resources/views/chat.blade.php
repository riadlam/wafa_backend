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
                    <div class="card-header">Pusher Test</div>
                    <div class="card-body">
                        <div id="messages" class="mb-3" style="height: 300px; overflow-y: scroll; border: 1px solid #ddd; padding: 10px;">
                            <!-- Messages will appear here -->
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

    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            encrypted: true
        });

        var channel = pusher.subscribe('chat');
        channel.bind('message', function(data) {
            $('#messages').append('<div class="alert alert-info">' + data.message + '</div>');
            $('#messages').scrollTop($('#messages')[0].scrollHeight);
        });

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
