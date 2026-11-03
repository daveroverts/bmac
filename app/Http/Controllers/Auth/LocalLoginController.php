<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocalLoginController extends Controller
{
    /**
     * VATSIM sandbox CIDs, so a local login never acts as a real member from a copied database.
     *
     * @var array<string, int>
     */
    private const array ACCOUNT_IDS = ['admin' => 10000001, 'user' => 10000002];

    /**
     * Log in as the dedicated local account for the role, creating it on first use.
     */
    public function __invoke(Request $request, string $role): RedirectResponse
    {
        abort_unless(app()->isLocal(), 404);

        $user = $this->localAccount($role);

        Auth::login($user);
        $request->session()->regenerate();

        return to_route('home');
    }

    private function localAccount(string $role): User
    {
        $id = self::ACCOUNT_IDS[$role];
        $isAdmin = $role === 'admin';

        $user = User::query()->find($id);

        if ($user === null) {
            $user = new User();
            $user->id = $id;
            $user->name_first = 'Local';
            $user->name_last = $isAdmin ? 'Admin' : 'Pilot';
            $user->email = sprintf('local-%s-%d@example.test', $isAdmin ? 'admin' : 'pilot', $id);
        }

        $user->isAdmin = $isAdmin;
        $user->save();

        return $user;
    }
}
