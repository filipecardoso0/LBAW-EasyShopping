@extends('layouts.app')

@section('title', 'Search Results')

@section('content')

@include('partials.breadcrumbs', $path = array('Search Results' => ''))

@if(count($results) == 0)
<p class="text-center text-2xl text-amber-400">
  No Results Found!
</p>
@else
<div class="flex flex-col lg:flex-row gap-4 justify-center flex-wrap mt-6 ml-6 mr-6 items-center">
  @foreach($results as $game)
      @include('partials.game_card', $game)
  @endforeach
<div>
@endif

{{ $results->links() }}

@endsection
