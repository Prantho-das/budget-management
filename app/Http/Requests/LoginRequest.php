<?php

namespace App\Http\Requests;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            Fortify::username() => 'required|string',
            'password' => 'required|string',
            'captcha' => 'required|numeric',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $sessionCaptcha = session('captcha_answer');
            $inputCaptcha = $this->input('captcha');

            if (!$sessionCaptcha || $inputCaptcha != $sessionCaptcha) {
                $validator->errors()->add('captcha', __('Incorrect Captcha answer. Please try again.'));
            }
        });
    }
}
