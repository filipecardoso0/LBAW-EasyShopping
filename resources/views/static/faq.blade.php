@extends('layouts.app')

@section('content')
    @include('partials.breadcrumbs', $path = array('FAQ' => route('faqpage')))
    <section class="flex flex-col items-center justify-center h-screen bg-gradient-to-r from-gray-700 to-gray-900">
        <h1 class="text-center mb-24 text-2xl md:text-5xl text-neutral-50 font-semibold lg:text-2xl">What do you need help with?</h1>
        <section class="flex flex-col flex-wrap md:flex-row md:space-x-4">
            <a href="{{route('faqgames')}}">
                <section class="px-24 py-12 md:px-36 md:py-16 text-center bg-gray-700 transition duration-150 hover:bg-gray-900 ease-in-out">
                    <i class="text-5xl fa-solid fa-gamepad text-amber-400"></i>
                    <p class="mt-4 text-neutral-50 text-3xl font-semibold">Games</p>
                </section>
            </a>
            <a href="{{route('faqaccount')}}">
                <section class="px-24 py-12 md:px-36 md:py-16 text-center bg-gray-700 transition duration-150 hover:bg-gray-900 ease-in-out mt-4 md:mt-0">
                    <i class="text-5xl fa-solid fa-user text-amber-400"></i>
                    <p class="mt-4 text-neutral-50 text-3xl font-semibold">Account</p>
                </section>
            </a>
        </section>
    </section>
@endsection
