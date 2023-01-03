@extends('layouts.app')

@section('content')
    <section class="flex flex-row flex-wrap">
        <!-- Search Filters -->
        <aside class="text-neutral-50 text-md mt-6 ml-4">
            <p class="font-bold">Filters:</p>
            <p class="mt-8 font-semibold mb-2">Price (EUR)</p>
            <section class="flex flex-row flex-wrap text-black">
            <!-- Price Filter -->
            <input class="w-14 h-8 px-2 text-lg" id="pricelow" oninput="filterPrice()" type="number">
            <span class="mx-4 text-neutral-50">-</span>
            <input class="w-14 h-8 px-2 text-lg" id="pricehigh" oninput="filterPrice()" type="number">
            </section>
            <section class="flex flex-col flex-wrap mt-8 space-y-4">
                <p class="font-semibold">Categories: </p>
                    @foreach(\App\Models\Category::getAllCategories() as $category)
                    <article class="flex flex-row flex-wrap">
                        <label for="{{$category->name}}"> {{$category->name}}</label>
                        <input class="ml-2 categorycheck" type="checkbox" id="{{$category->name}}" name="{{$category->name}}" onchange="getCategories()" value="{{$category->categoryid}}">
                    </article>
                @endforeach
            </section>
            <section class="flex flex-col flex-wrap mt-8">
                <label class="mb-2" for="sortby">Sort By: </label>
                <select class="text-black" name="sortby" onchange="sortBySelect()" id="sortbyselect">
                    <option value="default">-- Select an option -- </option>
                    <option value="high2low">Price: High to Low</option>
                    <option value="low2high">Price: Low to High</option>
                    <option value="discount">Discount: Best</option>
                    <option value="release">Release: Recent</option>
                    <option value="reviews">Reviews: Best</option>
                </select>
            </section>
        </aside>
        <section class="flex flex-col mx-auto">
            <h1 class="text-neutral-50 font-semibold m-8 text-2xl text-center">ALL GAMES:</h1>
            @if($games->count())
                <!-- Game List -->
                <section id="gameslist" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 mb-12">
                    <!-- Game -->
                    @foreach($games as $game)
                        <article class="hover:brightness-50 transition duration-300 ease-in-out bg-gray-700 border-2 rounded-lg border-transparent">
                            <!-- Game Image -->
                            <a href="{{ route('game', $game->gameid) }}"><img class="w-48 h-48 border-2 rounded-lg border-transparent" src="{{ url('/images/games/game_'.$game->gameid.'.jpg')}}" alt="Game Cover Image"></a>
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
            @else
                <p class="text-center text-amber-400 text-2xl font-semibold mt-12 mb-12">There are no games yet!</p>
            @endif
        </section>
    </section>


    <!-- Filters -->
    <script>


        function getCategories(){
            const checked = document.querySelectorAll('.categorycheck')

            for(let box of checked){
                if(box.checked){
                    //Performs AJAX Request to Game Controller
                    const xml = new XMLHttpRequest();
                    xml.open('GET', 'api/category/' + box.value, true)
                    xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
                    xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xml.send()

                    //Empty Old Game List
                    const gamelist = document.getElementById('gameslist')
                    gamelist.textContent = ' '

                    // Fired once the request completes successfully
                    xml.onload = function (e) {
                        // Check if the request was a success
                        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                                //Gets all games sent as response
                                let games = JSON.parse(xml.responseText);

                                //Build Game Card
                                for (let game of games) {
                                    const article = document.createElement('article')
                                    //Game Image
                                    const aimg = document.createElement('a')
                                    aimg.href = '{{url('/')}}/details/' + game.gameid
                                    const gameimg = document.createElement('img')
                                    gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                                    gameimg.src = '{{ URL::to('/') }}/images/games/game_' + game.gameid + '.jpg'
                                    aimg.appendChild(gameimg)
                                    const gameinfosection = document.createElement('section')
                                    gameinfosection.classList.add('p-2')
                                    const gameinfosectionanchor = document.createElement('a')
                                    gameinfosectionanchor.href = '{{url('/')}}/details/' + game.gameid
                                    const gameinfoparagraph = document.createElement('p')
                                    gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                                    gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                                    const gameinfoanchor2 = document.createElement('a')
                                    gameinfoanchor2.href = '{{url('/')}}/details/' + game.gameid
                                    gameinfoprice = document.createElement('p')
                                    gameinfoprice.classList.add('text-neutral-50', 'text-center')
                                    gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price - (game.price * game.discount)).toString() + ' &euro;</span>'
                                    gameinfoanchor2.appendChild(gameinfoprice)
                                    gameinfosectionanchor.appendChild(gameinfoparagraph)
                                    gameinfosection.appendChild(gameinfosectionanchor)
                                    gameinfosection.appendChild(gameinfoanchor2)
                                    article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                                        article.appendChild(aimg)
                                    article.appendChild(gameinfosection)
                                    article.appendChild(gameinfoanchor2)
                                    gamelist.appendChild(article)
                                }
                            }
                        }
                    }
                }
        }

        function filterPrice() {
            const pricelow = document.getElementById('pricelow').value
            const pricehigh = document.getElementById('pricehigh').value

            if (pricelow > 0 && pricehigh > 0) {

                //Performs AJAX Request to Game Controller
                const xml = new XMLHttpRequest();
                xml.open('GET', '/api/games/' + pricelow + '/' + pricehigh, true)
                xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
                xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xml.send()

                // Fired once the request completes successfully
                xml.onload = function (e) {
                    // Check if the request was a success
                    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                        //Gets all games sent as response
                        let games = JSON.parse(xml.responseText);

                        if(games.length === 0){
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'No games found at the given price range'
                            },{
                                once: true,
                            })
                        }

                        //Empty Old Game List
                        const gamelist = document.getElementById('gameslist')
                        gamelist.textContent = ' '

                        //Build Game Card
                        for (let game of games) {
                            const article = document.createElement('article')
                            //Game Image
                            const aimg = document.createElement('a')
                            aimg.href = '{{url('/')}}/details/' + game.gameid
                            const gameimg = document.createElement('img')
                            gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                            gameimg.src = '{{ URL::to('/') }}/images/games/game_' + game.gameid + '.jpg'
                            aimg.appendChild(gameimg)
                            const gameinfosection = document.createElement('section')
                            gameinfosection.classList.add('p-2')
                            const gameinfosectionanchor = document.createElement('a')
                            gameinfosectionanchor.href = '{{url('/')}}/details/' + game.gameid
                            const gameinfoparagraph = document.createElement('p')
                            gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                            gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                            const gameinfoanchor2 = document.createElement('a')
                            gameinfoanchor2.href = '{{url('/')}}/details/' + game.gameid
                            gameinfoprice = document.createElement('p')
                            gameinfoprice.classList.add('text-neutral-50', 'text-center')
                            gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price - (game.price * game.discount)).toString() + ' &euro;</span>'
                            gameinfoanchor2.appendChild(gameinfoprice)
                            gameinfosectionanchor.appendChild(gameinfoparagraph)
                            gameinfosection.appendChild(gameinfosectionanchor)
                            gameinfosection.appendChild(gameinfoanchor2)
                            article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                                article.appendChild(aimg)
                            article.appendChild(gameinfosection)
                            article.appendChild(gameinfoanchor2)
                            gamelist.appendChild(article)
                        }

                    }

                }

            }
            else if (pricelow > 0) {

                //Performs AJAX Request to Game Controller
                const xml = new XMLHttpRequest();
                xml.open('GET', '/api/games/' + pricelow, true)
                xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
                xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xml.send()

                // Fired once the request completes successfully
                xml.onload = function (e) {
                    // Check if the request was a success
                    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                        //Gets all games sent as response
                        let games = JSON.parse(xml.responseText);

                        if(games.length === 0){
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'No games found at the given price range'
                            },{
                                once: true,
                            })
                        }

                        //Empty Old Game List
                        const gamelist = document.getElementById('gameslist')
                        gamelist.textContent = ' '

                        //Build Game Card
                        for (let game of games) {
                            const article = document.createElement('article')
                            //Game Image
                            const aimg = document.createElement('a')
                            aimg.href = '{{url('/')}}/details/' + game.gameid
                            const gameimg = document.createElement('img')
                            gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                            gameimg.src = '{{ URL::to('/') }}/images/games/game_' + game.gameid + '.jpg'
                            aimg.appendChild(gameimg)
                            const gameinfosection = document.createElement('section')
                            gameinfosection.classList.add('p-2')
                            const gameinfosectionanchor = document.createElement('a')
                            gameinfosectionanchor.href = '{{url('/')}}/details/' + game.gameid
                            const gameinfoparagraph = document.createElement('p')
                            gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                            gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                            const gameinfoanchor2 = document.createElement('a')
                            gameinfoanchor2.href = '{{url('/')}}/details/' + game.gameid
                            gameinfoprice = document.createElement('p')
                            gameinfoprice.classList.add('text-neutral-50', 'text-center')
                            gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price - (game.price * game.discount)).toString() + ' &euro;</span>'
                            gameinfoanchor2.appendChild(gameinfoprice)
                            gameinfosectionanchor.appendChild(gameinfoparagraph)
                            gameinfosection.appendChild(gameinfosectionanchor)
                            gameinfosection.appendChild(gameinfoanchor2)
                            article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                                article.appendChild(aimg)
                            article.appendChild(gameinfosection)
                            article.appendChild(gameinfoanchor2)
                            gamelist.appendChild(article)
                        }

                    }

                }

            }
            else if (pricehigh > 0) {

                //Performs AJAX Request to Game Controller
                const xml = new XMLHttpRequest();
                xml.open('GET', '/api/games/max/' + pricehigh, true)
                xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
                xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xml.send()

                // Fired once the request completes successfully
                xml.onload = function (e) {
                    // Check if the request was a success
                    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                        //Gets all games sent as response
                        let games = JSON.parse(xml.responseText);

                        if(games.length === 0){
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'No games found at the given price range'
                            },{
                                once: true,
                            })
                        }

                        //Empty Old Game List
                        const gamelist = document.getElementById('gameslist')
                        gamelist.textContent = ' '

                        //Build Game Card
                        for (let game of games) {
                            const article = document.createElement('article')
                            //Game Image
                            const aimg = document.createElement('a')
                            aimg.href = '{{url('/')}}/details/' + game.gameid
                            const gameimg = document.createElement('img')
                            gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                            gameimg.src = '{{ URL::to('/') }}/images/games/game_' + game.gameid + '.jpg'
                            aimg.appendChild(gameimg)
                            const gameinfosection = document.createElement('section')
                            gameinfosection.classList.add('p-2')
                            const gameinfosectionanchor = document.createElement('a')
                            gameinfosectionanchor.href = '{{url('/')}}/details/' + game.gameid
                            const gameinfoparagraph = document.createElement('p')
                            gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                            gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                            const gameinfoanchor2 = document.createElement('a')
                            gameinfoanchor2.href = '{{url('/')}}/details/' + game.gameid
                            gameinfoprice = document.createElement('p')
                            gameinfoprice.classList.add('text-neutral-50', 'text-center')
                            gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price - (game.price * game.discount)).toString() + ' &euro;</span>'
                            gameinfoanchor2.appendChild(gameinfoprice)
                            gameinfosectionanchor.appendChild(gameinfoparagraph)
                            gameinfosection.appendChild(gameinfosectionanchor)
                            gameinfosection.appendChild(gameinfoanchor2)
                            article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                                article.appendChild(aimg)
                            article.appendChild(gameinfosection)
                            article.appendChild(gameinfoanchor2)
                            gamelist.appendChild(article)
                        }

                    }

                }

            }
            else{
                //Performs AJAX Request to Game Controller
                const xml = new XMLHttpRequest();
                xml.open('GET', '{{route('getallgames')}}' + pricehigh, true)
                xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
                xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xml.send()

                // Fired once the request completes successfully
                xml.onload = function (e) {
                    // Check if the request was a success
                    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                        //Gets all games sent as response
                        let games = JSON.parse(xml.responseText);

                        if(games.length === 0){
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'No games found at the given price range'
                            },{
                                once: true,
                            })
                        }

                        //Empty Old Game List
                        const gamelist = document.getElementById('gameslist')
                        gamelist.textContent = ' '

                        //Build Game Card
                        for (let game of games) {
                            const article = document.createElement('article')
                            //Game Image
                            const aimg = document.createElement('a')
                            aimg.href = '{{url('/')}}/details/' + game.gameid
                            const gameimg = document.createElement('img')
                            gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                            gameimg.src = '{{ URL::to('/') }}/images/games/game_' + game.gameid + '.jpg'
                            aimg.appendChild(gameimg)
                            const gameinfosection = document.createElement('section')
                            gameinfosection.classList.add('p-2')
                            const gameinfosectionanchor = document.createElement('a')
                            gameinfosectionanchor.href = '{{url('/')}}/details/' + game.gameid
                            const gameinfoparagraph = document.createElement('p')
                            gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                            gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                            const gameinfoanchor2 = document.createElement('a')
                            gameinfoanchor2.href = '{{url('/')}}/details/' + game.gameid
                            gameinfoprice = document.createElement('p')
                            gameinfoprice.classList.add('text-neutral-50', 'text-center')
                            gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price - (game.price * game.discount)).toString() + ' &euro;</span>'
                            gameinfoanchor2.appendChild(gameinfoprice)
                            gameinfosectionanchor.appendChild(gameinfoparagraph)
                            gameinfosection.appendChild(gameinfosectionanchor)
                            gameinfosection.appendChild(gameinfoanchor2)
                            article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                                article.appendChild(aimg)
                            article.appendChild(gameinfosection)
                            article.appendChild(gameinfoanchor2)
                            gamelist.appendChild(article)
                        }

                    }

                }
            }
        }

        //Sort By Select
        function sortBySelect(){
            const select = event.target

            if(select.value === 'high2low')
                high2low()
            else if(select.value === 'low2high')
                low2high()
            else if (select.value === 'discount')
                discount()
            else if (select.value === 'release')
                release()
            else if (select.value === 'reviews')
                reviews()

        }

        //Order by price (high to low)
        function high2low(){

            //Performs AJAX Request to Game Controller in order to obtain the games in desc order
            const xml = new XMLHttpRequest();
            xml.open('GET', '{{route('high2lowfilter')}}' , true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send()

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                    //Gets all games sent as response
                    let games = JSON.parse(xml.responseText);

                    if(games.length === 0){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'No games found at the given price range'
                        },{
                            once: true,
                        })
                    }

                    //Empty Old Game List
                    const gamelist = document.getElementById('gameslist')
                    gamelist.textContent = ' '

                    //Build Game Card
                    for(let game of games){
                        const article = document.createElement('article')
                        //Game Image
                        const aimg = document.createElement('a')
                        aimg.href = '{{url('/')}}/details/'+game.gameid
                        const gameimg = document.createElement('img')
                        gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                        gameimg.src = '{{ URL::to('/') }}/images/games/game_'+game.gameid+'.jpg'
                        aimg.appendChild(gameimg)
                        const gameinfosection = document.createElement('section')
                        gameinfosection.classList.add('p-2')
                        const gameinfosectionanchor = document.createElement('a')
                        gameinfosectionanchor.href = '{{url('/')}}/details/'+game.gameid
                        const gameinfoparagraph = document.createElement('p')
                        gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                        gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                        const gameinfoanchor2 = document.createElement('a')
                        gameinfoanchor2.href = '{{url('/')}}/details/'+game.gameid
                        gameinfoprice = document.createElement('p')
                        gameinfoprice.classList.add('text-neutral-50', 'text-center')
                        gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price-(game.price*game.discount)).toString() + ' &euro;</span>'
                        gameinfoanchor2.appendChild(gameinfoprice)
                        gameinfosectionanchor.appendChild(gameinfoparagraph)
                        gameinfosection.appendChild(gameinfosectionanchor)
                        gameinfosection.appendChild(gameinfoanchor2)
                        article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                        article.appendChild(aimg)
                        article.appendChild(gameinfosection)
                        article.appendChild(gameinfoanchor2)
                        gamelist.appendChild(article)
                    }

                }
            }

        }

        //Order by price (low to high)
        function low2high(){
            //Performs AJAX Request to Game Controller in order to obtain the games in desc order
            const xml = new XMLHttpRequest();
            xml.open('GET', '{{route('low2highfilter')}}' , true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send()

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                    //Gets all games sent as response
                    let games = JSON.parse(xml.responseText);

                    if(games.length === 0){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'No games found at the given price range'
                        },{
                            once: true,
                        })
                    }

                    //Empty Old Game List
                    const gamelist = document.getElementById('gameslist')
                    gamelist.textContent = ' '

                    //Build Game Card
                    for(let game of games){
                        const article = document.createElement('article')
                        //Game Image
                        const aimg = document.createElement('a')
                        aimg.href = '{{url('/')}}/details/'+game.gameid
                        const gameimg = document.createElement('img')
                        gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                        gameimg.src = '{{ URL::to('/') }}/images/games/game_'+game.gameid+'.jpg'
                        aimg.appendChild(gameimg)
                        const gameinfosection = document.createElement('section')
                        gameinfosection.classList.add('p-2')
                        const gameinfosectionanchor = document.createElement('a')
                        gameinfosectionanchor.href = '{{url('/')}}/details/'+game.gameid
                        const gameinfoparagraph = document.createElement('p')
                        gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                        gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                        const gameinfoanchor2 = document.createElement('a')
                        gameinfoanchor2.href = '{{url('/')}}/details/'+game.gameid
                        gameinfoprice = document.createElement('p')
                        gameinfoprice.classList.add('text-neutral-50', 'text-center')
                        gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price-(game.price*game.discount)).toString() + ' &euro;</span>'
                        gameinfoanchor2.appendChild(gameinfoprice)
                        gameinfosectionanchor.appendChild(gameinfoparagraph)
                        gameinfosection.appendChild(gameinfosectionanchor)
                        gameinfosection.appendChild(gameinfoanchor2)
                        article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                            article.appendChild(aimg)
                        article.appendChild(gameinfosection)
                        article.appendChild(gameinfoanchor2)
                        gamelist.appendChild(article)
                    }

                }
            }
        }

        function discount(){
            //Performs AJAX Request to Game Controller in order to obtain the games in desc order
            const xml = new XMLHttpRequest();
            xml.open('GET', '{{route('discountbest')}}' , true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send()

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                    //Gets all games sent as response
                    let games = JSON.parse(xml.responseText);

                    if(games.length === 0){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'No games found at the given price range'
                        },{
                            once: true,
                        })
                    }

                    //Empty Old Game List
                    const gamelist = document.getElementById('gameslist')
                    gamelist.textContent = ' '

                    //Build Game Card
                    for(let game of games){
                        const article = document.createElement('article')
                        //Game Image
                        const aimg = document.createElement('a')
                        aimg.href = '{{url('/')}}/details/'+game.gameid
                        const gameimg = document.createElement('img')
                        gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                        gameimg.src = '{{ URL::to('/') }}/images/games/game_'+game.gameid+'.jpg'
                        aimg.appendChild(gameimg)
                        const gameinfosection = document.createElement('section')
                        gameinfosection.classList.add('p-2')
                        const gameinfosectionanchor = document.createElement('a')
                        gameinfosectionanchor.href = '{{url('/')}}/details/'+game.gameid
                        const gameinfoparagraph = document.createElement('p')
                        gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                        gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                        const gameinfoanchor2 = document.createElement('a')
                        gameinfoanchor2.href = '{{url('/')}}/details/'+game.gameid
                        gameinfoprice = document.createElement('p')
                        gameinfoprice.classList.add('text-neutral-50', 'text-center')
                        gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price-(game.price*game.discount)).toString() + ' &euro;</span>'
                        gameinfoanchor2.appendChild(gameinfoprice)
                        gameinfosectionanchor.appendChild(gameinfoparagraph)
                        gameinfosection.appendChild(gameinfosectionanchor)
                        gameinfosection.appendChild(gameinfoanchor2)
                        article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                            article.appendChild(aimg)
                        article.appendChild(gameinfosection)
                        article.appendChild(gameinfoanchor2)
                        gamelist.appendChild(article)
                    }

                }
            }
        }

        function release(){
            //Performs AJAX Request to Game Controller in order to obtain the games in desc order
            const xml = new XMLHttpRequest();
            xml.open('GET', '{{route('orderlatestreleases')}}' , true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send()

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                    //Gets all games sent as response
                    let games = JSON.parse(xml.responseText);

                    if(games.length === 0){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'No games found at the given price range'
                        },{
                            once: true,
                        })
                    }

                    //Empty Old Game List
                    const gamelist = document.getElementById('gameslist')
                    gamelist.textContent = ' '

                    //Build Game Card
                    for(let game of games){
                        const article = document.createElement('article')
                        //Game Image
                        const aimg = document.createElement('a')
                        aimg.href = '{{url('/')}}/details/'+game.gameid
                        const gameimg = document.createElement('img')
                        gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                        gameimg.src = '{{ URL::to('/') }}/images/games/game_'+game.gameid+'.jpg'
                        aimg.appendChild(gameimg)
                        const gameinfosection = document.createElement('section')
                        gameinfosection.classList.add('p-2')
                        const gameinfosectionanchor = document.createElement('a')
                        gameinfosectionanchor.href = '{{url('/')}}/details/'+game.gameid
                        const gameinfoparagraph = document.createElement('p')
                        gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                        gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                        const gameinfoanchor2 = document.createElement('a')
                        gameinfoanchor2.href = '{{url('/')}}/details/'+game.gameid
                        gameinfoprice = document.createElement('p')
                        gameinfoprice.classList.add('text-neutral-50', 'text-center')
                        gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price-(game.price*game.discount)).toString() + ' &euro;</span>'
                        gameinfoanchor2.appendChild(gameinfoprice)
                        gameinfosectionanchor.appendChild(gameinfoparagraph)
                        gameinfosection.appendChild(gameinfosectionanchor)
                        gameinfosection.appendChild(gameinfoanchor2)
                        article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                            article.appendChild(aimg)
                        article.appendChild(gameinfosection)
                        article.appendChild(gameinfoanchor2)
                        gamelist.appendChild(article)
                    }

                }
            }
        }

        function reviews(){
            //Performs AJAX Request to Game Controller in order to obtain the games in desc order
            const xml = new XMLHttpRequest();
            xml.open('GET', '{{route('orderbestreviewed')}}' , true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send()

            // Fired once the request completes successfully
            xml.onload = function(e) {
                // Check if the request was a success
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {

                    //Gets all games sent as response
                    let games = JSON.parse(xml.responseText);

                    if(games.length === 0){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'No games found at the given price range'
                        },{
                            once: true,
                        })
                    }

                    //Empty Old Game List
                    const gamelist = document.getElementById('gameslist')
                    gamelist.textContent = ' '

                    //Build Game Card
                    for(let game of games){
                        const article = document.createElement('article')
                        //Game Image
                        const aimg = document.createElement('a')
                        aimg.href = '{{url('/')}}/details/'+game.gameid
                        const gameimg = document.createElement('img')
                        gameimg.classList.add('w-48', 'h-48', 'border-2', 'rounded-lg', 'border-transparent')
                        gameimg.src = '{{ URL::to('/') }}/images/games/game_'+game.gameid+'.jpg'
                        aimg.appendChild(gameimg)
                        const gameinfosection = document.createElement('section')
                        gameinfosection.classList.add('p-2')
                        const gameinfosectionanchor = document.createElement('a')
                        gameinfosectionanchor.href = '{{url('/')}}/details/'+game.gameid
                        const gameinfoparagraph = document.createElement('p')
                        gameinfoparagraph.classList.add('text-amber-400', 'text-center')
                        gameinfoparagraph.innerHTML = game.title.slice(0, 20)
                        const gameinfoanchor2 = document.createElement('a')
                        gameinfoanchor2.href = '{{url('/')}}/details/'+game.gameid
                        gameinfoprice = document.createElement('p')
                        gameinfoprice.classList.add('text-neutral-50', 'text-center')
                        gameinfoprice.innerHTML = 'Price: <span class="text-amber-400">' + parseInt(game.price-(game.price*game.discount)).toString() + ' &euro;</span>'
                        gameinfoanchor2.appendChild(gameinfoprice)
                        gameinfosectionanchor.appendChild(gameinfoparagraph)
                        gameinfosection.appendChild(gameinfosectionanchor)
                        gameinfosection.appendChild(gameinfoanchor2)
                        article.classList.add('hover:brightness-50', 'transition', 'duration-300', 'ease-in-out', 'bg-gray-700', 'border-2', 'rounded-lg', 'border-transparent'),
                            article.appendChild(aimg)
                        article.appendChild(gameinfosection)
                        article.appendChild(gameinfoanchor2)
                        gamelist.appendChild(article)
                    }
                }
            }
        }

    </script>

@endsection
