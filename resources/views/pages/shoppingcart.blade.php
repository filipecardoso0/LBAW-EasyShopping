@extends('layouts.app')

@section('content')
    <section class="flex flex-col md:flex-row justify-center text-neutral-50">
        <!-- Cart Products -->
        <section class="flex flex-col mb-8">
            <!-- Games -->
            @auth
                <!-- {{ $total = 0 }} -->
            @endauth
            @foreach($items->items as $item)
            <article class="flex flex-row bg-gray-700 items-center space-y-1 p-4 rounded-md mt-4 mr-4 ml-4 mb-8">
                <a href="#"><img class="w-26 h-36 rounded-md" src="https://picsum.photos/200/300" alt="Game Image"></a>
                <section class="m-4">
                    <p>{{ \App\Models\Game::getOwnerNameByGameId($item->gameid) }}</p>
                    <p>{{ $item->title }}</p>
                    <p>{{ $item->price-($item->price*$item->discount) }}</p>
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
            @endforeach
        </section>
        <!-- Cart Summary -->
        <section class="flex flex-col ml-6 mt-6 mb-8 gap-4 items-center">
            <p>Cart Summary</p>
            @auth
                @if($total > 0)
                    <a href="#" class="border border-solid border-transparent p-2 bg-gray-900 rounded-full hover:bg-amber-400 transition duration-300 ease-in-out text-sm">
                            Proceed to checkout
                    </a>
                @endif
            @endauth
            @guest
                @if($items->totalPrice > 0)
                    <a href="{{ route('login') }}" class="border border-solid border-transparent p-2 bg-gray-900 rounded-full hover:bg-amber-400 transition duration-300 ease-in-out text-sm">Proceed to checkout</a>
                @endif
            @endguest
            @auth
                <p>Quantity: {{ $items->count() }}</p>
                <p>Total: <span class="text-xl font-semibold">&euro; {{ $items->totalQuantity }}</span></p>
            @endauth
            @guest
                <p>Quantity: {{ $items->totalQuantity }}</p>
                <p>Total: <span class="text-xl font-semibold">&euro; {{ $items->totalPrice }}</span></p>
            @endguest
        </section>
    </section>
@endsection
