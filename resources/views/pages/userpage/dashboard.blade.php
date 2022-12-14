@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@include('partials.breadcrumbs', $path = array('Profile' => route('userprofile'), ' > Dashboard' => ''))
    <section class="flex flex-col mt-4 items-center">
        <p class="font-semibold text-lg text-amber-400">Order History</p>
        @if($data->count())
            @foreach($data as $info)
                <article class="mt-4">
                    <table class="text-center border-spacing-x-4 border-separate border bg-amber-400 border-solid rounded-md border-gray-900">
                        <tr>
                            <th>Order ID</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Value</th>
                            <th>Order Date</th>
                            <th>Game Title</th>
                        </tr>
                        <tr class="text-black font-medium">
                            <td>{{ $info->orderid }}</td>
                            <td>{{ $info->type }}</td>
                            @if($info->state)
                                <td class="text-green-400">Approved</td>
                            @else
                                <td class="text-red-400">Not Accepted</td>
                            @endif
                            <td>{{ $info->value }}</td>
                            <td>{{ date('d-m-Y', strtotime($info->order_date)) }}</td>
                            <td>{{ $info->title }}</td>
                        </tr>
                    </table>
                </article>
            @endforeach
        @else
            <p class="text-amber-400 text-lg font-semibold mt-4">There are no orders yet!</p>
        @endif
    </section>
@endsection
