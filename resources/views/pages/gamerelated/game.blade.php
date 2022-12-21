@extends('layouts.app')

@section('title', $game->title)
@section('content')
@include('partials.breadcrumbs', $path = array($game->title => ''))

    <section class="flex flex-col">
        <section class="flex flex-col md:flex-row mt-8 ml-4">
            <img class="w-48 h-48 mr-4 mb-4" src="{{ url('/images/games/game_'.$game->gameid.'.jpg')}}" alt="Game Image">
            <article class="ml-4 space-y-2">
                <h1 class="text-amber-400 font-semibold text-2xl">{{ $game->title }}</h1>
                <h2 class="text-neutral-50"><span class="text-amber-400 font-semibold text-xl">Release:</span> {{\Carbon\Carbon::parse($game->release_date)->format('d/m/Y')}}</h2>
                <p class="text-amber-400 font-semibold">Classification:
                    @if($game->reviews->count())
                        @for($i=0; $i<5; $i++)
                            @if($i<round($game->classification))
                                <span class="text-neutral-50"><i class="fa-solid fa-star"></i></span>
                            @else
                                <span class="text-neutral-50"><i class="fa-regular fa-star"></i></span>
                            @endif
                        @endfor
                    @else
                        <span class="text-neutral-50">No Reviews Yet</span>
                    @endif
                </p>
                <p class="text-amber-400 font-semibold">{{Str::plural('Category', $categories->count())}}:
                    @foreach($categories as $category)
                        <span class="text-neutral-50 mr-2">
                            <a class="underline" href="#">{{$category->name}}</a>
                        </span> <!-- TODO Adicionar hyperlink para a categoria -->
                    @endforeach
                </p>
                <p class="text-amber-400 font-semibold">Publisher: <span class="text-neutral-50 underline font-normal">{{ $game->user->publisher_name }}</span></p>
                <p class="text-neutral-50 font-semibold">Price: <span class="text-amber-400">{{ $game->price }}</span></p>
                <section class="flex flex-row mt-6 space-x-4">
                    @auth
                    <form>
                        @csrf
                        <button class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2" type="submit" onclick="addToCartAuth()" value="{{ $game->gameid }}"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</button>
                    </form>
                    <form>
                        @csrf
                        <button class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2" type="button" onclick="addToWishlist()" value="{{ $game->gameid }}"><i class="fa-solid fa-heart"></i> Add to Wishlist</button>
                    </form>
                    @endauth
                    @guest
                        <button onclick="addToCartGuest()" value="{{$game->gameid}}" class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</button>
                        <a href="{{ route('login') }}" class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2"><i class="fa-solid fa-heart"></i> Add to Wishlist</a>
                    @endguest
                </section>
            </article>

            <!-- Carousel With game detail photos -->
            <div id="carouselExampleIndicators" class="carousel slide relative" data-bs-ride="carousel">
                <div class="carousel-indicators absolute right-0 bottom-0 left-0 flex justify-center p-0 mb-4">
                    <button
                        type="button"
                        data-bs-target="#carouselExampleIndicators"
                        data-bs-slide-to="0"
                        class="active"
                        aria-current="true"
                        aria-label="Slide 1"
                    ></button>
                    <button
                        type="button"
                        data-bs-target="#carouselExampleIndicators"
                        data-bs-slide-to="1"
                        aria-label="Slide 2"
                    ></button>
                    <button
                        type="button"
                        data-bs-target="#carouselExampleIndicators"
                        data-bs-slide-to="2"
                        aria-label="Slide 3"
                    ></button>
                </div>
                <div class="carousel-inner relative w-full overflow-hidden">
                    <div class="carousel-item active float-left w-full">
                        <img
                            src="https://mdbcdn.b-cdn.net/img/new/slides/041.webp"
                            class="block w-full"
                            alt="Wild Landscape"
                        />
                    </div>
                    <div class="carousel-item float-left w-full">
                        <img
                            src="https://mdbcdn.b-cdn.net/img/new/slides/042.webp"
                            class="block w-full"
                            alt="Camera"
                        />
                    </div>
                    <div class="carousel-item float-left w-full">
                        <img
                            src="https://mdbcdn.b-cdn.net/img/new/slides/043.webp"
                            class="block w-full"
                            alt="Exotic Fruits"
                        />
                    </div>
                </div>
                <button
                    class="carousel-control-prev absolute top-0 bottom-0 flex items-center justify-center p-0 text-center border-0 hover:outline-none hover:no-underline focus:outline-none focus:no-underline left-0"
                    type="button"
                    data-bs-target="#carouselExampleIndicators"
                    data-bs-slide="prev"
                >
                    <span class="carousel-control-prev-icon inline-block bg-no-repeat" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button
                    class="carousel-control-next absolute top-0 bottom-0 flex items-center justify-center p-0 text-center border-0 hover:outline-none hover:no-underline focus:outline-none focus:no-underline right-0"
                    type="button"
                    data-bs-target="#carouselExampleIndicators"
                    data-bs-slide="next"
                >
                    <span class="carousel-control-next-icon inline-block bg-no-repeat" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>


        </section>
        <section class="m-4">
            <h3 class="font-semibold text-neutral-50 text-lg underline">About this Game:</h3>
            <p class="mt-4 text-amber-400 bg-gray-900 p-2 border border-transparent border-solid rounded-md">
                {!! nl2br($game->description) !!}
            </p>
        </section>
        <section class="mr-4 ml-4 mt-4 mb-8">
            <h4 class="font-semibold text-neutral-50 text-lg">Comments: {{$game->reviews->count()}}</h4>
            @if($game->reviews->count())
                @foreach($game->reviews as $review)
                    <article class="flex flex-col m-4 bg-amber-400 p-2 border border-transparent border-solid rounded-md">
                        <section class="flex flex-row">
                            <img class="w-8 h-8" src="{{ url('images/avatar.png') }}" alt="Avatar image">
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

    <!-- TODO COLOCAR NUM FICHEIRO JS -->
    <!-- Add to cart AJAX request -->
    <script>
        function encodeForAjax(data) {
            return Object.keys(data).map(function(k){
                return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
            }).join('&')
        }

        //Add to cart auth user
        function addToCartAuth(){
            const button = event.target
            const gameid = button.value

            //Disables button
            button.disabled = true

            //TODO ADD NOTIFICATION


            //INITIALIZE AJAX POST REQUEST TO ShoppingCartController
            const xml = new XMLHttpRequest();
            xml.open('POST', '{{route('addtocart')}}', true) //TODO ADD ROUTE TO REMOVE FROM WISHLIST
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({gameid: gameid}))

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    //Checks for duplicate insertions
                    let response = xml.responseText;

                    //Checks for duplicate values (If game is already added don't update quantity)
                    if(response == 0){
                        //Update Cart qty
                        const elem = document.getElementById('cartqty')
                        let qty = parseInt(elem.innerHTML)
                        elem.innerHTML = (qty+1).toString()
                    }
                }
            }
        }

        //Add to cart guest
        function addToCartGuest(){
            const button = event.target
            const gameid = button.value

            //Disables button
            button.disabled = true

            //TODO ADD NOTIFICATION

            //INITIALIZE AJAX GET REQUEST TO ShoppingCartController
            const xml = new XMLHttpRequest();
            xml.open('GET', '/addToCartGuest/' + gameid, true) //TODO ADD ROUTE TO REMOVE FROM WISHLIST
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send()

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    // Get and convert the responseText into JSON
                    let response = xml.responseText;

                    //Checks for duplicate values (If game is already added don't update quantity)
                    if(response == 0){
                        //Update Cart qty
                        const elem = document.getElementById('cartqty')
                        let qty = parseInt(elem.innerHTML)
                        elem.innerHTML = (qty+1).toString()
                    }
                }
            }

        }

        // Add to wishlist AJAX request
        function addToWishlist(){
            //Gets gameid
            const button = event.target
            const gameid = button.value

            //Disables button
            button.disabled = true

            //TODO ADD NOTIFICATION

            //Initializes AJAX POST REQUEST TO WishListController
            const xml = new XMLHttpRequest();
            xml.open('POST', '{{route('addtowishlist')}}', true) //TODO ADD ROUTE TO REMOVE FROM WISHLIST
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({gameid: gameid}))
        }

    </script>
@endsection
