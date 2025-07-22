<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    // GET /api/users
    public function index()
    {
        return response()->json(User::all());
    }

    // POST /api/users
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'google_id' => 'required|string|unique:users,google_id',
            'avatar' => 'nullable|string',
            'role' => 'required|in:shop_owner,user',
        ]);

        $user = User::create($data);
        return response()->json($user, 201);
    }

    // GET /api/users/{user}
    public function show(User $user)
    {
        return response()->json($user);
    }

    // PUT/PATCH /api/users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'avatar' => 'nullable|string',
        ]);

        $user->update($data);
        return response()->json($user);
    }

    // DELETE /api/users/{user}
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
