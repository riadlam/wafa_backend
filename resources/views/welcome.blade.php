<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel WebSocket Test</title>

    <!-- Pusher JavaScript -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <script>
        // Enable logging for debugging
        Pusher.logToConsole = true;

        // Initialize Pusher with your credentials
        var pusher = new Pusher("7e8fce68170007f551f3", {
            cluster: "eu",
            encrypted: true
        });

        // Subscribe to the 'chat' channel
        var channel = pusher.subscribe('chat');

        // Bind to the 'message.sent' event
        channel.bind('message.sent', function(data) {
            alert('Received: ' + data.message);
            console.log('Message received:', data);
        });
    </script>
</head>
<body>
    <h1>Testing WebSockets</h1>
</body>
</html>
