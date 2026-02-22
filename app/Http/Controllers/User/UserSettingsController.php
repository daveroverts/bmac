<?php

namespace App\Http\Controllers\User;

use Illuminate\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\User\UpdateUserSettings;

class UserSettingsController extends Controller
{
    public function edit(): View
    {
        return view('user.settings', ['user' => auth()->user()]);
    }

    public function update(UpdateUserSettings $request): RedirectResponse
    {
        auth()->user()->update($request->validated());
        flashMessage('success', __('Done'), 'Settings saved!');

        return back();
    }
}
