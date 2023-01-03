@extends('layouts.app')

@section('content')
    @include('partials.breadcrumbs', $path = array('About Us' => route('aboutpage')))
    <section class="flex flex-col flex-wrap bg-gradient-to-r from-gray-700 to-gray-900 justify-center md:h-screen">
        <section class="flex flex-col flex-wrap justify-center md:flex-row mt-4 mb-6 items-center">
            <article>
                <section class="mb-4">
                    <h1 class="text-amber-400 font-semibold text-md uppercase">What is EasyShopping</h1>
                    <h2 class="text-neutral-50 font-semibold text-lg mt-4">GLOBAL E-COMMERCE</h2>
                    <p class="text-neutral-50 text-lg">We provide world's lowest prices</p>
                </section>
                <a class="border-solid p-2 bg-gray-700 text-neutral-50 hover:bg-amber-400 hover:text-black transition duration-300 ease-in-out" href="{{route('homepage')}}">Start buying</a>
            </article>
            <img class="w-45 h-60" src="{{url('images/aboutuslaptop.png')}}" alt="A Laptop with Easy Shopping Overall View">
        </section>
        <section class="flex flex-wrap flex-col justify-center items-center mx-12 mb-12">
            <h3 class="text-amber-400 font-semibold text-center text-2xl mb-4">We're on a mission to enable everyone to discover the joy of gaming</h3>
            <p class="text-neutral-50 font-normal text-center">Easy Shopping is an online game store that focuses on selling video games that can be on the website. With Easy Shopping buying games is easier than ever, by having cheaper prices than every
                other website you can also have your games delivered instantly as your order is finished. Our platform is provided by gamers to gamers. Therefore, it helps those who can't bear the anxiety
                levels when they want to play a game they are hyped about.
            </p>
        </section>
        <section class="flex flex-col justify-center items-center mx-12 mb-6">
            <h4 class="text-amber-400 font-semibold text-center text-xl mb-4">Our Story</h4>
            <p class="text-neutral-50 font-normal text-center">The founders of Easy Shopping, are avid gamers themselves. Their story began in Portugal,
                in the mid 2000s. Children being children, they spent countless nights playing Counter-Strike, FIFA, CALL OF DUTY, Battlefield,
                F1, NBA, among other simulation and first-person shooter games. Actually, most of the early business ideas came to them while gaming. Fast forward through a couple of companies built and a lot of learning, our founders
                decided to circle back to what sparked entrepreneurship in them - gaming! That’s how we came about.
                Easy Shopping was born in 2022.
            </p>
        </section>
    </section>
@endsection
