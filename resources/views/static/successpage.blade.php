@extends('layouts.app')

@section('content')
    <section class="flex flex-col items-center justify-center h-screen space-y-4">
        <i class="fa-solid fa-circle-check text-green-500 text-9xl"></i>
        <p class="text-6xl text-neutral-50">Transaction Completed!</p>
        <p class="text-3xl text-neutral-50">Thank you for purchasing at Easy Shopping!</p>
        <a href="{{route('userorders')}}" class="px-8 py-4 bg-gray-700 hover:bg-amber-400 text-neutral-50 transition duration-150 ease-in-out text-3xl">View Orders</a>
    </section>
@endsection
