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
        $validator = Validator::make($request->all(),[ 
            'email' => 'required|email',
            'password' => 'required', 
        ]);
        if($validator->fails())
        {
            return response()->json([
                'validation_errors' =>$validator->messages(),
            ]);
        }
        else
        {
            $user = User::where('email', $request->email)->first();
            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([ 
                    'status' => 401, 
                    'message'=>'Invalid Credentials', 
                ]);
            }
            else
            {
                $token = $user->createToken($user->email.'_Token')->plainTextToken; 
               // $cookie = cookie('jwt', $token, 60 * 24); // 1 day
                return response()->json([
                    'status' => 200,
                    'username'=> $user->name,
                    'token'=>$token,
                    'message'=>'Logged In Successfully',
                ]);
            }  
        } 
    }
    public function user()
    {
        return Auth::user();
    }
    public function logout()
    { 
        auth()->user()->tokens()->delete();

        return response([
            'status' => 200,
            'message' => 'Successfully Logged out'
        ]);
    }
}
