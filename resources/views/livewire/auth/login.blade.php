@extends('layouts.skot_auth')

@section('title', __('Login'))

@section('content')

<section class="login-page-main" >
    <div class="login-wrapper">
       <div class="logo-box-inner">
          <div class="logo-box">
                <img src="{{asset('assets/images/login/logo.png')}}" alt="">
            </div>
            <h1><span>e-Budget</span> Management System</h1>
            <h2>Department of Immigration & Passports <span>Bangladesh</span></h1>
         <div class="login-form-box">
            
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">{{ __('User ID') }}</label>
                    <input type="email" name="email" id="email" placeholder="Type your email/ mobile no./ user id" class="form-control" value="{{ old('email') }}" required autofocus >
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>

                    </div>
                </div>
                <div class="form-group">
                       <label for="password" class="form-label">{{ __(' Password') }}</label>
                    <input type="password" name="password" id="password" placeholder="Type  password" class="form-control" required>
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>

                    </div>
                </div>
                 <div class="form-group">
                     <label for="captcha" class="form-label">
                        {{ __("Type Result of  ") }} <span>
                            {{{$captcha_question}}}
                        </span>
                    
                    </label>
                    <input type="number" name="captcha" id="captcha" placeholder="Type result" class="form-control captcha-control" required>
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M64 64C28.7 64 0 92.7 0 128L0 384c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-256c0-35.3-28.7-64-64-64L64 64zm16 64l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16zM64 240c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32zM176 128l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16zM160 240c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32zm16 80l224 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-224 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16zm80-176c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32zm16 80l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16zm80-80c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32zm16 80l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16zm80-80c0-8.8 7.2-16 16-16l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32zm16 80l32 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-32 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16z"/></svg>
                    </div>
                    {{-- <button type="button" class="btn btn-link try-captcha-btn btn-sm p-0 text-decoration-none" onclick="window.location.reload()">
                      {{ __('Try New Captcha') }}
                    </button> --}}
                    @error('captcha') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <button type="submit" class="btn login-btn">{{ __('Login') }}
                         
                     <i class="btn-arrow-right"></i>

                    </button>
                </div>
                <div class="form-text">
                  <a class="forget-pass" href="{{ route('password.request') }}" wire:navigate>  {{ __('Forget Password ?') }}</a>
                </div>
            </form>
        </div>
       </div>
    </div>
</section>
 

@endsection