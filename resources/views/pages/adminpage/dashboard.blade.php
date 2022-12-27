@extends('layouts.app')

@section('content')
    <!-- TODO ADD BREADCRUMBS -->
    <section class="flex flex-col flex-wrap mt-8">

        <!-- Page Title -->
        <h1 class="text-center text-2xl font-bold text-neutral-50 mt-8">Dashboard</h1>

        <!-- Statistics -->
        <section class="w-full flex flex justify-center items-center mt-8">
            <section class="container flex flex-col gap-4 mx-8">
                <p class="text-gray-100 font-semibold tracking-wider text-lg">Statistics</p>
                <section class="bg-gray-100 rounded-lg w-full h-auto py-4 flex flex-row justify-between sectionide-x sectionide-solid sectionide-gray-400">
                    <section class="relative flex-1 flex flex-col gap-2 px-4">
                        <label class="text-gray-800 text-base font-semibold tracking-wider">Users</label>
                        <label class="text-green-800 text-4xl font-bold">14K</label>
                    </section>
                    <section class="relative flex-1 flex flex-col gap-2 px-4">
                        <label class="text-gray-800 text-base font-semibold tracking-wider">Sales</label>
                        <label class="text-green-800 text-4xl font-bold">$1.2M</label>
                    </section>
                    <section class="relative flex-1 flex flex-col gap-2 px-4">
                        <label class="text-gray-800 text-base font-semibold tracking-wider">Orders</label>
                        <label class="text-green-800 text-4xl font-bold">6K</label>
                    </section>
                </section>
            </section>
        </section>

        <!-- Managment Options -->
        <section class="w-full flex flex justify-center items-center mt-8">
            <section class="container flex flex-col gap-4 mx-8">
                <p class="text-gray-100 font-semibold tracking-wider text-lg">Managements Options:</p>
                <section class="flex flex-col md:flex-row flex-wrap items-center justify-center md:space-x-6 md:space-y-0 space-y-6 w-full h-auto ">
                    <section class="flex flex-col flex-wrap p-4 bg-gray-700 rounded-md text-amber-400 items-center space-y-2">
                        <a class="hover:underline" href="#">Manage Products</a>
                        <i class="fa-sharp fa-solid fa-gears"></i>
                    </section>
                    <section class="flex flex-col flex-wrap p-4 bg-gray-700 rounded-md text-amber-400 items-center space-y-2">
                        <a class="hover:underline" href="#">Manage Users</a>
                        <i class="fa-solid fa-users"></i>
                    </section>
                    <section class="flex flex-col flex-wrap p-4 bg-gray-700 rounded-md text-amber-400 items-center space-y-2">
                        <a class="hover:underline" href="#">Manage Orders</a>
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </section>
                </section>
            </section>
        </section>

        <!-- RECENT ORDERS (20 Latest Orders) -->
        <section class="w-full flex flex justify-center items-center my-8">
            <section class="container flex flex-col gap-4 mx-8">
                <p class="text-gray-100 font-semibold tracking-wider text-lg">Recent Orders: </p>
                <section class="w-full h-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-200 border-b">
                        <tr>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                User
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Total
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Status
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Date
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-row items-center">
                                    <img class="w-8 h-8" src="{{ url('images/avatar.png') }}" alt="Avatar image">
                                    <p class="ml-2">Alfreds</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                89.00&euro;
                            </td>
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                Approved
                            </td>
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                6/10/2020
                            </td>
                        </tr>
                        <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-row items-center">
                                    <img class="w-8 h-8" src="{{ url('images/avatar.png') }}" alt="Avatar image">
                                    <p class="ml-2">Jolina</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                6.00&euro;
                            </td>
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                Pending
                            </td>
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                6/10/2020
                            </td>
                        </tr>
                        <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-row items-center">
                                    <img class="w-8 h-8" src="{{ url('images/avatar.png') }}" alt="Avatar image">
                                    <p class="ml-2">Francisco</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                16.00&euro;
                            </td>
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                Denied
                            </td>
                            <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                6/10/2020
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </section>
                <a href="#" class="text-amber-400 hover:underline tracking-wider self-end">View More</a>
            </section>
        </section>

        <!-- TODO COMPLETE -->

        <!-- RECENT TICKETS -->

    </section>


@endsection
