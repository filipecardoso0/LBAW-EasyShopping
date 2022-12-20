@extends('layouts.app')

@section('content')
    <section class="flex flex-col flex-wrap">
        <section class="flex flex-row">
            @include('partials.userpage_aside')
            <section class="mx-auto text-neutral-50 text-lg">
                <h1 class="my-12 text-amber-400 text-2xl md:text-5xl font-bold" id="profilePage">Account Details</h1>
                <section class="flex flex-col space-y-4">
                    <p><a href="#"><span class="text-amber-400">Username</span>: {{auth()->user()->username}}<i class="ml-2 fa-solid fa-pen-to-square"></i></a></p>
                    <p><a href="#"><span class="text-amber-400">Email</span>: {{auth()->user()->email}}<i class="ml-2 fa-solid fa-pen-to-square"></i></a></p>
                    <p><a href="#">Change Password</a><i class="ml-2 fa-solid fa-lock"></i></p>
                </section>
            </section>
        </section>
    </section>

    <!-- Select Active Page on Aside -->
    <!-- TODO RETIRAR ESTE CODIGO DEPOIS DAQUI (ARRUMAR TODO O CODIGO JS) -->
    <script>

        function setActive(){
            const h1 = document.getElementById('profilePage')
            if(h1.innerHTML == 'Account Details'){
                document.querySelector('aside ul li:first-child').classList.add('underline')
                document.querySelector('aside ul li:first-child').classList.add('text-amber-400')
            }
            else if(h1.innerHTML == 'Order History'){
                document.querySelector('aside ul li:nth-child(2)').classList.add('underline')
                document.querySelector('aside ul li:nth-child(2)').classList.add('text-amber-400')
            }
            else if(h1.innerHTML == 'Wishlist'){
                document.querySelector('aside ul li:nth-child(2)').classList.add('underline')
                document.querySelector('aside ul li:nth-child(2)').classList.add('text-amber-400')
            }
            else if(h1.innerHTML == 'Library'){
                document.querySelector('aside ul li:nth-child(2)').classList.add('underline')
                document.querySelector('aside ul li:nth-child(2)').classList.add('text-amber-400')
            }
            else if(h1.innerHTMl == 'Support Tickets'){
                document.querySelector('aside ul li:last-child').classList.add('underline')
                document.querySelector('aside ul li:last-child').classList.add('text-amber-400')
            }
        }

        setActive()
    </script>

    <!-- MODAL FORM -->

@endsection
