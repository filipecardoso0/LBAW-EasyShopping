@extends('layouts.app')

@section('content')
    <section class="flex flex-col md:flex-row justify-center text-neutral-50">
        <!-- Cart Products -->
        <section class="flex flex-col mb-8">
            <!-- Games -->
            @auth
                @php $total = 0; @endphp
                @foreach($items as $item)
                    @include('partials.cart_items', [$item])
                    @php $total += $item->price; @endphp
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
                    <a id="proceed2checkoutbtn" href="{{ route('checkout') }}" class="border border-solid border-transparent p-2 bg-gray-900 rounded-full hover:bg-amber-400 transition duration-300 ease-in-out text-sm">
                            Proceed to checkout
                    </a>
                @endif
            @endauth
            @guest
                @if($items != null)
                    @if($items->totalPrice > 0)
                        <a id="proceed2checkoutbtn" href="{{ route('guestCheckout') }}" class="border border-solid border-transparent p-2 bg-gray-900 rounded-full hover:bg-amber-400 transition duration-300 ease-in-out text-sm">Proceed to checkout</a>
                    @endif
                @endif
            @endguest
            @auth
                <p id="shopping-cart-qty">Quantity: {{ $items->count() }}</p>
                <p id="shopping-cart-price">Total: <span class="text-xl font-semibold">&euro; {{ $total }}</span></p>
            @endauth
            @guest
                @if($items != null)
                    <p id="shopping-cart-qty">Quantity: {{ $items->totalQuantity }}</p>
                    <p id="shopping-cart-price">Total: <span class="text-xl font-semibold">&euro; {{ $items->totalPrice }}</span></p>
                @else
                    <p>Quantity: 0</p>
                    <p>Total: <span class="text-xl font-semibold">&euro; 0</span></p>
                @endif
            @endguest
        </section>
    </section>

    <!-- TODO COLOCAR ESTE JS ARRANJADINHO NUM FICHEIRO ESPECIFICO DEPOIS -->
    <script>
        function encodeForAjax(data) {
            return Object.keys(data).map(function(k){
                return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
            }).join('&')
        }

        //Remove from shopping cart auth()
        function removeFromCartAuth(){
            const button = event.target
            const gameid = button.value
            const gamecard = (button.parentElement).parentElement
            const gamecardcollection = gamecard.parentElement
            const gameprice = parseInt(button.parentElement.querySelector('p span.text-amber-400').textContent)

            //Update cart quantity
            let cartqty = document.getElementById('shopping-cart-qty')
            let qtyval = parseInt(cartqty.innerHTML.split(' ').pop())
            qtyval = qtyval-1
            cartqty.innerHTML = "Quantity: " + qtyval.toString()

            //Update cart price
            let price = document.getElementById('shopping-cart-price')
            let priceval = parseInt(price.lastChild.textContent.split(' ').pop())
            priceval = (priceval - gameprice)
            price.lastChild.textContent = '\u20AC ' + priceval.toString()

            //AJAX Request in order to delete the game from the shopping cart
            const xml = new XMLHttpRequest();
            xml.open('DELETE', '{{route('removefromcart')}}', true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({gameid: gameid}))

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    //Delete game card from Page's HTML
                    gamecardcollection.removeChild(gamecard)

                    //Update navbar quantity
                    const elem = document.getElementById('cartqty')
                    let qty = parseInt(elem.innerHTML)
                    elem.innerHTML = (qty-1).toString()

                    //Cart is empty now
                    if(qtyval == 0){
                        const proceed2checkoutbtn = document.getElementById('proceed2checkoutbtn')
                        proceed2checkoutbtn.classList.add('hidden')
                    }
                }
            }
        }

        //Remove from shopping cart guest
        function removeFromCartGuest(){
            const button = event.target
            const gameid = button.value
            const gamecard = (button.parentElement).parentElement
            const gamecardcollection = gamecard.parentElement
            const gameprice = parseInt(button.parentElement.querySelector('p span.text-amber-400').textContent)

            //Update cart quantity
            let cartqty = document.getElementById('shopping-cart-qty')
            let qtyval = parseInt(cartqty.innerHTML.split(' ').pop())
            qtyval = qtyval-1
            cartqty.innerHTML = "Quantity: " + qtyval.toString()

            //Update cart price
            let price = document.getElementById('shopping-cart-price')
            let priceval = parseInt(price.lastChild.textContent.split(' ').pop())
            priceval = (priceval - gameprice)
            price.lastChild.textContent = '\u20AC ' + priceval.toString()

            //AJAX Request in order to delete the game from the shopping cart
            const xml = new XMLHttpRequest();
            xml.open('GET', '/removeFromCart/' + gameid, true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send()

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    //Delete game card from Page's HTML
                    gamecardcollection.removeChild(gamecard)

                    //Update navbar quantity
                    const elem = document.getElementById('cartqty')
                    let qty = parseInt(elem.innerHTML)
                    elem.innerHTML = (qty-1).toString()

                    //Cart is empty now
                    if(qtyval == 0){
                        const proceed2checkoutbtn = document.getElementById('proceed2checkoutbtn')
                        proceed2checkoutbtn.classList.add('hidden')
                    }
                }
            }
        }

    </script>

@endsection
