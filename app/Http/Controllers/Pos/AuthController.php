<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('pos.auth.login');
    }
}
