@extends('layouts.app')

@section('title', 'Categories')

@section('content')

@include('partials.breadcrumbs', $path = array('Categories' => route('categories')))
    <section class="flex flex-row">
        <aside class="flex flex-col ml-4 my-auto space-y-4">
            @php $categorylist = App\Http\Controllers\CategoryController::getAllCategories(); @endphp
            @foreach($categorylist as $categoryitem)
                <a href="{{ route('gamecategories', $categoryitem->categoryid) }}" class="text-neutral-50 font-semibold hover:text-amber-400 transition duration-300 ease-in-out">{{$categoryitem->name}}</a>
            @endforeach
        </aside>
        <section class="flex flex-col mx-auto">
            @if($categories->count())
            <section class="flex flex-col lg:flex-row gap-4 justify-center flex-wrap mt-6 ml-6 mr-6 items-center">
                    @foreach($categories as $category)
                        <article class="hover:brightness-50 transition duration-300 ease-in-out bg-gray-700 border-2 rounded-lg border-transparent">
                            <a href="{{ route('gamecategories', $category->categoryid) }}"><img class="w-60 h-60" src="{{url('images/gamecategories/category_'.$category->categoryid.'.jpg')}}" alt="Category Image"></a>
                            <section>
                                <a href="{{ route('gamecategories', $category->categoryid) }}">
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
    </section>
@endsection
