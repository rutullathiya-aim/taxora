<?php

namespace App\Livewire\Auth;

use App\Enums\UserStatus;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class AcceptInvitation extends Component
{
    public string $token;

    public string $password = '';

    public string $password_confirmation = '';

    public ?UserInvitation $invitation = null;

    public function mount(string $token)
    {
        $this->token = $token;
        $this->invitation = UserInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $this->invitation) {
            abort(403, 'This invitation link is invalid or has expired.');
        }
    }

    public function save()
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->invitation->user;

        $user->update([
            'password' => $this->password,
            'status' => UserStatus::Active,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        $this->invitation->update([
            'accepted_at' => now(),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.accept-invitation');
    }
}
