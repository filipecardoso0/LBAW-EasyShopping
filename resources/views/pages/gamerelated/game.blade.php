@extends('layouts.app')

@section('title', $game->title)
@section('content')
@include('partials.breadcrumbs', $path = array($game->title => ''))

    <section class="flex flex-col">
        <section class="flex flex-col md:flex-row mt-8 ml-4">
            <img class="w-48 h-48 mr-4 mb-4" src="{{ url('/images/games/game_'.$game->gameid.'.jpg')}}" alt="Game Image">
            <article class="ml-4 space-y-2">
                <input type="text" class="hidden" value="{{$game->gameid}}" id="game-gameid">
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
                        <button class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2" type="button" onclick="addToCartAuth()" value="{{ $game->gameid }}"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</button>
                        <button class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2" type="button" onclick="addToWishlist()" value="{{ $game->gameid }}"><i class="fa-solid fa-heart"></i> Add to Wishlist</button>
                    @endauth
                    @guest
                        <button onclick="addToCartGuest()" value="{{$game->gameid}}" class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2"><i class="fa-solid fa-cart-shopping"></i> Add to Cart</button>
                        <a href="{{ route('login') }}" class="rounded-none bg-amber-400 text-neutral-50 lg:px-8 lg:py-2 px-4 py-2"><i class="fa-solid fa-heart"></i> Add to Wishlist</a>
                    @endguest
                </section>
            </article>

            <!-- TODO ADD Carousel With game detail photos -->


        </section>
        <section class="m-4">
            <h3 class="font-semibold text-neutral-50 text-lg underline">About this Game:</h3>
            <p class="mt-4 text-amber-400 bg-gray-900 p-2 border border-transparent border-solid rounded-md">
                {!! nl2br($game->description) !!}
            </p>
        </section>
        <
        <section class="mr-4 ml-4 mt-4 mb-8">
            <section class="flex flex-row justify-between">
                <h4 class="font-semibold text-neutral-50 text-lg">{{Str::plural('Comment', $game->reviews->count())}}: {{$game->reviews->count()}}</h4>
                @auth
                    <button onclick="showReviewModalForm()" class="rounded-lg bg-gray-700 p-2 text-neutral-50 transition duration-300 ease-in-out hover:bg-amber-400">Post a review</button>
                @endauth
                @guest
                    <a href="{{route('login')}}" class="rounded-lg bg-gray-700 p-2 text-neutral-50 transition duration-300 ease-in-out hover:bg-amber-400">Post a review</a>
                @endguest
            </section>
            @if($game->reviews->count())
                @foreach($game->reviews as $review)
                    <article class="flex flex-col m-4 bg-amber-400 p-2 border border-transparent border-solid rounded-md">
                        <section class="flex flex-row">
                            <img class="w-8 h-8" src="{{ url('images/avatar.png') }}" alt="Avatar image">
                            <p class="ml-2 text-neutral-50 font-bold">{{$review->user->username}}</p>
                            <p class="ml-2 text-neutral-50">
                                @for($i=1; $i<=5; $i++)
                                    @if($i<=$review->rating)
                                        <i class="fa-solid fa-star"></i>
                                    @else
                                        <i class="fa-regular fa-star"></i>
                                    @endif
                                @endfor
                            </p>
                        </section>
                        <section class="flex flex-row justify-between text-neutral-50">
                            <p class="mt-2 font-semibold">{{$review->comment}}</p>
                            <p class="underline font-bold">{{\Carbon\Carbon::createFromTimeStamp(strtotime($review->date))->diffForHumans()}}</p>
                        </section>
                        <section class="flex flex-row justify-start text-neutral-50 mt-2 space-x-4">
                            <button class="rounded px-4 py-2 bg-gray-800 hover:bg-blue-500 transition duration-300 ease-in-out">Edit</button>
                            <button class="rounded px-4 py-2 bg-gray-800 hover:bg-red-500 transition duration-300 ease-in-out">Delete</button>
                        </section>
                    </article>
                @endforeach
            @else
                <p class="text-center text-amber-400 font-semibold text-lg m-4">There are no comments yet.</p>
            @endif
        </section>
    </section>

    <!-- MODAL REVIEW FORM -->
    <section id="reviewmodal" class="flex justify-center items-center antialiased h-screen fixed top-0 left-0 right-0 z-50 hidden bg-black bg-opacity-60">
        <section class="flex flex-col w-11/12 sm:w-5/6 lg:w-1/2 max-w-2xl mx-auto rounded-lg border border-gray-300 shadow-xl">
            <section
                class="flex flex-row justify-between p-6 bg-white border-b border-gray-200 rounded-tl-lg rounded-tr-lg"
            >
                <p class="font-semibold text-gray-800">Post a Review</p>
                <button onclick="closeReviewModalForm()"><i class="fa-solid fa-xmark text-2xl hover:text-red-500 transition duration-150 ease-in-out"></i></button>
            </section>
            <div class="flex flex-col px-6 py-5 bg-gray-50">
                <p class="mb-2 font-semibold text-gray-700">Comment</p>
                <textarea
                    type="text"
                    id="textareareviewcomment"
                    placeholder="Type message..."
                    class="p-5 mb-5 bg-white border border-gray-200 rounded shadow-sm h-36"
                ></textarea>
                <div class="flex flex-col sm:flex-row items-center mb-5 sm:space-x-5">
                    <div class="w-full sm:w-1/2">
                        <p class="mb-2 font-semibold text-gray-700">Rating</p>
                        @for($i=1; $i<=5; $i++)
                            <i {{"id=star-" . $i}} onclick="updateReviewStars({{$i}})" class="fa-regular fa-star modalreviewstar text-xl"></i>
                        @endfor
                        <input id="reviewclassificationvalue" type="text" value="0" class="hidden">
                    </div>
                </div>
            </div>
            <div
                class="flex flex-row items-center justify-between p-5 bg-white border-t border-gray-200 rounded-bl-lg rounded-br-lg"
            >
                <button onclick="closeReviewModalForm()" class="font-semibold hover:text-neutral-50 text-gray-600 px-4 py-2 rounded hover:bg-red-500 transition duration-150 ease-in-out">Cancel</button>
                <button onclick="submitReviewModalForm()" class="px-4 py-2 text-gray-600 hover:text-neutral-50 font-semibold hover:bg-green-700 rounded">Submit</button>
            </div>
        </section>
    </section>

    <!-- TODO COLOCAR NUM FICHEIRO JS -->
    <!-- Add to cart AJAX request -->
    <script>

        //TODO ASSERT IF USER HAS ALREADY REVIEWED THE GAME
        //TODO EDIT REVIEW
        //TODO DELETE REVIEW
        //TODO INSERT REVIEW IN HTML AFTER SUBMISSION

        function encodeForAjax(data) {
            return Object.keys(data).map(function(k){
                return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
            }).join('&')
        }

        //Close Review Modal Form
        function closeReviewModalForm(){
            const form = document.getElementById('reviewmodal')
            form.classList.add('hidden')
        }

        //Show Review Modal Form
        function showReviewModalForm(){
            const form = document.getElementById('reviewmodal')
            form.classList.remove('hidden')
        }

        function submitReviewModalForm(){
            const comment = document.getElementById('textareareviewcomment').value
            const classification = parseInt(document.getElementById('reviewclassificationvalue').value)
            const gameid = parseInt(document.getElementById('game-gameid').value)

            //Perform AJAX Post Request to ReviewController
            const xml = new XMLHttpRequest();
            xml.open('post', '{{route('userpublishreview')}}', true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({gameid: gameid, rating: classification, comment: comment}))

            closeReviewModalForm()
        }


        //Review Game star hover
        function updateReviewStars(numstar){
            const stars = document.querySelectorAll('.modalreviewstar')
            const starno = parseInt(numstar)
            const classification = document.getElementById('reviewclassificationvalue')
            classification.value = numstar

            for(star of stars){
                let id = parseInt(star.id.slice(-1))

                if(id <= starno){
                    star.classList.remove('fa-regular')
                    star.classList.add('fa-solid')
                }
                else{
                    star.classList.remove('fa-solid')
                    star.classList.add('fa-regular')
                }
            }
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
