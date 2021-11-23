<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash; 
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
class AuthController extends Controller
{
    public function register(Request $req)
    {
        $user = new User;
        if(($req->name=="" || $req->email=="")|| $req->password=="")
        {
            return 3;
        }
        else
        {
            $data =  $user->where(['email'=>$req->email])->count();
            if($data > 0)
            {
                return 2;
            }
            else
            {
                $user->name = $req->name;
                $user->email= $req->email;
                $user->password = Hash::make($req->password);
                $result = $user->save();
                if($result)
                {
                    return 1;
                }
                else
                {
                    return 0;
                }
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
