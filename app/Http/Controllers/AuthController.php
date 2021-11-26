<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Validator; 
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
class AuthController extends Controller
{
    public function register(Request $request)
    { 
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:100',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|min:6', 
        ]);
        if($validator->fails())
        {
            return response()->json([
                'validation_errors' =>$validator->messages(),
            ]);
        }
        else
        {
            if($request->status==false)
            {
                return response()->json([
                    'status' => 201,
                    'message'=>'Please accept the terms and Condition!',
                ]);
            }
            else
            {
                $user=User::create([
                    'name'=>$request->name,
                    'email'=>$request->email,
                    'password'=>Hash::make($request->password),
    
                ]); 
                $token = $user->createToken($user->email.'_Token')->plainTextToken; 
                return response()->json([
                    'status' => 200,
                    'username'=> $user->name,
                    'token'=>$token,
                    'message'=>'Registered Successfully',
                ]);
            }
           
        }
         
    }
    public function login(Request $request)
    {
        if(!Auth::attempt($request->only('email','password')))
        {
            return response([
                'message' =>'Invalid Credentials'
            ],Response::HTTP_UNAUTHORIZED);
        }
        $user = Auth::user();
        $token = $user->createToken('token')->plainTextToken; 
        $cookie = cookie('jwt', $token, 60 * 24); // 1 day
        $user= Auth::user();
        return response([
            'message' =>  'success',
            'name' =>  $user->name,
            'authid' =>  $user->id,
        ])->withCookie($cookie);
    }
    public function user()
    {
        return Auth::user();
    }
    public function logout()
    {
        $cookie = Cookie::forget('jwt');

        return response([
            'message' => 'Success'
        ])->withCookie($cookie);
    }
}
