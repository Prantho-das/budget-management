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
            <form action="">
                <div class="form-group">
                    <label for="email" class="form-label">Enter Email Address</label>
                    <input type="email" placeholder="Enter Your E-mail" class="form-control">
                </div>
                <div class="form-group">
                       <label for="email" class="form-label">Enter Password </label>
                    <input type="password" placeholder="Enter Your Password" class="form-control">
                </div>
                 <div class="form-group">
                    <label for="email" class="form-label">Enter The Text Displayed Bellow </label>
                    <input type="text" placeholder="Enter Your Captcha" class="form-control">
                </div>
                <div class="form-group">
                    <button class="btn login-btn">Login
                         
                     <i class="btn-arrow-right"></i>

                    </button>
                </div>
                <div class="form-text">
                  <a href="#">  Forget Password ?</a>
                </div>
            </form>
        </div>
    </div>
</section>
 

@endsection