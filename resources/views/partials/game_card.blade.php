<article class="hover:brightness-50 transition duration-300 ease-in-out bg-gray-700 border-2 rounded-lg border-transparent">
    <!-- Game Image -->
    <a href="{{ route('game', $game->gameid) }}"><img class="w-15 h-18 lg:w-48 lg:h-48 mb-4" src="{{ url('/images/games/game_'.$game->gameid.'.jpg')}}" alt="Game Image"></a>
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
