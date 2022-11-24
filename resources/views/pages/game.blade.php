@extends('layouts.app')

@section('content')
    <!--TODO add breadcrumbs & add reviews}}-->
    <section class="flex flex-col">
        <section class="flex flex-col md:flex-row mt-8 ml-4">
            <img class="w-15 h-18 lg:w-70 lg:h-100 mr-4 mb-4" src="https://picsum.photos/200/300" alt="Game Image">
            <article class="ml-4 space-y-2">
                <h1 class="text-amber-400 font-semibold text-2xl">{{ $game->title }}</h1>
                <h2 class="text-neutral-50"><span class="text-amber-400 font-semibold text-xl">Release</span>: {{\Carbon\Carbon::parse($game->release_date)->format('d/m/Y')}}</h2>
                <p class="text-neutral-50">Classification</p>
                <p class="text-amber-400 font-semibold">Categories/Category:</p> <!--Usar aquela cena da gramatica que o meu menino mostra no crash course de laravel-->
                <p class="text-amber-400 font-semibold">Publisher: <span class="text-neutral-50 underline font-normal">{{ $game->user->username }}</span></p>
                <section class="flex flex-row mt-6 space-x-4">
                    @auth
                    <form method="post" action="{{ route('addtocart') }}">
                        @csrf
                        <button class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2" type="submit" name="gameid" value="{{ $game->gameid }}"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</button>
                    </form>
                    <form action="#" method="POST">
                        @csrf
                        <button class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2" type="submit"><i class="fa-solid fa-heart"></i> Add to Wishlist</button>
                    </form>
                    @endauth
                    @guest
                        <a href="{{ route('addToCartGuest', $game->gameid) }}" class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</a>
                        <a href="#" class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2"><i class="fa-solid fa-heart"></i> Add to Wishlist</a>
                    @endguest
                </section>
            </article>
            <div>
                <p class="md:ml-16 ml-4 md:mt-8 mt-4 md:bg-amber-400 lg:px-16 lg:py-3 md:px-8 md:py-2 text-neutral-50 font-semibold text-xl">{{$game->price-($game->price*$game->discount)}} &euro;</p>
            </div>
        </section>
        <section class="m-4">
            <h3 class="font-semibold text-neutral-50 text-lg underline">About this Game:</h3>
            <p class="mt-4 text-amber-400 bg-gray-900 p-2 border border-transparent border-solid rounded-md">
                {{ $game->description }}
            </p>
        </section>
        <section class="mr-4 ml-4 mt-4 mb-8">
            <h4 class="font-semibold text-neutral-50 text-lg underline">Comments:</h4>
            @if($game->reviews->count())
                @foreach($game->reviews as $review)
                    <article class="flex flex-col m-4 bg-amber-400 p-2 border border-transparent border-solid rounded-md">
                        <section class="flex flex-row">
                            <img class="w-8 h-8" src="{{ url('image/avatar.png') }}" alt="Avatar image">
                            <p class="ml-2 text-neutral-50 font-bold">{{$review->user->username}}</p>
                            <p class="ml-2 text-neutral-50">Classification (In Stars Representation)</p>
                        </section>
                        <p class="mt-2 text-neutral-50 font-semibold">{{$review->comment}}</p>
                    </article>
                @endforeach
            @else
                <p class="text-center text-amber-400 font-semibold text-lg m-4">There are no comments yet.</p>
            @endif
        </section>
    </section>
@endsection