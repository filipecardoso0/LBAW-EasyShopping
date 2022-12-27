@extends('layouts.app')

@section('content')

    <section class="flex flex-col flex-wrap items-center">
        <h1 class="mt-12 text-neutral-50 text-2xl font-bold">Manage User</h1>
        @if($user->count())
            @if($user[0]->orders->count())
                <section class="container flex flex-col gap-4 mx-8">
                    <section class="flex flex-row flex-wrap space-x-4">
                        <p>Email: {{$user[0]->email}}</p>
                        <p></p>
                    </section>
                    <p class="text-gray-100 font-semibold tracking-wider text-lg">Purchase History:</p>
                    <section class="w-full h-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-200 border-b">
                            <tr>
                                <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                    Order #
                                </th>
                                <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                    Payment Method
                                </th>
                                <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                    Order Total
                                </th>
                                <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                    Order Date
                                </th>
                                <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                    Products
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($user[0]->orders as $order)
                                    @if($order->state === true)
                                        <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">
                                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                                {{$order->orderid}}
                                            </td>
                                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                                {{$order->type}}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{$order->value}}</a>
                                            </td>
                                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                                {{\Carbon\Carbon::parse($order->order_date)->format('Y-m-d')}}
                                            </td>
                                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                                <section class="flex flex-col space-y-2 font-bold">
                                                    @foreach(\App\Models\GameOrder::orderGames($order->orderid) as $ordergame)
                                                        <a href="{{ route('game', $ordergame->gameid) }}" class="text-black hover:text-amber-400 hover:underline">{{$ordergame->title}}</a>
                                                    @endforeach
                                                </section>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </section>
                </section>
            @else
                <p class="mt-8 text-xl font-semibold text-amber-400">The given username does not have any orders yet!</p>
            @endif
        <!-- User Purchase History -->


        @else
            <p class="mt-8 text-xl font-semibold text-amber-400">The given username does not match any existing user</p>
        @endif
    </section>

@endsection
