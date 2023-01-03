@extends('layouts.app')


@section('content')
    <section class="flex flex-col flex-wrap">
        <section class="flex flex-row">
            @include('partials.userpage_aside')
            <section class="mx-auto text-neutral-50 text-lg">
                <h1 class="my-12 text-amber-400 text-2xl md:text-5xl font-bold">Wishlist</h1>
                <section class="flex flex-col">
                    @if($games->count())
                        @foreach($games as $game)
                            <article class="flex flex-row border-t-2">
                                <a href="{{ route('game', $game->gameid) }}"><img class="w-30 h-40 lg:w-25 lg:h-48 mb-4 mt-4 hover:brightness-50 transition duration-300 ease-in-out" src="{{ url('/images/games/game_'.$game->gameid.'.jpg')}}" alt="Game Image"></a>
                                <a href="{{ route('game', $game->gameid) }}">
                                    <section class="flex flex-col lg:mt-10 mx-4 mt-2">
                                        <p class="hidden" id="gameid">{{$game->gameid}}</p>
                                        <p class="text-amber-400">{{$game->title}}</p>
                                        <p><span class="text-amber-400">Price:</span> {{$game->price}} &euro;</p>
                                        <a class="transition duration-150 hover:text-red-400" id="removewishlist">Remove From Wishlist<i class="ml-2 fa-solid   fa-heart"></i></a>
                                    </section>
                                </a>
                            </article>
                        @endforeach

                        <!-- Games Pagination -->
                        <section class="self-end m-12">
                            {{ $games->links('pagination::tailwind') }}
                        </section>
                    @else
                        <p>You haven't added a game to your wishlist yet!</p>
                    @endif
                </section>
            </section>
        </section>
    </section>

    <!-- TODO ARRUMAR O CODIGO DESTA FUNCAO ENCODE FOR AJAX -->
    <!-- AJAX Request In order to remove products from the wishlist -->
    <script>
        function encodeForAjax(data) {
            return Object.keys(data).map(function(k){
                return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
            }).join('&')
        }

        //TODO DAQUI PARA A FRENTE FAZER O HTML CHAMAR O JS ATRAVES DE ONCLICK(PESQUISAR SOBRE ISSO)

        let delbtns = document.querySelectorAll('#removewishlist')
        let length = delbtns.length
        for(let delbtn of delbtns){
            delbtn.addEventListener("click", function(){
                let game = delbtn.parentElement
                let gameid = parseInt(game.querySelector('#gameid').innerHTML)

                //Get current pagination page
                let url_string = window.location.href
                let url = new URL(url_string)
                let pagenumber = parseInt(url.searchParams.get("page"))

                length--

                //Removes element from HTML
                if(length > 0){
                    let article = game.parentElement
                    let section = article.parentElement
                    section.removeChild(article)
                }
                else if(length == 0 && pagenumber != 1){
                    let article = game.parentElement
                    let section = article.parentElement
                    section.removeChild(article)
                    let p = document.createElement("p")
                    //Redirects to the previous page
                    let pagenumberstr = (pagenumber-1).toString()
                    window.location.replace('{{url('/')}}/wishlist?page='+pagenumberstr)
                }

                else{
                    let article = game.parentElement
                    let section = article.parentElement
                    section.removeChild(article)
                    let p = document.createElement("p")
                    p.innerHTML = "You haven't added a game to your wishlist yet!"
                    section.appendChild(p)
                }


                //Initializes AJAX DELETE REQUEST TO WishListController
                const xml = new XMLHttpRequest();
                xml.open('DELETE', '{{route('removefromwishlist')}}', true) //TODO ADD ROUTE TO REMOVE FROM WISHLIST
                xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
                xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xml.send(encodeForAjax({gameid: gameid}))
            })
        }
    </script>


@endsection
