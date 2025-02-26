<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function generateToken(Request $request)
    {
        $hardcodedUser = User::find(1);

        if (!$hardcodedUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $token = $hardcodedUser->createToken('registration_token', ['register'], now()->addMinutes(40));

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
        ]);
    }
}
