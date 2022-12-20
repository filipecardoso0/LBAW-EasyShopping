@extends('layouts.app')

@section('content')
    <section class="flex flex-col md:flex-row justify-center text-neutral-50">
        <!-- Cart Products -->
        <section class="flex flex-col mb-8">
            <!-- Games -->
            @auth
                @php $total = 0 @endphp
                @foreach($items as $item)
                    @include('partials.cart_items', [$item])
                    @php $total += $item->price @endphp
                @endforeach
            @endauth
            @guest
                @if($items != null)
                    @foreach($items->items as $item)
                        @include('partials.cart_items', $item)
                    @endforeach
                @endif
            @endguest
        </section>
        <!-- Cart Summary -->
        <section class="flex flex-col ml-6 mt-6 mb-8 gap-4 items-center">
            <p>Cart Summary</p>
            @auth
                @if($total > 0)
                    <a href="{{ route('checkout') }}" class="border border-solid border-transparent p-2 bg-gray-900 rounded-full hover:bg-amber-400 transition duration-300 ease-in-out text-sm">
                            Proceed to checkout
                    </a>
                @endif
            @endauth
            @guest
                @if($items != null)
                    @if($items->totalPrice > 0)
                        <a href="{{ route('guestCheckout') }}" class="border border-solid border-transparent p-2 bg-gray-900 rounded-full hover:bg-amber-400 transition duration-300 ease-in-out text-sm">Proceed to checkout</a>
                    @endif
                @endif
            @endguest
            @auth
                <p>Quantity: {{ $items->count() }}</p>
                <p>Total: <span class="text-xl font-semibold">&euro; {{ $total }}</span></p>
            @endauth
            @guest
                @if($items != null)
                    <p>Quantity: {{ $items->totalQuantity }}</p>
                    <p>Total: <span class="text-xl font-semibold">&euro; {{ $items->totalPrice }}</span></p>
                @else
                    <p>Quantity: 0</p>
                    <p>Total: <span class="text-xl font-semibold">&euro; 0</span></p>
                @endif
            @endguest
        </section>
    </section>
@endsection
