<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function loginAs(User $user): RedirectResponse
    {
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
