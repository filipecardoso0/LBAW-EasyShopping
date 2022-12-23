@extends('layouts.app')

@section('title', $game->title)
@section('content')
    @include('partials.breadcrumbs', $path = array($game->title => ''))

    <!-- TODO ONLY ALLOW REVIEW IF THE GAME HAS BEEN BOUGHT BY THE USER -->
    <section class="flex flex-col">
        <section class="flex flex-col lg:flex-row mt-8 ml-4">
            <img class="w-48 h-48 mr-4 mb-4 ml-4" src="{{ url('/images/games/game_'.$game->gameid.'.jpg')}}" alt="Game Image">
            <article class="ml-4 space-y-2">
                <input type="text" class="hidden" value="{{$game->gameid}}" id="game-gameid">
                <h1 class="text-amber-400 font-semibold text-2xl">{{ $game->title }}</h1>
                <h2 class="text-neutral-50"><span class="text-amber-400 font-semibold text-xl">Release:</span> {{\Carbon\Carbon::parse($game->release_date)->format('d/m/Y')}}</h2>
                <p class="text-amber-400 font-semibold">Classification:
                    @if($game->reviewsDesc->count())
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
                <p class="text-neutral-50 font-semibold">Price: <span class="text-amber-400">{{ $game->price }}&euro;</span></p>
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

            <!-- TODO COLOCAR ESTE CODIGO DE FORMA LIMPA SEPARADA POR FICHEIROS -->
            <section class="lg:mx-auto lg:my-0 mx-4 my-4 max-w-md">
                <div class="shadow-2xl relative">
                    <!-- large image on slides -->
                    <div class="mySlides hidden">
                        <img src="{{ url('/images/gamevisuals/game'.$game->gameid.'_1.jpg')}}" class="w-full object-cover">
                    </div>
                    <div class="mySlides hidden">
                        <img src="{{ url('/images/gamevisuals/game'.$game->gameid.'_2.jpg')}}" class="w-full object-cover">
                    </div>
                    <div class="mySlides hidden">
                        <img src="{{ url('/images/gamevisuals/game'.$game->gameid.'_3.jpg')}}" class="w-full object-cover">
                    </div>
                    <div class="mySlides hidden">
                        <img src="{{ url('/images/gamevisuals/game'.$game->gameid.'_4.jpg')}}" class="w-full object-cover">
                    </div>
                    <div class="mySlides hidden">
                        <img src="{{ url('/images/gamevisuals/game'.$game->gameid.'_5.jpg')}}" class="w-full object-cover">
                    </div>

                    <!-- butttons -->
                    <a class="absolute left-0 inset-y-0 flex items-center -mt-32 px-4 text-white hover:text-gray-800 cursor-pointer text-3xl font-extrabold" onclick="plusSlides(-1)">❮</a>
                    <a class="absolute right-0 inset-y-0 flex items-center -mt-32 px-4 text-white hover:text-gray-800 cursor-pointer text-3xl font-extrabold" onclick="plusSlides(1)">❯</a>

                    <!-- image description -->
                    <div class="text-center text-white font-light tracking-wider bg-gray-800 py-2">
                        <p id="caption"></p>
                    </div>

                    <!-- smaller images under description -->
                    <div class="flex">
                        <div>
                            <img class="gamedetailsimage1 description h-24 opacity-50 hover:opacity-100 cursor-pointer" src="{{ url('/images/gamevisuals/game'.$game->gameid.'_1.jpg')}}" onclick="currentSlide(1)">
                        </div>
                        <div>
                            <img class="gamedetailsimage2 description h-24 opacity-50 hover:opacity-100 cursor-pointer" src="{{ url('/images/gamevisuals/game'.$game->gameid.'_2.jpg')}}" onclick="currentSlide(2)">
                        </div>
                        <div>
                            <img class="gamedetailsimage3 description h-24 opacity-50 hover:opacity-100 cursor-pointer" src="{{ url('/images/gamevisuals/game'.$game->gameid.'_3.jpg')}}" onclick="currentSlide(3)">
                        </div>
                        <div>
                            <img class="gamedetailsimage4 description h-24 opacity-50 hover:opacity-100 cursor-pointer" src="{{ url('/images/gamevisuals/game'.$game->gameid.'_4.jpg')}}" onclick="currentSlide(4)">
                        </div>
                        <div>
                            <img class="gamedetailsimage5 description h-24 opacity-50 hover:opacity-100 cursor-pointer" src="{{ url('/images/gamevisuals/game'.$game->gameid.'_5.jpg')}}" onclick="currentSlide(5)">
                        </div>
                    </div>
                </div>
            </section>


            <script>
                //JS to switch slides and replace text in bar//
                var slideIndex = 1;
                showSlides(slideIndex);

                function plusSlides(n) {
                    showSlides(slideIndex += n);
                }

                function currentSlide(n) {
                    showSlides(slideIndex = n);
                }

                function showSlides(n) {
                    var i;
                    var slides = document.getElementsByClassName("mySlides");
                    var dots = document.getElementsByClassName("description");
                    var captionText = document.getElementById("caption");
                    if (n > slides.length) {
                        slideIndex = 1
                    }
                    if (n < 1) {
                        slideIndex = slides.length
                    }
                    for (i = 0; i < slides.length; i++) {
                        slides[i].style.display = "none";
                    }
                    for (i = 0; i < dots.length; i++) {
                        dots[i].className = dots[i].className.replace(" opacity-100", "");
                    }
                    slides[slideIndex - 1].style.display = "block";
                    dots[slideIndex - 1].className += " opacity-100";
                    captionText.innerHTML = dots[slideIndex - 1].alt;
                }
            </script>


        </section>
        <section class="m-4">
            <h3 class="font-semibold text-neutral-50 text-lg underline">About this Game:</h3>
            <p class="mt-4 text-amber-400 bg-gray-900 p-2 border border-transparent border-solid rounded-md">
                {!! nl2br($game->description) !!}
            </p>
        </section>
        <section class="mr-4 ml-4 mt-4 mb-8">
            <section class="flex flex-row justify-between">
                <h4 class="font-semibold text-neutral-50 text-lg">{{Str::plural('Comment', $game->reviewsDesc->count())}}: {{$game->reviewsDesc->count()}}</h4>
                @auth
                    @php
                        //Asserts if user has already posted a review about the game
                        $flag = false;
                        foreach($game->reviewsDesc as $review){
                            if($review->user->username === auth()->user()->username)
                                $flag = true;
                            else
                                continue;
                        }
                    @endphp
                    @if($flag === false)
                        <button onclick="showReviewModalForm()" type="button" id="showmodalform" class="rounded-lg bg-gray-700 p-2 text-neutral-50 transition duration-300 ease-in-out hover:bg-amber-400">Post a review</button>
                    @else
                        <p class="text-amber-400 font-semibold underline reviewmessage">You have already posted a review</p>
                    @endif
                @endauth
                @guest
                    <a href="{{route('login')}}" class="rounded-lg bg-gray-700 p-2 text-neutral-50 transition duration-300 ease-in-out hover:bg-amber-400">Post a review</a>
                @endguest
            </section>
            <section id="game-commentsection">
                @if($game->reviewsDesc->count())
                    @foreach($game->reviewsDesc as $review)
                        <article class="flex flex-col m-4 bg-amber-400 p-2 border border-transparent border-solid rounded-md">
                            <section class="flex flex-row">
                                <img class="w-8 h-8" src="{{ url('images/avatar.png') }}" alt="Avatar image">
                                <p class="ml-2 text-neutral-50 font-bold">{{$review->user->username}}</p>
                                <p class="ml-2 text-neutral-50 stars">
                                    @php $aux = 0; @endphp
                                    @for($i=1; $i<=5; $i++)
                                        @if($i<=$review->rating)
                                            @php $aux++; @endphp
                                            <i class="fa-solid fa-star"></i>
                                        @else
                                            <i class="fa-regular fa-star"></i>
                                        @endif
                                    @endfor
                                    <input class="hidden reviewclassificationcomment" type="text" value="{{$aux}}">
                                </p>
                                @if($review->status === true)
                                    <p class="font-bold underline text-neutral-50 ml-2">(Edited)</p>
                                @endif
                            </section>
                            <section class="flex flex-row justify-between text-neutral-50">
                                <p class="mt-2 font-semibold usercomment">{!! nl2br($review->comment) !!}</p>
                                <p class="text-neutral-50 underline font-bold timecomment">{{\Carbon\Carbon::createFromTimeStamp(strtotime($review->date))->diffForHumans()}}</p>
                            </section>
                            <section class="flex flex-row justify-start text-neutral-50 mt-2 space-x-4">
                                @if($review->user->username === auth()->user()->username)
                                    <button onclick="gameEditReview()" type="button" class="rounded px-4 py-2 bg-gray-800 hover:bg-blue-500 transition duration-300 ease-in-out">Edit</button>
                                    <button onclick="gameDeleteReview()" type="button" value="{{$game->gameid}}" class="rounded px-4 py-2 bg-gray-800 hover:bg-red-500 transition duration-300 ease-in-out">Delete</button>
                                @endif
                            </section>
                        </article>
                    @endforeach
                @else
                    <p id="game-nocomments" class="text-center text-amber-400 font-semibold text-lg m-4">There are no comments yet.</p>
                @endif
            </section>
        </section>
    </section>

    <!-- MODAL CREATE REVIEW FORM -->
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
                        <div>
                            @for($i=1; $i<=5; $i++)
                                <i {{"id=star-" . $i}} onclick="updateReviewStars({{$i}})" class="fa-regular fa-star modalreviewstar text-xl"></i>
                            @endfor
                        </div>
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

    <!-- MODAL EDIT REVIEW FORM -->
    <section id="editreviewmodal" class="flex justify-center items-center antialiased h-screen fixed top-0 left-0 right-0 z-50 hidden bg-black bg-opacity-60">
        <section class="flex flex-col w-11/12 sm:w-5/6 lg:w-1/2 max-w-2xl mx-auto rounded-lg border border-gray-300 shadow-xl">
            <section
                class="flex flex-row justify-between p-6 bg-white border-b border-gray-200 rounded-tl-lg rounded-tr-lg"
            >
                <p class="font-semibold text-gray-800">Edit Review</p>
                <button onclick="closeEditReviewModalForm()"><i class="fa-solid fa-xmark text-2xl hover:text-red-500 transition duration-150 ease-in-out"></i></button>
            </section>
            <div class="flex flex-col px-6 py-5 bg-gray-50">
                <p class="mb-2 font-semibold text-gray-700">Comment</p>
                <textarea
                    type="text"
                    id="textareaeditreviewcomment"
                    placeholder="Type message..."
                    class="p-5 mb-5 bg-white border border-gray-200 rounded shadow-sm h-36"
                ></textarea>
                <div class="flex flex-col sm:flex-row items-center mb-5 sm:space-x-5">
                    <div class="w-full sm:w-1/2">
                        <p class="mb-2 font-semibold text-gray-700">Rating</p>
                        <div>
                            @for($i=1; $i<=5; $i++)
                                <i {{"id=star-" . $i}} onclick="updateReviewStars({{$i}})" class="fa-regular fa-star modalreviewstar text-xl"></i>
                            @endfor
                        </div>
                        <input id="reviewclassificationvalue" type="text" value="0" class="hidden">
                    </div>
                </div>
            </div>
            <div
                class="flex flex-row items-center justify-between p-5 bg-white border-t border-gray-200 rounded-bl-lg rounded-br-lg"
            >
                <button onclick="closeEditReviewModalForm()" class="font-semibold hover:text-neutral-50 text-gray-600 px-4 py-2 rounded hover:bg-red-500 transition duration-150 ease-in-out">Cancel</button>
                <button onclick="submitEditReviewModalForm()" class="px-4 py-2 text-gray-600 hover:text-neutral-50 font-semibold hover:bg-green-700 rounded">Submit Changes</button>
            </div>
        </section>
    </section>

    <!-- TODO COLOCAR NUM FICHEIRO JS -->
    <script>
        function encodeForAjax(data) {
            return Object.keys(data).map(function(k){
                return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
            }).join('&')
        }

        //Functions to format date
        function padTo2Digits(num) {
            return num.toString().padStart(2, '0');
        }

        function formatDate(date) {
            return (
                [
                    date.getFullYear(),
                    padTo2Digits(date.getMonth() + 1),
                    padTo2Digits(date.getDate()),
                ].join('-') +
                ' ' +
                [
                    padTo2Digits(date.getHours()),
                    padTo2Digits(date.getMinutes()),
                    padTo2Digits(date.getSeconds()),
                ].join(':')
            );
        }


        //Delete Review
        function gameDeleteReview(){

            const btn = event.target
            const gameid = parseInt(document.getElementById('game-gameid').value)
            const btnsection = btn.parentElement
            const article = btnsection.parentElement

            //Perform AJAX Request to ReviewController
            const xml = new XMLHttpRequest();
            xml.open('DELETE', '{{route('userremovereview')}}', true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({gameid: gameid}))

            //Remove comment from HTML
            article.parentElement.removeChild(article)

            //Update Comments count
            const h4 = document.querySelector('h4')
            let commentsqty = parseInt(h4.textContent.split(' ').pop())
            commentsqty--
            h4.textContent = 'Comments: ' + commentsqty.toString()

            //If Comment count is 0
            if(commentsqty == 0){
                const p = document.createElement('p')
                p.id = 'game-nocomments'
                p.classList.add('text-center', 'text-amber-400', 'font-semibold', 'text-lg', 'm-4')
                p.innerHTML = 'There are no comments yet.'
                const section = document.getElementById('game-commentsection')
                section.appendChild(p)
            }

            //Show again the Post a review button
            const postbtn = document.createElement('button')
            postbtn.setAttribute('onclick', 'showReviewModalForm()')
            postbtn.type = 'button'
            postbtn.id = 'showmodalform'
            postbtn.classList.add('rounded-lg', 'bg-gray-700', 'p-2', 'text-neutral-50', 'transition', 'duration-300', 'ease-in-out', 'hover:bg-amber-400')
            postbtn.textContent = 'Post a review'
            h4.parentElement.appendChild(postbtn)

            //Delete the "you have already posted a review" message
            h4.parentElement.removeChild(document.querySelector('.reviewmessage'))
        }

        //Edit Review
        function gameEditReview(){
            //Opens Review Modal Form
            showEditReviewModalForm()

            const btn = event.target
            const btnsection = btn.parentElement
            const article = btnsection.parentElement
            article.setAttribute('id','active') //Tags which comment we are updating

            const textcomment = article.querySelector('.usercomment').innerHTML
            const commentclassification = parseInt(article.querySelector('.reviewclassificationcomment').value)

            //Fill in the text area
            const textarea = document.getElementById('textareaeditreviewcomment')
            textarea.value = textcomment

            //Fill in Stars
            updateReviewStars(commentclassification)
        }

        function submitEditReviewModalForm(){

            //New comment and new classification
            const finalcomment = document.getElementById('textareaeditreviewcomment').value
            const finalrating = document.getElementById('reviewclassificationvalue').value
            const gameid = parseInt(document.getElementById('game-gameid').value)

            //Get Current date
            const timenow = formatDate(new Date())

            //Perform AJAX Request to ReviewController
            const xml = new XMLHttpRequest();
            xml.open('PUT', '{{route('userupdatereview')}}', true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({gameid: gameid, date: timenow, rating: finalrating, comment: finalcomment}))

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    //Update Comment HTML
                    const activecomment = document.getElementById('active')
                    const textcomment = activecomment.querySelector('.usercomment')
                    textcomment.innerHTML = finalcomment
                    const timecomment = activecomment.querySelector('.timecomment')
                    timecomment.innerHTML = 'Just Now'
                    const stars = activecomment.querySelector('.stars')

                    //Update Comment Stars
                    let str = ''
                    for(let i=1; i<=5; i++){
                        if(i <= finalrating){
                            str += '<i class="fa-solid fa-star ml-1"></i>'
                        }
                        else{
                            str += '<i class="fa-regular fa-star ml-1"></i>'
                        }
                    }

                    stars.innerHTML = str
                    //Update comment classification on "hidden input"
                    const input = document.createElement('input')
                    input.classList.add('hidden', 'reviewclassificationcomment')
                    input.type = 'text'
                    input.value = finalrating.toString()
                    stars.appendChild(input)

                    //Adds "Edited" Tag to the comment
                    const commentheader = activecomment.querySelector('section')
                    const tag = document.createElement('p')
                    tag.classList.add('font-bold', 'underline', 'text-neutral-50', 'ml-2')
                    tag.innerHTML = '(Edited)'
                    commentheader.appendChild(tag)

                    //Closes Modal Form
                    closeEditReviewModalForm()
                }
            }

        }

        //Close Review Modal Form
        function closeEditReviewModalForm(){
            //Untags the active comment
            const activecomment = document.getElementById('active')
            activecomment.removeAttribute('id')

            const form = document.getElementById('editreviewmodal')
            form.classList.add('hidden')
        }

        //Show Review Modal Form
        function showEditReviewModalForm(){
            const form = document.getElementById('editreviewmodal')
            form.classList.remove('hidden')
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
            xml.open('POST', '{{route('userpublishreview')}}', true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({gameid: gameid, rating: classification, comment: comment}))

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    //If comment count = 0 remove the "There are no comments yet" text
                    const h4 = document.querySelector('h4')
                    let commentsqty = parseInt(h4.textContent.split(' ').pop())
                    const gamereviews = document.getElementById('game-commentsection')
                    const textremove = document.getElementById('game-nocomments')
                    if(textremove != null)
                        gamereviews.removeChild(textremove)
                    //Add comment to Page Html
                    const article = document.createElement('article')
                    article.classList.add('flex', 'flex-col', 'm-4', 'bg-amber-400', 'p-2', 'border', 'border-transparent', 'border-solid', 'rounded-md')
                    const articlesection1 = document.createElement('section')
                    articlesection1.classList.add('flex', 'flex-row')
                    const userimg = document.createElement('img')
                    userimg.classList.add('w-8', 'h-8')
                    userimg.src = "{{url('images/avatar.png')}}"
                    userimg.alt = "Avatar Image"
                    articlesection1.appendChild(userimg)
                    const p1 = document.createElement('p')
                    const p2 = document.createElement('p')
                    p1.classList.add('ml-2', 'text-neutral-50', 'font-bold')
                    const username = document.getElementById('navusername').textContent
                    p1.innerHTML = username
                    p2.classList.add('ml-2', 'text-neutral-50', 'font-xl', 'stars')
                    let str = ''
                    for(let i=1; i<=5; i++){
                        if(i<=classification){
                            str += '<i class="fa-solid fa-star ml-1"></i>'
                        }
                        else{
                            str += '<i class="fa-regular fa-star ml-1"></i>'
                        }
                    }

                    p2.innerHTML = str
                    articlesection1.appendChild(p1)
                    articlesection1.appendChild(p2)
                    const starsinput = document.createElement('input')
                    starsinput.value = classification
                    starsinput.type = 'text'
                    starsinput.classList.add('hidden', 'reviewclassificationcomment')
                    articlesection1.appendChild(starsinput)
                    article.appendChild(articlesection1)
                    const articlesection2 = document.createElement('section')
                    articlesection2.classList.add('flex', 'flex-row', 'justify-between', 'text-neutral-50')
                    const p3 = document.createElement('p')
                    const p4 = document.createElement('p')
                    p3.classList.add('mt-2', 'font-semibold', 'whitespace-pre-wrap', 'usercomment')
                    p3.innerHTML = comment
                    p4.classList.add('underline', 'font-bold', 'timecomment')
                    p4.innerHTML = 'Just now'
                    articlesection2.appendChild(p3)
                    articlesection2.appendChild(p4)
                    article.appendChild(articlesection2)
                    const articlesection3 = document.createElement('section')
                    articlesection3.classList.add('flex', 'flex-row', 'justify-start', 'text-neutral-50', 'mt-2', 'space-x-4')
                    const btn1 = document.createElement('button')
                    const btn2 = document.createElement('button')
                    btn1.classList.add('rounded', 'px-4', 'py-2', 'bg-gray-800', 'hover:bg-blue-500', 'transition', 'duration-300', 'ease-in-out')
                    btn2.classList.add('rounded', 'px-4', 'py-2', 'bg-gray-800', 'hover:bg-red-500', 'transition', 'duration-300', 'ease-in-out')
                    btn1.textContent = 'Edit'
                    btn2.textContent = 'Delete'
                    btn1.setAttribute('onclick', 'gameEditReview()')
                    btn2.setAttribute('onclick', 'gameDeleteReview()')
                    articlesection3.appendChild(btn1)
                    articlesection3.appendChild(btn2)
                    article.appendChild(articlesection3)
                    //Add as latest comment (Parent's first child)
                    gamereviews.insertBefore(article, gamereviews.firstChild)

                    //If there is the text there are no comments yet make it hidden
                    const text = document.getElementById('game-nocomments')
                    if(text != null)
                        text.classList.add('hidden')

                    //Updates comments number
                    commentsqty++
                    h4.textContent = 'Comments: ' + commentsqty.toString()

                    //Removes Post a Review Button
                    const openmodalformbtn = document.getElementById('showmodalform')
                    const section = openmodalformbtn.parentElement
                    section.removeChild(openmodalformbtn)

                    //Display message saying the user has already posted a review
                    const p = document.createElement('p')
                    p.classList.add('text-amber-400', 'font-semibold', 'underline', 'reviewmessage')
                    p.innerHTML = 'You have already posted a review'
                    h4.parentElement.appendChild(p)

                    closeReviewModalForm()
                }
            }
        }


        //Review Game star hover
        function updateReviewStars(numstar){
            const stars = document.querySelectorAll('.modalreviewstar')
            const starno = parseInt(numstar)
            const classifications = document.querySelectorAll('#reviewclassificationvalue')
            for(let classification of classifications){
                classification.value = numstar
            }

            for(let star of stars){
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
