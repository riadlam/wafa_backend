<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid token payload',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $token = $request->input('token');
        $tokens = $user->fcm_tokens ?? [];

        // Ensure array and uniqueness
        if (!is_array($tokens)) {
            $tokens = [];
        }
        if (!in_array($token, $tokens, true)) {
            $tokens[] = $token;
        }

        $user->fcm_tokens = $tokens;
        $user->save();

        return response()->json([
            'message' => 'FCM token saved',
            'tokens' => $user->fcm_tokens,
        ]);
    }
}


