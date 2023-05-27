<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PassportAuthController extends Controller
{
    private function validateRequest(){
        if(empty(auth()->user()))
            return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function register(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name'     => 'required|min:4',
            'email'    => 'required|email',
            'password' => 'required|min:4'
        ]);

        if($user = User::where(['email' => $request->email])->first())
            return response()->json(['message' => 'User already exists'], 400);

        try{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
    
            $token = $user->createToken('LaravelAuthApp')->accessToken;
    
            return response()->json(['user' => $user, 'token' => $token], 200);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if(auth()->attempt($data)){
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['user' => auth()->user(), 'token' => $token], 200);
        }

        return response()->json(['error' => 'Unauthorized']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->validateRequest();
        try{
            $token = $request->user()->token();
            $token->revoke();
            return response()->json(['message' => 'You have been successfully logged out'], 200);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    public function show():JsonResponse
    {
        $this->validateRequest();

        return response()->json(['user' => auth()->user()], 200);
    }

    public function resetPassword(Request $request):JsonResponse
    {
        $this->validateRequest();

        $this->validate($request, [
            'password' => 'required|min:8',
        ]);

        try{
            if(!$user = User::where(['id' => auth()->user()->id])->first()){
                return response()->json(['message' => 'This user is not exists'. 400]);
            }
    
            $user->password = bcrypt($request->password);
            $user->save();
    
            return response()->json(['message' => 'Successed user updated'], 200);
        } catch(\Throwable $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
