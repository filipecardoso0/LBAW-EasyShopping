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
