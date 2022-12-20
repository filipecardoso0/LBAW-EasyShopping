<article class="flex flex-row bg-gray-700 items-center space-y-1 p-4 rounded-md mt-4 mr-4 ml-4 mb-8">
    <a href="{{route('game', $item->gameid)}}"><img class="w-15 h-18 lg:w-48 lg:h-48 mr-4 mb-4" src="{{ url('/images/games/game_'.$item->gameid.'.jpg')}}" alt="Game Image"></a>
    <section class="m-4">
        <a href="{{route('game', $item->gameid)}}">
        <p>{{ \App\Models\Game::getOwnerNameByGameId($item->gameid) }}</p>
        <p>{{ $item->title }}</p>
            <p><span class="text-amber-400">Price:</span> {{ $item->price-($item->price*$item->discount) }} €</p></a>
        @auth
            <!-- {{ $total += $item->price-($item->price*$item->discount) }} -->
        @endauth
        @auth
            <form action="{{ route('removefromcart') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" name="gameid" value="{{ $item->gameid }}" class="hover:text-amber-400">Remove Product <i class="fa-solid fa-trash-can"></i></button>
                @endauth
                @guest
                    <a href="{{ route('removeFromCartGuest', $item->gameid) }}" class="hover:text-amber-400">Remove Product <i class="fa-solid fa-trash-can"></i></a>
        @endguest
    </section>
</article>
