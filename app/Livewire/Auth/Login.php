<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public $email;
    public $password;
    public $captcha;
    public $captcha_question;

    public function mount()
    {
        if (Auth::check()) {
            return $this->redirect(route('dashboard'), navigate: true);
        }
        $this->generateCaptcha();
    }

    public function generateCaptcha()
    {
        $n1 = rand(1, 10);
        $n2 = rand(1, 10);
        session()->put('captcha_answer', $n1 + $n2);
        $this->captcha_question = "$n1 + $n2 = ?";
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
            'captcha' => 'required|numeric',
        ]);

        $sessionCaptcha = session('captcha_answer');
        if (!$sessionCaptcha || $this->captcha != $sessionCaptcha) {
            $this->addError('captcha', __('Incorrect Captcha answer. Please try again.'));
            $this->generateCaptcha();
            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            Session::regenerate();
            return $this->redirectIntended(route('dashboard'), navigate: true);
        }

        $this->addError('email', __('auth.failed'));
        $this->generateCaptcha();
    }

    public function render()
    {
        return view('livewire.auth.login-component')
            ->layout('layouts.skot_auth')
            ->section('content');
    }
}
