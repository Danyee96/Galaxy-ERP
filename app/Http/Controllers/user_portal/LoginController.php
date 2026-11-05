<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class LoginController extends Controller
{
    public function getLogin()
    {
        return view('user_portal.login');
    }

    public function userLogin(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::where('email',$request->email)->first();
        if(!empty($user))
        {
            if(!Hash::check($request->password, $user->password))
            {
                return back()->withErrors([
                    'password' => ['In-Correct Password']
                ]);
            }
            $credentials = $request->only('email','password');
            if (Auth::attempt($credentials)) {
                return redirect()->route('index');
            }else{
                return back()->withErrors([
                    'email' => ['Please Check Your credentials.']
                ]);
            }
        }
        else
        {
            return back()->withErrors([
                'email' => ['In-Correct E-mail']
            ]);
        }
    }
}
