@extends('layouts.app')

@section('content')
    @include('partials.breadcrumbs', $path = array('FAQ' => route('faqpage'), ' > Games' => route('faqgames')))
    <section class="flex flex-col flex-wrap items-center text-neutral-50 justify-center mb-12">
        <h1 class="uppercase text-2xl md:text-5xl font-semibold my-4">Faq- Games</h1>
        <section class="space-y-6 my-4">
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">Do I have to create an account to purchase a game?</p>
                <p>Yes, for several reasons: to download your game straight away in "Library" section, but also to contact us via the ticket system which requires an authentication.</p>
                <p>To create an account or log in <span class="text-amber-400"><a href="{{route('login')}}">Click Here</a></span></p>
            </article>
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">How long does it take for my game be able for downloading?</p>
                <p>If you pay by <span class="text-amber-400">Credit Card</span> or <span class="text-amber-400">PayPal</span> your download will be instantly available.</p>
                <p>Other methods like <span class="text-amber-400">Bank Transfer</span> require some administration verification, so it might take up to 2 hours for the download to be available.</p>
            </article>
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">Is there a deadline to download my game and how many times can I download it?</p>
                <p>No! No time limit, so you can download it whenever you want and as many times you wish.</p>
            </article>
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">Is a refund possible?</p>
                <p>A refund is possible if haven't downloaded your game yet in a 15 days period after your purchase. After clicking on the download button we don't refund anyone.</p>
            </article>
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">Are the games official?</p>
                <p>Yes, every game that we sell is licensed by the publisher and authorized by them, so every game that you buy is official.</p>
            </article>
        </section>
        <p class="mt-8 text-2xl font-semibold text-center">Still haven't found an answer to your problem? Reach out to us via the <span class="text-amber-400 underline"><a href="{{route('contactuspage')}}">"Contact Us"</a></span> section</p>
    </section>
@endsection
