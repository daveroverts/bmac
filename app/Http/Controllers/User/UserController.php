<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserSettings;

class UserController extends Controller
{
    public function showSettingsForm()
    {
        return view('user.settings', ['user' => auth()->user()]);
    }

    public function saveSettings(UpdateUserSettings $request)
    {
        auth()->user()->update($request->validated());
        flashMessage('success', __('Done'), 'Settings saved!');
        return back();
    }
}
