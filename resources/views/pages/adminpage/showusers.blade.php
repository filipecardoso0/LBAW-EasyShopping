@extends('layouts.app')

@section('content')
    <section class="flex flex-col flex-wrap mt-8">

        <!-- Page Title -->
        <h1 class="text-center text-2xl font-bold text-neutral-50 mt-8">Manage Users</h1>

        <section class="w-full flex flex justify-center items-center mt-8 mb-16">
            <section class="container flex flex-col gap-4 mx-8">
                <!-- Search User -->
                <section class="flex flex-row flex-wrap space-x-4 justify-between">
                    <section>
                        <p class="text-xl font-semibold text-neutral-50">Search User:</p>
                        <form action="{{route('adminexactsearchusername')}}" method="GET">
                            <input type="text" name="username" class="px-2" placeholder="Type user's username">
                            <button><i class="fa-solid fa-magnifying-glass px-4 py-1 bg-amber-400 text-neutral-50"></i></button>
                        </form>
                    </section>
                    <!-- Create User -->
                    <section class="flex flex-row flex-wrap">
                        <a class="text-center text-lg font-semibold text-neutral-50 px-4 py-2 bg-gray-500 rounded-full hover:bg-amber-400 transition duration-300 ease-in-out" href="{{route('adminformcreateuseraccount')}}">Create New User</a>
                    </section>
                </section>
                <p class="text-gray-100 font-semibold tracking-wider text-lg">User List:</p>
                <section class="w-full h-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-200 border-b">
                        <tr>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                User #
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Username
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Purchase History
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Account Status
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                                Manage Account
                            </th>
                            <th scope="col" class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100">
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    <p id="adminmanageuserid">{{$user->userid}}</p>
                                </td>
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-row items-center">
                                        <img class="w-8 h-8" src="{{ url('images/avatar.png') }}" alt="Avatar image">
                                        <p class="ml-2">{{$user->username}}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <form action="{{route('adminexactsearchusername')}}" method="GET">
                                        <input type="text" name="username" class="hidden" value="{{$user->username}}">
                                        <button class="hover:text-amber-400 hover:underline">View All</button>
                                    </form>
                                </td>
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    @if($user->banned === false)
                                        <div class="flex flex-row flex-wrap text-green-600 font-semibold items-center">
                                            <p id="accountstatus">Not Banned</p>
                                            <i id="accountstatusicon" class="ml-2 fa-solid fa-circle-check"></i>
                                        </div>
                                    @else
                                        <div class="flex flex-row flex-wrap text-red-500 font-semibold items-center">
                                            <p id="accountstatus">Banned</p>
                                            <i class="ml-2 fa-solid fa-ban"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    <a href="#" onclick="adminDisplayUserActions()">Actions</a>
                                </td>
                                <td class="text-sm text-gray-900 font-light px-6 py-4 whitespace-nowrap">
                                    <a class="hover:underline hover:text-amber-400" href="{{route('adminexactsearchusername')}}?username={{$user->username}}">View More</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </section>
                <!-- Orders Pagination -->
                <section class="self-end m-12">
                    {{ $users->links('pagination::tailwind') }}
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

        function hideUserActions(){
            //If an element was already active, we are going to restore it
            let elem = document.querySelector('.active')
            if(elem != null){
                elem.textContent = ' '
                const actions = document.createElement('a')
                actions.setAttribute('onclick', 'adminDisplayUserActions()')
                actions.textContent = 'Actions'
                elem.appendChild(actions)
                elem.classList.remove('active')
            }
        }

        function adminUpdateAccountBanStatus(){
            const btn = event.target
            const newstatus = btn.name
            const userid = parseInt(btn.value)

            const table = ((btn.parentElement).parentElement).parentElement

            //Perform AJAX Request to UserController
            const xml = new XMLHttpRequest();
            xml.open('PUT', '{{route('adminupdateuserbanstatus')}}', true)
            xml.setRequestHeader("X-CSRF-TOKEN", document.head.querySelector("[name=csrf-token]").content);
            xml.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xml.send(encodeForAjax({userid: userid, status: newstatus}))

            //Update HTML
            hideUserActions() //Reshow Actions
            const accountstatus = table.querySelector('#accountstatus')
            const accountstatusicon = table.querySelector('#accountstatusicon')

            if(newstatus === 'unban'){
                accountstatus.innerHTML = 'Not Banned'
                accountstatus.classList.remove('text-red-500')
                accountstatus.classList.add('text-green-600')
                accountstatusicon.classList.remove('fa-solid', 'fa-ban', 'text-red-500')
                accountstatusicon.classList.add('fa-solid', 'fa-circle-check', 'text-green-600')
            }
            else{
                accountstatus.innerHTML = 'Banned'
                accountstatus.classList.remove('text-green-600')
                accountstatus.classList.add('text-red-500')
                accountstatusicon.classList.remove('fa-solid', 'fa-circle-check', 'text-green-600')
                accountstatusicon.classList.add('fa-solid', 'fa-ban', 'text-red-500')
            }

        }

        function adminDeleteUserAccount(){
            //TODO DELETE USER ACCOUNTS
        }

        function adminDisplayUserActions(){
            hideUserActions()

            const btn = event.target
            const td = btn.parentElement
            td.classList.add('active')
            const table = td.parentElement

             //Erase Actions Text
            td.textContent = ''

            const userid = table.querySelector('#adminmanageuserid').innerHTML

            const section = document.createElement('section')
            section.classList.add('flex', 'flex-row', 'flex-wrap', 'space-x-4')
            const banbtn = document.createElement('button')
            banbtn.classList.add('hover:text-amber-400', 'hover:underline')
            banbtn.setAttribute('onclick', 'adminUpdateAccountBanStatus()')
            const deletebtn = document.createElement('button')
            deletebtn.classList.add('hover:text-red-500', 'hover:underline')
            deletebtn.textContent = 'Delete Account'
            deletebtn.setAttribute('onclick', 'adminDeleteUserAccount()')
            deletebtn.value = userid

            const status = table.querySelector('#accountstatus').innerHTML

            if(status === 'Banned'){
                banbtn.textContent = 'Unban'
                banbtn.name = 'unban'
                banbtn.value = userid
            }
            else{
                banbtn.textContent = 'Ban'
                banbtn.name = 'ban'
                banbtn.value = userid
            }

            section.appendChild(banbtn)
            section.appendChild(deletebtn)
            td.appendChild(section)

        }
    </script>
@endsection
