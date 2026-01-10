@extends('layouts.skot_auth')

@section('title', __('Login'))

@section('content')

<section class="login-page-main">
    <div class="login-wrapper">
        <div class="login-logo-box">
            Lorem ipsum dolor sit amet consectetur, adipisicing elit. Autem quasi quidem distinctio numquam exercitationem. In voluptates ab totam, sit omnis minima veritatis facilis. Obcaecati sunt iusto culpa eius, reprehenderit commodi!
        </div>
         <div class="login-form-box">
            <h1>Login</h1>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">{{ __('Enter Email Address') }}</label>
                    <input type="email" name="email" id="email" placeholder="Enter Your E-mail" class="form-control" value="{{ old('email') }}" required autofocus>
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                       <label for="password" class="form-label">{{ __('Enter Password') }}</label>
                    <input type="password" name="password" id="password" placeholder="Enter Your Password" class="form-control" required>
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                 <div class="form-group">
                     <label for="captcha" class="form-label">{{ __('Enter Result of 8 + 8 ') }}</label>
                    <input type="number" name="captcha" id="captcha" placeholder="Result?" class="form-control" required>
                     <label for="captcha" class="form-label captcha-label">{{ $captcha_question ?? 'Captcha' }}</label>
                    @error('captcha') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <button type="submit" class="btn login-btn">{{ __('Login') }}
                         
                     <i class="btn-arrow-right"></i>

                    </button>
                </div>
                <div class="form-text">
                  <a href="{{ route('password.request') }}">  {{ __('Forget Password ?') }}</a>
                </div>
            </form>
        </div>
    </div>
</section>
 

@endsection