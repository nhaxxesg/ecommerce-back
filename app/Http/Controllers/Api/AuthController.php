<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RucValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $rucValidationService;

    public function __construct(RucValidationService $rucValidationService)
    {
        $this->rucValidationService = $rucValidationService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:client,owner',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'ruc' => $request->role === 'owner' ? 'required|string|size:11' : 'nullable|string|size:11',
        ]);

        try {
            // Si es propietario, validar el RUC
            if ($request->role === 'owner' && $request->ruc) {
                $this->rucValidationService->validateRuc($request->ruc);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 201);

        } catch (\Exception $e) {
            if ($request->role === 'owner') {
                throw ValidationException::withMessages([
                    'ruc' => ['Error al validar el RUC: ' . $e->getMessage()],
                ]);
            }
            throw $e;
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
} 