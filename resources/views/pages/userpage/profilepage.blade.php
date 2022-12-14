@extends('layouts.app')

@section('content')
    <section class="flex flex-col flex-wrap">
        <h1 class="text-center my-12 text-amber-400 text-5xl font-bold">Account Details</h1>
        <section class="flex flex-row">
            <aside class="text-neutral-50 flex flex-col">
                <ul class="space-y-4">
                    <li><a href="#">Account Details</a></li>
                    <li><a href="#">Orders</a></li>
                    <li><a href="#">Wishlist</a></li>
                    <li><a href="#">Library</a></li>
                    <li><a href="#">Support Tickets</a></li>
                </ul>
            </aside>
            <section class="mx-auto">
                <p>Username: {{auth()->user()->username}}</p>
                <p>Email: {{auth()->user()->email}}</p>
                <p>Change Password</p>
            </section>
        </section>
    </section>
@endsection
