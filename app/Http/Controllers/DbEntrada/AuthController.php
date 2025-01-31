<?php

namespace App\Http\Controllers\DbEntrada;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request){
        
        $data = $request->validate([
            'user_name'=> 'required',
            'password' => 'required'
        ]);

        if(Auth::attempt($data)){
            $user = Auth::user();
            
            if($user->hasRole('Administrador')){
                return view('pages.entrance.admin_entrance');
            }else if($user->hasRole('Aprendiz')){
                return view('pages.entrance.aprentice_entrance');
            }           

        }else{
            return "Y vos quien sos?";
        }
    }

    public function logout(Request $request){
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
  
}
