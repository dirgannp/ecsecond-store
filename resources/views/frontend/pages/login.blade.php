@extends('frontend.layouts.master')

@section('title','ECSECOND || Login Page')

@section('main-content')
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0);">Login</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->
            
    <!-- Shop Login -->
    <section class="shop login section">
        <div class="container">
            <div class="row"> 
                <div class="col-lg-6 offset-lg-3 col-12">
                    <div class="login-form">
                        <h2>Login</h2>
                        <p>Please register in order to checkout more quickly</p>
                        <!-- Form -->
                        <form class="form" method="post" action="{{route('login.submit')}}">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Your Email<span>*</span></label>
                                        <input
                                            type="email"
                                            name="email"
                                            placeholder=""
                                            required="required"
                                            value="{{old('email')}}"
                                            class="@error('email') is-invalid @enderror"
                                            oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Email harus diisi.' : 'Format email tidak valid.')"
                                            oninput="this.setCustomValidity('')"
                                        >
                                        @error('email')
                                            <span class="text-danger form-error">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Your Password<span>*</span></label>
                                        <input
                                            type="password"
                                            name="password"
                                            placeholder=""
                                            required="required"
                                            class="@error('password') is-invalid @enderror"
                                            oninvalid="this.setCustomValidity('Password harus diisi.')"
                                            oninput="this.setCustomValidity('')"
                                        >
                                        @error('password')
                                            <span class="text-danger form-error">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Captcha: {{$captchaQuestion}} = ?<span>*</span></label>
                                        <input
                                            type="number"
                                            name="captcha_answer"
                                            placeholder="Enter captcha answer"
                                            required="required"
                                            class="@error('captcha_answer') is-invalid @enderror"
                                            oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Captcha harus diisi.' : 'Captcha harus berupa angka.')"
                                            oninput="this.setCustomValidity('')"
                                        >
                                        @error('captcha_answer')
                                            <span class="text-danger form-error">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group login-btn">
                                        <button class="btn" type="submit">Login</button>
                                        <a href="{{route('register.form')}}" class="btn">Register</a>
                                        OR
                                        <a href="{{route('login.redirect','facebook')}}" class="btn btn-facebook"><i class="ti-facebook"></i></a>
                                        <a href="{{route('login.redirect','github')}}" class="btn btn-github"><i class="ti-github"></i></a>
                                        <a href="{{route('login.redirect','google')}}" class="btn btn-google"><i class="ti-google"></i></a>

                                    </div>
                                    <div class="checkbox">
                                        <label class="checkbox-inline" for="2"><input name="news" id="2" type="checkbox">Remember me</label>
                                    </div>
                                    @if (Route::has('password.request'))
                                        <a class="lost-pass" href="{{ route('password.request') }}">
                                            Lost your password?
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        <!--/ End Form -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--/ End Login -->
@endsection
@push('styles')
<style>
    .shop.login .form .btn{
        margin-right:0;
    }
    .btn-facebook{
        background:#39579A;
    }
    .btn-facebook:hover{
        background:#073088 !important;
    }
    .btn-github{
        background:#444444;
        color:white;
    }
    .btn-github:hover{
        background:black !important;
    }
    .btn-google{
        background:#ea4335;
        color:white;
    }
    .btn-google:hover{
        background:rgb(243, 26, 26) !important;
    }
    .shop.login .form input.is-invalid{
        border:1px solid #dc3545;
    }
    .shop.login .form .form-error{
        display:block;
        font-size:14px;
        margin-top:6px;
    }
</style>
@endpush
