@extends('layouts.app')

@section('content')
    <form class="flex flex-col items-center justify-center h-screen text-neutral-50 bg-gray-800 text-md font-semibold" method="post" action="{{ route('admincreateuseraccount') }}">
        @csrf

        <!-- Errors -->
        @if ($errors->has('username'))
            <span class="mb-4 text-amber-400 text-xl">
          {{ $errors->first('username') }}
      </span>
        @endif

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
            <p class="text-xl text-center mb-4">ADMIN</p>
            <p class="text-xl text-center">Create new User Account</p>
        </section>

        <input name="username" value="{{ old('username') }}" class="text-black mt-4 p-2 rounded-lg focus:outline-none focus:border-amber-400 focus:border-2" type="text" placeholder="Username" required autofocus>
        <input name="email" value="{{ old('email') }}" class="text-black mt-4 p-2 rounded-lg focus:outline-none focus:border-amber-400 focus:border-2" type="email" placeholder="Email Address" required>
        <input name="password" class="text-black mt-4 p-2 rounded-lg focus:outline-none focus:border-amber-400 focus:border-2" type="password" placeholder="Password" required>
        <input name="password_confirmation" class="text-black mt-4 p-2 rounded-lg focus:outline-none focus:border-amber-400 focus:border-2" type="password" placeholder="Confirm Password" required>

        <button class="bg-amber-400 rounded-md border-none px-5 py-3 text-black mt-5 hover:text-neutral-50" type="submit">Create New User</button>
    </form>
@endsection
