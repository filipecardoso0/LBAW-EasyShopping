@extends('layouts.app')

@section('content')
    <!-- Best Sellers Finish this later on (Add Categories and a list of all games coming soon)-->
    <section class="flex flex-col items-center mt-6">
        <!-- Just the Title and stuff like that -->
        <section class="flex flex-row">
            <h1 class="text-neutral-50 font-semibold text-2xl">Best Sellers</h1>
        </section>
        <!-- Games -->
        <section class="flex flex-col flex-wrap md:flex-row mt-6 md:space-x-8 md:space-y-0 space-x-0 space-y-8 justify-center items-center">
            <article>
                <a href="#"><img src="https://picsum.photos/id/237/200/300" alt="Game Cover Image"></a>
                <a href="#"><p class="text-neutral-50">Game Title</p></a>
                <a href="#"><p class="text-neutral-50">Game Price</p></a>
            </article>
            <article>
                <a href="#"><img src="https://picsum.photos/id/237/200/300" alt="Game Cover Image"></a>
                <a href="#"><p class="text-neutral-50">Game Title</p></a>
                <a href="#"><p class="text-neutral-50">Game Price</p></a>
            </article>
            <article>
                <a href="#"><img src="https://picsum.photos/id/237/200/300" alt="Game Cover Image"></a>
                <a href="#"><p class="text-neutral-50">Game Title</p></a>
                <a href="#"><p class="text-neutral-50">Game Price</p></a>
            </article>
            <article>
                <a href="#"><img src="https://picsum.photos/id/237/200/300" alt="Game Cover Image"></a>
                <a href="#"><p class="text-neutral-50">Game Title</p></a>
                <a href="#"><p class="text-neutral-50">Game Price</p></a>
            </article>
            <article>
                <a href="#"><img src="https://picsum.photos/id/237/200/300" alt="Game Cover Image"></a>
                <a href="#"><p class="text-neutral-50">Game Title</p></a>
                <a href="#"><p class="text-neutral-50">Price: Game Price</p></a>
            </article>
        </section>
    </section>
    <section>

    </section>
@endsection
