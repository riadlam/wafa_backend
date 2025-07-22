# Flutter WebSocket Connection Example

Here's how to connect your Flutter app to the Laravel Reverb server:

## 1. Add Dependencies

Add these to your `pubspec.yaml`:

```yaml
dependencies:
  web_socket_channel: ^2.4.5
  flutter_dotenv: ^5.1.0
  intl: ^0.19.0
  jwt_decoder: ^2.0.1
```

## 2. Create a WebSocket Service

Create a new file `lib/services/websocket_service.dart`:

```dart
import 'dart:async';
import 'dart:convert';
import 'package:web_socket_channel/web_socket_channel.dart';
import 'package:jwt_decoder/jwt_decoder.dart';

class WebSocketService {
  static final WebSocketService _instance = WebSocketService._internal();
  factory WebSocketService() => _instance;
  WebSocketService._internal();

  WebSocketChannel? _channel;
  String? _token;
  String? _userId;
  final String _host = '192.168.1.8';
  final int _port = 8080;
  final String _appKey = 'raglpr6qnmqedcyakkvv';

  // Callback for when a message is received
  Function(dynamic)? onMessage;

  // Initialize the WebSocket connection
  void initialize(String token) {
    _token = token;
    _userId = _getUserIdFromToken(token);
    _connect();
  }

  // Get user ID from JWT token
  String? _getUserIdFromToken(String token) {
    try {
      final decodedToken = JwtDecoder.decode(token);
      return decodedToken['sub'] ?? decodedToken['id'];
    } catch (e) {
      print('Error decoding token: $e');
      return null;
    }
  }

  // Connect to WebSocket server
  void _connect() {
    if (_userId == null) {
      print('Cannot connect: User ID is null');
      return;
    }

    try {
      // Close existing connection if any
      _channel?.sink.close();

      // Create new connection
      _channel = WebSocketChannel.connect(
        Uri.parse('ws://$_host:$_port/app/$_appKey?protocol=7&client=flutter&version=1.0.0'),
      );

      // Listen for messages
      _channel?.stream.listen(
        (message) {
          print('WebSocket message: $message');
          if (onMessage != null) {
            onMessage!(jsonDecode(message));
          }
        },
        onError: (error) {
          print('WebSocket error: $error');
          // Attempt to reconnect after a delay
          Future.delayed(const Duration(seconds: 5), _connect);
        },
        onDone: () {
          print('WebSocket connection closed');
          // Attempt to reconnect after a delay
          Future.delayed(const Duration(seconds: 5), _connect);
        },
      );

      // Subscribe to private channel
      _subscribeToPrivateChannel();
    } catch (e) {
      print('WebSocket connection error: $e');
      // Attempt to reconnect after a delay
      Future.delayed(const Duration(seconds: 5), _connect);
    }
  }


  // Subscribe to private channel
  void _subscribeToPrivateChannel() {
    if (_userId == null || _channel == null) return;

    final subscribeMessage = {
      'event': 'subscribe',
      'data': {
        'channel': 'private-user.$_userId',
        'auth': {
          'headers': {
            'Authorization': 'Bearer $_token',
          },
        },
      },
    };

    _channel?.sink.add(jsonEncode(subscribeMessage));
  }

  // Close the WebSocket connection
  void dispose() {
    _channel?.sink.close();
  }
}

// Global instance
final webSocketService = WebSocketService();
```

## 3. Initialize in Your App

In your main.dart or a service provider:

```dart
import 'package:flutter/material.dart';
import 'services/websocket_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Initialize WebSocket when user logs in
  // webSocketService.initialize(userToken);
  
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Loyalty App',
      home: WebSocketListener(),
    );
  }
}

class WebSocketListener extends StatefulWidget {
  @override
  _WebSocketListenerState createState() => _WebSocketListenerState();
}

class _WebSocketListenerState extends State<WebSocketListener> {
  @override
  void initState() {
    super.initState();
    
    // Listen for WebSocket messages
    webSocketService.onMessage = (message) {
      if (message['event'] == 'stamp.updated') {
        final data = message['data'];
        // Handle stamp update
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Stamp updated! Current: ${data['current_stamps']}'))
        );
      }
    };
  }

  @override
  void dispose() {
    webSocketService.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Loyalty App')),
      body: Center(
        child: Text('Listening for stamp updates...'),
      ),
    );
  }
}
```

## 4. Handling Stamp Updates

When a stamp is updated in your Laravel backend, the Flutter app will receive a message like this:

```json
{
  "event": "stamp.updated",
  "data": {
    "user_loyalty_card": {
      "id": 1,
      "user_id": 1,
      "loyalty_card_id": 1,
      "active_stamps": 1,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "loyalty_card": {
        "id": 1,
        "shop_id": 1,
        "name": "Coffee Lovers",
        "description": "Get a free coffee after 10 stamps",
        "total_stamps": 10,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
      }
    },
    "current_stamps": 1,
    "total_stamps_needed": 10,
    "stamp_reset": false,
    "shop": {
      "id": 1,
      "name": "Coffee Shop",
      "email": "coffee@example.com"
    },
    "timestamp": "2023-01-01 12:00:00"
  }
}
```

## 5. Testing the Connection

1. Start your Laravel Reverb server:
   ```bash
   php artisan reverb:start
   ```

2. Run your Flutter app and log in to initialize the WebSocket connection.

3. Use the `processQrScan` endpoint to trigger a stamp update and verify the Flutter app receives the update.

## Troubleshooting

1. **Connection Issues**:
   - Verify the server is running and accessible from your Flutter device/emulator
   - Check the WebSocket URL and port
   - Ensure CORS is properly configured

2. **Authentication Issues**:
   - Verify the JWT token is valid and not expired
   - Check that the token includes the user ID in the 'sub' or 'id' claim

3. **Subscription Issues**:
   - Verify the channel name matches between Laravel and Flutter
   - Check the WebSocket messages in the browser's developer tools

4. **Debugging**:
   - Enable debug logging in your Flutter app
   - Check Laravel logs for any errors
   - Use a WebSocket client like Postman to test the connection manually
