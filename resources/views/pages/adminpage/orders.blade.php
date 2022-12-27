@extends('layouts.app')

@section('content')

    <section class="flex flex-col flex-wrap mt-8">

        <!-- Page Title -->
        <h1 class="text-center text-2xl font-bold text-neutral-50 mt-8">Manage Order Status</h1>

        <section class="w-full flex flex justify-center items-center mt-8 mb-16">
            <section class="container flex flex-col gap-4 mx-8">
                <p class="text-gray-100 font-semibold tracking-wider text-lg">Recent Orders:</p>
                <section class="w-full h-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-200 border-b">
                        <tr>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Order #
                            </th>
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
                                Payment Method
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Date(Y-M-D)
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">
                                <td id="orderid" class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    {{$order->orderid}}
                                </td>
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-row items-center">
                                        <img class="w-8 h-8" src="{{ url('images/avatar.png') }}" alt="Avatar image">
                                        <p id="orderusername" class="ml-2">{{$order->username}}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{$order->value}}&euro;
                                </td>
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    @if($order->state === true)
                                        <span class="orderstatus font-semibold text-green-600">Approved</span><i onclick="editorderstatus()" class="ml-2 fa-solid fa-pen-to-square"></i>
                                    @else
                                        <span class="orderstatus font-semibold text-red-500">Waiting Approval</span><i onclick="editorderstatus()" class="ml-2 fa-solid fa-pen-to-square"></i>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    {{$order->type}}
                                </td>
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    {{\Carbon\Carbon::parse($order->order_date)->format('Y-m-d')}}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </section>
                <!-- Orders Pagination -->
                <section class="self-end m-12">
                    {{ $orders->links('pagination::tailwind') }}
                </section>
            </section>
        </section>
    </section>

    <script>
        function encodeForAjax(data) {
            return Object.keys(data).map(function(k){
                return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
            }).join('&')
        }

        //Revers HTML Changes
        function reverteditorderstatus(){
            const elem = document.querySelector('.active')

            let span = document.createElement('span')
            span.classList.add('orderstatus', 'font-semibold')
            let i = document.createElement('i')
            i.setAttribute('onclick', 'editorderstatus()')
            i.classList.add('ml-2', 'fa-solid', 'fa-pen-to-square')

            if(elem.classList.contains('Approved')){
                span.classList.add('text-green-600')
                span.textContent = 'Approved'
                elem.classList.remove('Approved')
            }
            else{
                span.classList.add('text-red-500')
                span.textContent = 'Waiting Approval'
                elem.classList.remove('Waiting')
            }

            //Empties HTML
            elem.textContent = ' '

            elem.appendChild(span)
            elem.appendChild(i)

            elem.classList.remove('active')
        }

        function submiteditorderstatus(){
            const elem = document.querySelector('.active')

            let status = elem.querySelector('section select').value

            const row = elem.parentElement
            const username = row.querySelector('#orderusername').textContent
            const orderid = parseInt(row.querySelector('#orderid').textContent)

            //Perform AJAX Request to OrderController
            const xml = new XMLHttpRequest();
            xml.open('PUT', '{{route('adminupdateorderstate')}}', true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({orderid: orderid, status: status, username: username}))

            //Update HTML
            elem.textContent = ' '

            let span = document.createElement('span')
            span.classList.add('orderstatus', 'font-semibold')
            let i = document.createElement('i')
            i.setAttribute('onclick', 'editorderstatus()')
            i.classList.add('ml-2', 'fa-solid', 'fa-pen-to-square')


            if(status === 'true'){
                span.classList.add('text-green-600')
                span.textContent = 'Approved'
            }
            else{
                span.classList.add('text-red-500')
                span.textContent = 'Waiting Approval'
            }

            elem.appendChild(span)
            elem.appendChild(i)

            if(elem.classList.contains('Approved'))
                elem.classList.remove('Approved')
            else
                elem.classList.remove('Waiting')

            elem.classList.remove('active')
        }

        function editorderstatus(){

            //If user already clicked on one to edit
            if(document.querySelector('.active'))
                reverteditorderstatus()

            const orderstatus = (event.target).parentElement
            const status = orderstatus.querySelector('.orderstatus').textContent

            if(status != 'Approved')
                orderstatus.classList.add('Waiting', 'active')
            else
                orderstatus.classList.add(status, 'active')

            let section = document.createElement('section')
            section.classList.add('flex', 'flex-row')
            let select = document.createElement('select')
            let opt1 = document.createElement('option')
            opt1.value = 'true'
            opt1.innerHTML = 'Approved'
            let opt2 = document.createElement('option')
            opt2.value = 'false'
            opt2.innerHTML = 'Waiting Approval'
            select.appendChild(opt1)
            select.appendChild(opt2)

            let bt1 = document.createElement('button')
            let bt2 = document.createElement('button')

            bt1.textContent = 'Make Changes'
            bt1.classList.add('hover:underline', 'hover:text-green-600', 'transition', 'duration-150', 'ease-in-out', 'ml-2')
            bt1.setAttribute('onclick', 'submiteditorderstatus()')
            bt2.textContent = 'Cancel'
            bt2.classList.add('hover:underline', 'hover:text-red-500', 'transition', 'duration-150', 'ease-in-out', 'ml-4')
            bt2.setAttribute('onclick', 'reverteditorderstatus()')

            section.appendChild(select)
            section.appendChild(bt1)
            section.appendChild(bt2)

            //Deletes the old html
            orderstatus.textContent = ' '

            orderstatus.appendChild(section)
        }
    </script>

@endsection
