<?php

namespace App\Http\Controllers\User;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\User\UpdateUserSettings;

class UserController extends Controller
{
    public function showSettingsForm(): View
    {
        return view('user.settings', ['user' => auth()->user()]);
    }

    public function saveSettings(UpdateUserSettings $request): RedirectResponse
    {
        auth()->user()->update($request->validated());
        flashMessage('success', __('Done'), 'Settings saved!');
        return back();
    }
}
