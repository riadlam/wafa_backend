<?php

use Illuminate\Support\Facades\Route;
use App\Events\MessageSent;
use Illuminate\Http\Request;

// Test Pusher page
Route::get('/test-pusher', function () {
    return view('test-pusher');
});

// Welcome page with chat
Route::get('/', function () {
    return view('chat');
});

// Send message route
Route::post('/send-message', function (Request $request) {
    event(new MessageSent($request->input('message')));
    return ['status' => 'Message Sent!'];
});
