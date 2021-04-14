<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\UpdateUserSettings;

class UserController extends Controller
{
    public function edit()
    {
        return inertia('Settings/Edit',['user' => Auth::user()->get(['airport_view', 'use_monospace_font'])]);
    }

    public function update(UpdateUserSettings $request)
    {
        auth()->user()->update($request->validated());
        return back()->with('sucess', 'Settings updated!');
    }
}
