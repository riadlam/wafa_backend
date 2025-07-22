<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Handle Firebase login with ID token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function firebaseLogin(Request $request)
    {
        $idToken = $request->input('token');
        
        if (!$idToken) {
            return response()->json(['error' => 'Token is required'], 400);
        }

        try {
            // Split the JWT into parts
            $tokenParts = explode('.', $idToken);
            if (count($tokenParts) !== 3) {
                throw new \Exception('Invalid token format');
            }

            // Decode the payload (middle part)
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

            // Extract user data
            $uid = $payload['sub'] ?? null;
            $email = $payload['email'] ?? null;
            $name = $payload['name'] ?? 'User';
            $picture = $payload['picture'] ?? null;

            if (!$email) {
                throw new \Exception('Email not found in token');
            }

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt(Str::random(24)),
                    'email_verified_at' => now(),
                    'google_id' => $uid,
                    'avatar' => $picture
                ]
            );

            // Update user ID if it's different
            if ($user->google_id !== $uid) {
                $user->google_id = $uid;
                $user->save();
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Firebase login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function redirectToGoogle()
    {
        $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        Log::info('Redirecting to Google with URL: ' . $redirectUrl);

        return response()->json(['url' => $redirectUrl]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            Log::info('Handling Google OAuth callback');

            $googleUser = Socialite::driver('google')->stateless()->user();
            Log::info('Google user retrieved', ['email' => $googleUser->getEmail()]);

            $isNewUser = false;
            $user = User::firstOrNew(['email' => $googleUser->getEmail()]);
            
            if (!$user->exists) {
                $isNewUser = true;
                $user->fill([
                    'google_id' => $googleUser->getId(),
                    'name' => $googleUser->getName(),
                    'password' => bcrypt(Str::random(24)),
                    'email_verified_at' => now(),
                ]);
                $user->save();
            } else if ($user->google_id !== $googleUser->getId()) {
                $user->google_id = $googleUser->getId();
                $user->save();
            }

            Log::info($isNewUser ? 'New user created' : 'Existing user logged in', ['user_id' => $user->id]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'is_new_user' => $isNewUser
            ]);

        } catch (\Exception $e) {
            Log::error('Error in handleGoogleCallback', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Failed to authenticate with Google',
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function googleLogin(Request $request)
    {
        Log::info('Starting googleLogin method');

        $accessToken = $request->input('access_token');
        
        if (!$accessToken) {
            return response()->json([
                'error' => 'Access token is required',
            ], 400);
        }

        Log::info('Access token received', ['token' => $accessToken]);

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($accessToken);

            Log::info('Google user from token', [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar()
            ]);

            $isNewUser = false;
            $user = User::firstOrNew(['email' => $googleUser->getEmail()]);
            
            if (!$user->exists) {
                $isNewUser = true;
                $user->fill([
                    'google_id' => $googleUser->getId(),
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(Str::random(24)),
                    'email_verified_at' => now(),
                    'role' => 'user', // Default role
                ]);
                $user->save();
            } else {
                // Update user data if it has changed
                $updateData = [];
                if ($user->google_id !== $googleUser->getId()) {
                    $updateData['google_id'] = $googleUser->getId();
                }
                if ($user->name !== $googleUser->getName()) {
                    $updateData['name'] = $googleUser->getName();
                }
                if ($user->avatar !== $googleUser->getAvatar()) {
                    $updateData['avatar'] = $googleUser->getAvatar();
                }
                
                if (!empty($updateData)) {
                    $user->update($updateData);
                }
            }

            Log::info($isNewUser ? 'New user logged in via token' : 'Existing user logged in via token', 
                     ['user_id' => $user->id]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'is_new_user' => $isNewUser
            ]);

        } catch (\Exception $e) {
            Log::error('Error in googleLogin', [
                'message' => $e->getMessage(),
                'token' => $request->access_token,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Invalid token provided',
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get the authenticated User
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user()
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            
            return response()->json([
                'user' => $user
            ]);
            
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Failed to fetch user data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Failed to logout',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Mark the authenticated user as existed
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsExisted(Request $request)
    {
        try {
            $user = auth('api')->user();
            $user->is_existed = 1;
            $user->save();

            return response()->json([
                'message' => 'User marked as existed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking user as existed', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to mark user as existed'
            ], 500);
        }
    }

    /**
     * Set user as shop owner and set subscription plan
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setupShopOwner(Request $request)
    {
        try {
            $user = auth('api')->user();
            
            $request->validate([
                'plan_type' => 'required|in:free_trial,pro',
                'duration' => 'required_if:plan_type,pro|in:1,3,12|nullable|integer',
            ]);

            $user->role = 'shop_owner';
            $now = now();
            
            if ($request->plan_type === 'free_trial') {
                $user->trial_ends_at = $now->copy()->addDays(30);
                $user->plan = 'free';
                $message = 'Free trial activated for 30 days';
            } else {
                // For pro plans
                $months = (int)$request->duration;
                $daysToAdd = $months * 30; // Assuming 30 days per month
                $user->pro_ends_at = $now->copy()->addDays($daysToAdd);
                $user->plan = 'pro';
                $message = "Pro plan activated for $months months";
            }
            
            $user->save();

            return response()->json([
                'message' => 'Successfully set as shop owner. ' . $message,
                'user' => $user->only(['id', 'name', 'email', 'role', 'plan', 'trial_ends_at', 'pro_ends_at'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error setting up shop owner: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to set up shop owner subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Update the authenticated user's name
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateName(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            $user->name = $request->name;
            $user->save();

            return response()->json([
                'message' => 'Name updated successfully',
                'user' => $user->only(['id', 'name', 'email'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating user name', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update name',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the authenticated user's account
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteAccount()
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            // Log the user out
            auth('api')->logout();

            // Delete the user
            $user->delete();

            return response()->json([
                'message' => 'Account successfully deleted'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting user account', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to delete account',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
