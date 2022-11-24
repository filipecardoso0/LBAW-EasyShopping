@extends('layouts.app')

@section('content')
    <section class="flex flex-col">
        <h1 class="text-neutral-50 font-semibold m-8 text-2xl">CATEGORIES:</h1>
        @if($categories->count())
        <section class="flex flex-col lg:flex-row gap-4 justify-center flex-wrap mt-6 ml-6 mr-6 items-center">
                @foreach($categories as $category)
                    <article class="hover:brightness-50 transition duration-300 ease-in-out bg-gray-700 border-2 rounded-lg border-transparent">
                        <a href="#"><img class="w-ful h-ful" src="https://picsum.photos/200/300" alt="Category Image"></a>
                        <section>
                            <a href="#">
                                <p class="text-center text-neutral-50">
                                    @if(strlen($category->name) > 25)
                                        {{substr($category->name, 0,  25) . '...'}}
                                    @else
                                        {{substr($category->name, 0,  25)}}
                                    @endif
                                </p>
                            </a>
                        </section>
                    </article>
                @endforeach
        </section>

            <!-- Categories Pagination -->
        <section class="self-end m-12">
                {{ $categories->links('pagination::tailwind') }}
        </section>
        @else
            <p class="text-center text-amber-400 text-2xl font-semibold mt-12 mb-12">There are no categories yet!</p>
        @endif
    </section>
@endsection
