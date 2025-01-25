@extends('layouts.admin')
@section('content')
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Edit User</h3>
            </div>
            <div class="wg-box">

                <form class="form-new-product form-style-1" action="{{ route('admin.user.update') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $user->id }}">
                    <fieldset class="name">
                        <div class="body-title">Name <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="text" placeholder="Name" name="name" tabindex="0"
                            value="{{ $user->name }}" aria-required="true" required="">
                    </fieldset>
                    @error('name')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                    <fieldset class="name">
                        <div class="body-title">Email <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="text" placeholder="Email" name="email" tabindex="0"
                            value="{{ $user->email }}" aria-required="true" required="">
                    </fieldset>
                    @error('email')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                    <fieldset class="name">
                        <div class="body-title">Mobile <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="text" placeholder="Mobile" name="mobile" tabindex="0"
                            value="{{ $user->mobile }}" aria-required="true" required="">
                    </fieldset>
                    @error('phone')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                    <fieldset class="name">
                        <div class="body-title">Password <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="password" placeholder="New Password" name="password" tabindex="0"
                            value="" aria-required="true">
                    </fieldset>
                    @error('password')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                    <fieldset class="name">
                        <div class="body-title">Confirm Password <span class="tf-color-1">*</span></div>
                        <input class="flex-grow" type="password" placeholder="Password confirm" name="password_confirmation"
                            tabindex="0" value="" aria-required="true">
                    </fieldset>
                    @error('password_confirmation')
                        <span class="alert alert-danger text-center">{{ $message }}</span>
                    @enderror
                    <div class="bot">
                        <div></div>
                        <button class="tf-button w208" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
