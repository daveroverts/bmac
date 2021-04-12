<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserSettings;

class UserController extends Controller
{
    public function edit()
    {
        return view('user.settings', ['user' => auth()->user()]);
    }

    public function update(UpdateUserSettings $request)
    {
        auth()->user()->update($request->validated());
        return back()->with('sucess', 'Settings updated!');
    }
}
