@extends('layouts.app')

@section('content')

<form class="flex flex-col items-center justify-center h-screen text-neutral-50 bg-gray-800 text-md font-semibold" method="post" action="{{ route('login') }}">
    @csrf

    <!-- Error check -->
    @if ($errors->has('email'))
    <span class="mb-4 text-amber-400 text-xl">
        {{ $errors->first('email') }}
    </span>
    @endif

    @if ($errors->has('password'))
    <span class="mb-4 text-amber-400 text-xl">
        {{ $errors->first('password') }}
    </span>
    @endif

    <section class="flex flex-col mb-5">
        <p class="text-xl text-center mb-4">LOGIN</p>
        <p class="text-xl text-center">New User?<span><a class="text-amber-400 ml-1" href="{{ route('register') }}">Create an account</a></span></p>
    </section>
    <input class="text-black mt-4 p-2 rounded-lg focus:outline-none focus:border-amber-400 focus:border-2" name="email" type="email" placeholder="Email" required value="{{ old('email') }}" autofocus>
    <input class="text-black mt-4 p-2 rounded-lg focus:outline-none focus:border-amber-400 focus:border-2" name="password" type="password" placeholder="Password" required>
    <label>
        <input class="mt-5" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
    </label>
    <button class="bg-amber-400 rounded-md border-none px-5 py-3 text-black mt-5 hover:text-neutral-50" type="submit">Sign-In</button>
</form>

@endsection
