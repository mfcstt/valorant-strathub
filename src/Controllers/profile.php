<?php

declare(strict_types=1);

use App\Http\ProfileActions;
use App\Models\Favorite;
use App\Models\Strategy;
use App\Models\User;
use App\Support\Auth;

if (!Auth::check()) {
    flash()->put('error', 'Faça login para acessar seu perfil.');
    redirect('/login');
}

$userId = (int) Auth::id();

if (is_post()) {
    (new ProfileActions($userId))->handle((string) ($_POST['action'] ?? 'update_avatar'));

    redirect('/profile');
}

$user = User::find($userId);

if ($user === null) {
    // A conta desapareceu por baixo de uma sessão ainda válida.
    Auth::logout();
    redirect('/login');
}

view('app', [
    'user' => $user,
    'strategies_count' => Strategy::count(['user_id' => $userId]),
    'ratings_count' => User::ratingsCount($userId),
    'favorites_count' => Favorite::countForUser($userId),
], 'profile');
