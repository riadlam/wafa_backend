<?php

use Illuminate\Support\Facades\Broadcast;

// Default user model channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private user channel for stamp updates
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});


Broadcast::channel('chat', function ($user) {
    return true;
});
