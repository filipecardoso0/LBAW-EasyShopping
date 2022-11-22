@extends('layouts.app')

@section('content')
    <!-- Categories -->
    <section class="flex flex-col flex-wrap items-center bg-gray-800 p-6">
        <p class="text-neutral-50 font-semibold text-2xl">Categories</p>
        @if($categories->count())
        <!-- Category List -->
        <section class="flex flex-col md:flex-row items-center justify-center mt-4">
            <!-- Category -->
            @foreach($categories as $category)
                <article class="p-2 bg-amber-400 border-solid rounded-md text-neutral-50 flex flex-col items-center mx-3 my-2 text-sm">
                    <p>Category Icon/Image</p> <!-- Do this later -->
                    <p>{{ $category->name }}</p>
                </article>
            @endforeach
        </section>
        <a href="{{ route('categories') }}" class="border border-solid rounded-full mt-4 p-2 border-transparent bg-gray-900 text-neutral-50 hover:bg-amber-400 transition duration-300 ease-in-out">Show More</a>
        @else
            <p class="text-amber-400 items-center mt-4">There are no categories at the moment!</p>
        @endif
    </section>

    <!-- Best Sellers-->
    <section class="flex flex-col items-center mt-4 bg-gray-900 p-6">
        <p class="text-neutral-50 font-semibold text-2xl"><i class="fa-sharp fa-solid fa-fire-flame-curved text-amber-400"></i> Best Sellers</p>
        @if($bestsellers->count())
        <!-- Game List -->
        <section class="flex flex-col gap-4 justify-center flex-wrap md:flex-row mt-6">
            <!-- Game -->
            @foreach($bestsellers as $bestseller)
                <article class="hover:brightness-50 transition duration-300 ease-in-out bg-gray-700 border-2 rounded-lg border-transparent">
                    <!-- Game Image (Do this later) -->
                    <a href="{{ route('game', $bestseller->gameid) }}"><img class="w-50 h-70 border-2 rounded-lg border-transparent" src="https://picsum.photos/200/300" alt="Game Cover Image"></a>
                    <!-- Game Info -->
                    <section class="p-2">
                        <a href="{{ route('game', $bestseller->gameid) }}">
                            <p class="text-amber-400 text-center">
                                @if(strlen($bestseller->title) > 20)
                                    {{substr($bestseller->title, 0,  20) . '...'}}
                                @else
                                    {{substr($bestseller->title, 0,  20)}}
                                @endif
                            </p>
                        </a>
                        <a href="{{ route('game', $bestseller->gameid) }}"><p class="text-neutral-50 text-center">Price: <span class="text-amber-400">{{$bestseller->price-($bestseller->price*$bestseller->discount)}}&euro;</span></p></a>
                    </section>
                </article>
            @endforeach
        </section>
        <a href="{{route('bestsellers')}}" class="border border-solid rounded-full mt-4 p-2 border-transparent bg-gray-600 text-neutral-50 hover:bg-amber-400 transition duration-300 ease-in-out">Show More</a>
        @else
            <p class="text-amber-400 items-center mt-4">There are no Best Sellers at the moment!</p>
        @endif
    </section>

        <!-- Coming Soon Games -->
        <section class="flex flex-col flex-wrap items-center bg-gray-800 p-6 mb-4">
            <p class="text-neutral-50 font-semibold text-2xl">Coming Soon ... <i class="fa-solid fa-hourglass-end text-amber-400"></i></p>
            @if($gamesoons->count())
            <!-- Game List -->
            <section class="flex flex-col gap-4 justify-center flex-wrap md:flex-row mt-6">
                <!-- Game -->
                @foreach($gamesoons as $gamesoon)
                    <article class="hover:brightness-50 transition duration-300 ease-in-out bg-gray-700 border-2 rounded-lg border-transparent">
                        <a href="{{ route('game', $gamesoon->gameid) }}"><img class="w-50 h-70 border-2 rounded-lg border-transparent" src="https://picsum.photos/200/300" alt="Game Cover Image"></a>
                        <section class="p-2">
                            <a href="{{ route('game', $gamesoon->gameid) }}">
                                <p class="text-amber-400 text-center">
                                    @if(strlen($gamesoon->title) > 20)
                                        {{substr($gamesoon->title, 0,  20) . '...'}}
                                    @else
                                        {{substr($gamesoon->title, 0,  20)}}
                                    @endif
                                </p>
                            </a>
                            <a href="{{ route('game', $gamesoon->gameid) }}"><p class="text-neutral-50 text-center">Price: <span class="text-amber-400">{{$gamesoon->price}} &euro;</span></p></a>
                        </section>
                    </article>
                @endforeach
            </section>
            <a href="{{route('comingsoon')}}" class="border border-solid rounded-full mt-4 p-2 border-transparent bg-gray-900 text-neutral-50 hover:bg-amber-400 transition duration-300 ease-in-out">Show More</a>
            @else
                <p class="text-amber-400 items-center mt-4">There are no games coming soon at the moment!</p>
            @endif
        </section>

        <!-- View All Games -->
        <section class="flex flex-col flex-wrap items-center bg-gray-900 p-6 mb-4">
            <p class="text-neutral-50 font-semibold text-2xl">Still haven't found what you are looking for?</p>
            <a href="{{route('viewallgames')}}" class="border border-solid rounded-full mt-4 p-2 border-transparent bg-gray-600 text-neutral-50 hover:bg-amber-400 transition duration-300 ease-in-out">View all games</a>
        </section>
@endsection
