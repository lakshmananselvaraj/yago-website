<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\ClientProfile;
use App\Models\User;

final class OnboardingController extends Controller
{
    public function page(Request $request): void
    {
        $profile = ClientProfile::findByUserId(Auth::id());
        $profile = $profile ? ClientProfile::hydrate($profile) : null;

        $this->view('onboarding/profile-wizard', [
            'profile' => $profile,
            'user' => User::find(Auth::id()),
        ], 'dashboard');
    }
}
