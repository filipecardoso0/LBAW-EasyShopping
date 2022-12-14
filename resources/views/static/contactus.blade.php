@extends('layouts.app')

@section('content')
    @include('partials.breadcrumbs', $path = array('Contact Us' => route('contactuspage')))
    <section>
        <section class="py-8 lg:py-16 px-4 mx-auto max-w-screen-md">
            <p class="hidden text-center mb-4 text-red-400 font-semibold text-2xl" id="alert"></p>
            <h2 class="mb-4 text-4xl tracking-tight font-bold text-center text-gray-900 dark:text-white">Contact Us</h2>
            <p class="mb-8 lg:mb-16 font-semibold text-center text-amber-400 sm:text-xl">Run into an issue? Write a ticket, our team is available for helping you as soon as possible.</p>
            <form action="{{route('submitTicket')}}" class="space-y-8" method="POST">
                @csrf
                <section>
                    <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Your Username</label>
                    <input type="text" id="username" name="username" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:shadow-sm-light" value="{{auth()->user()->username}}" readonly required>
                </section>
                <section>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Your email</label>
                    <input type="email" id="email" name="email" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:shadow-sm-light" value="{{auth()->user()->email}}" readonly required>
                </section>
                <section>
                    <label for="issuetype" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Choose an issue type:</label>
                    <select name="type" id="issuetype" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:shadow-sm-light">
                        <option class="hidden" value="default"> -- Select an option -- </option>
                        <option value="account">Account</option>
                        <option value="games">Games</option>
                    </select>
                </section>
                <section>
                    <label for="subject" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Subject</label>
                    <input type="text" id="subject" name="subject" class="block p-3 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 dark:shadow-sm-light" placeholder="Let us know how we can help you" required>
                </section>
                <section class="sm:col-span-2">
                    <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Your message</label>
                    <textarea id="message" name="message" rows="6" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg shadow-sm border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Please describe your issue..." required></textarea>
                </section>
                <button id="submitbtn" type="button" class="bg-gray-700 py-3 px-5 text-sm font-medium text-center text-white rounded-lg hover:bg-amber-400 sm:w-fit hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Submit Ticket</button>
            </form>
        </section>
    </section>

    <!-- TODO "ARRUMAR" O JS DEPOIS -->
    <script>
        const btn = document.getElementById('submitbtn')
        btn.addEventListener("click", function(){
            if((document.getElementById('issuetype').value == 'account') ||(document.getElementById('issuetype').value == 'games')){
                //Enables form submission
                btn.type='submit'
                const p = document.getElementById('alert')
                p.classList.add('hidden')
            }
            else{
                //Disables form submission
                btn.type='button'
                const p = document.getElementById('alert')
                p.innerHTML = 'ERROR: Please Select An Issue Type'
                p.classList.remove('hidden')
            }
        })
    </script>

@endsection

