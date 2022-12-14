@extends('layouts.app')

@section('content')
    <!-- Carousel With the Latest Launched Games -->
    <section class="flex flex-col flex-wrap">
        <p class="text-neutral-50 font-semibold text-2xl text-center mt-4">Latest Releases <i class="fa-solid fa-bolt text-amber-400"></i> </p>
        <div class="flex items-center justify-center w-full h-full py-24 sm:py-8 px-4">
            <div class="w-full relative flex items-center justify-center">
                <button aria-label="slide backward" class="absolute z-30 left-0 ml-10 focus:outline-none focus:bg-gray-400 focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 cursor-pointer" id="prev">
                    <svg class="dark:text-gray-900" width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path class="text-neutral-50" d="M7 1L1 7L7 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="w-full h-full mx-auto overflow-x-hidden overflow-y-hidden">
                    <div id="slider" class="h-full flex lg:gap-8 md:gap-6 gap-14 items-center justify-start transition ease-out duration-700">
                        @foreach($latestgames as $latestgame)
                            <div class="flex flex-shrink-0 relative w-full sm:w-auto">
                                <img class="object-cover object-center w-48 h-60" src="{{ url('/images/games/game_'.$latestgame->gameid.'.jpg')}}" alt="Game Cover"/>
                                <div class="bg-gray-800 bg-opacity-30 absolute w-full h-full p-6">
                                    <div class="flex h-full items-end pb-6">
                                        <a href="{{ route('game', $latestgame->gameid) }}">
                                            <h3 class="text-center text-lg lg:text-2xl font-semibold leading-5 lg:leading-6 text-neutral-50 underline">
                                                @if(strlen($latestgame->title) > 17)
                                                    {{substr($latestgame->title, 0,  17) . '...'}}
                                                @else
                                                    {{substr($latestgame->title, 0,  17)}}
                                                @endif
                                            </h3>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <button aria-label="slide forward" class="absolute z-30 right-0 mr-10 focus:outline-none focus:bg-gray-400 focus:ring-2 focus:ring-offset-2 focus:ring-gray-400" id="next">
                    <svg class="dark:text-gray-900" width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path class="text-neutral-50" d="M1 1L7 7L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- TODO ADICIONAR DEPOIS ESTE CODIGO JS A UM FICHEIRO JS COMO DEVE SER -->
    <script>
        let defaultTransform = 0;
        function goNext() {
            defaultTransform = defaultTransform - 398;
            var slider = document.getElementById("slider");
            if (Math.abs(defaultTransform) >= slider.scrollWidth / 2.7) defaultTransform = 0;
            slider.style.transform = "translateX(" + defaultTransform + "px)";
        }
        next.addEventListener("click", goNext);
        function goPrev() {
            var slider = document.getElementById("slider");
            if (Math.abs(defaultTransform) === 0) defaultTransform = 0;
            else defaultTransform = defaultTransform + 398;
            slider.style.transform = "translateX(" + defaultTransform + "px)";
        }
        prev.addEventListener("click", goPrev);
    </script>

    <!-- Best Sellers-->
    <section class="flex flex-col items-center bg-gray-900 p-6">
        <p class="text-neutral-50 font-semibold text-2xl">Best Sellers <i class="fa-sharp fa-solid fa-fire-flame-curved text-amber-400"></i></p>
        @if($bestsellers->count())
            <!-- Game List -->
            <section class="flex flex-col gap-4 justify-center flex-wrap md:flex-row mt-6">
                <!-- Game -->
                @foreach($bestsellers as $bestseller)
                    <article class="hover:brightness-50 transition duration-300 ease-in-out bg-gray-700 border-2 rounded-lg border-transparent">
                        <!-- Game Image -->
                        <a href="{{ route('game', $bestseller->gameid) }}"><img class="w-48 h-48 border-2 rounded-lg border-transparent" src="{{ url('/images/games/game_'.$bestseller->gameid.'.jpg')}}" alt="Game Cover Image"></a>
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
                        <!-- Game Image -->
                        <a href="{{ route('game', $gamesoon->gameid) }}"><img class="w-48 h-48 border-2 rounded-lg border-transparent" src="{{ url('/images/games/game_'.$gamesoon->gameid.'.jpg')}}" alt="Game Cover Image"></a>
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

    <!-- Categories -->
    <section class="flex flex-col flex-wrap items-center bg-gray-900 p-6">
        <p class="text-neutral-50 font-semibold text-2xl">Categories <i class="fa-solid fa-tag text-amber-400"></i></p>
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
            <a href="{{ route('categories') }}" class="border border-solid rounded-full mt-4 p-2 border-transparent bg-gray-600 text-neutral-50 hover:bg-amber-400 transition duration-300 ease-in-out">Show More</a>
        @else
            <p class="text-amber-400 items-center mt-4">There are no categories at the moment!</p>
        @endif
    </section>

        <!-- View All Games -->
        <section class="flex flex-col flex-wrap items-center bg-gray-800 p-6 mb-8">
            <p class="text-neutral-50 font-semibold text-2xl text-center">Still haven't found what you are looking for?</p>
            <a href="{{route('viewallgames')}}" class="border border-solid rounded-full mt-4 p-2 border-transparent bg-gray-600 text-neutral-50 hover:bg-amber-400 transition duration-300 ease-in-out">View all games</a>
        </section>
@endsection
