<section id="searchmodalform" class="hidden flex justify-center items-center antialiased h-screen fixed top-0 left-0 right-0 z-50 bg-black bg-opacity-60">
    <section class="flex flex-col w-11/12 sm:w-5/6 lg:w-1/2 max-w-2xl mx-auto rounded-lg border border-gray-300 shadow-xl bg-gray-700 border-none">
        <!-- Search Bar -->
        <input id="modalsearchbar" placeholder="Looking for a game?" oninput="searchgame()" type="text" class="text-neutral-50 relative py-4 text-xl pr-10 pl-4 rounded-md bg-gray-700">
        <i class="absolute self-end text-2xl mt-4 mr-2 text-amber-400 fa-sharp fa-solid fa-magnifying-glass"></i>
        <!-- Game Listing -->
        <section id="searchresults" class="flex flex-col flex-wrap text-neutral-50">
            <!-- Game Items -->
        </section>
    </section>
</section>
