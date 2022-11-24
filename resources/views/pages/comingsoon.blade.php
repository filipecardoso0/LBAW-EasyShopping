@extends('layouts.app')

@section('content')
    <section class="flex flex-col">
        <h1 class="text-neutral-50 font-semibold m-8 text-2xl">COMING SOON ... <i class="fa-solid fa-hourglass-end text-amber-400"></i></h1>
        @if($games->count())
            <section class="flex flex-col lg:flex-row gap-4 justify-center flex-wrap mt-6 ml-6 mr-6 items-center">
                @foreach($games as $game)
                    <article class="hover:brightness-50 transition duration-300 ease-in-out bg-gray-700 border-2 rounded-lg border-transparent">
                        <!-- Game Image (Do this later) -->
                        <a href="{{ route('game', $game->gameid) }}"><img class="w-50 h-70 border-2 rounded-lg border-transparent" src="https://picsum.photos/200/300" alt="Game Cover Image"></a>
                        <!-- Game Info -->
                        <section class="p-2">
                            <a href="{{ route('game', $game->gameid) }}">
                                <p class="text-amber-400 text-center">
                                    @if(strlen($game->title) > 20)
                                        {{substr($game->title, 0,  20) . '...'}}
                                    @else
                                        {{substr($game->title, 0,  20)}}
                                    @endif
                                </p>
                            </a>
                            <a href="{{ route('game', $game->gameid) }}"><p class="text-neutral-50 text-center">Price: <span class="text-amber-400">{{$game->price-($game->price*$game->discount)}}&euro;</span></p></a>
                        </section>
                    </article>
                @endforeach
            </section>

            <!-- Categories Pagination -->
            <section class="self-end m-12">
                {{ $games->links('pagination::tailwind') }}
            </section>
        @else
            <p class="text-center text-amber-400 text-2xl font-semibold mt-12 mb-12">There are no categories yet!</p>
        @endif
    </section>
@endsection
