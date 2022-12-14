@extends('layouts.app')

@section('content')
    @include('partials.breadcrumbs', $path = array('FAQ' => route('faqpage'), ' > Account' => route('faqaccount')))
    <section class="flex flex-col flex-wrap items-center text-neutral-50 justify-center mb-12">
        <h1 class="uppercase text-2xl md:text-5xl font-semibold my-4">Faq- Account</h1>
        <section class="space-y-6 my-4">
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">How do I register?</p>
                <p>If you want to register an account, click on <span class="text-amber-400"><a href="{{route('register')}}">"Register"</a></span> and fill in the required information.</p>
                <ul class="list-disc">
                    <li>Enter e-mail address, username and password</li>
                </ul>
            </article>
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">How do I know if I can buy/register from my country/region?</p>
                <p>For the time being, registrations, as well as purchasing is not available to users from these countries or regions:</p>
                <ul class="list-disc">
                    <li>Afghanistan</li>
                    <li>Bangladesh</li>
                    <li>Bolivia</li>
                    <li>Crimea / Sevastopol</li>
                    <li>Cuba</li>
                    <li>Democratic Republic of the Congo</li>
                    <li>Equatorial Guinea</li>
                    <li>Iran</li>
                    <li>Iraq</li>
                    <li>Liberia</li>
                    <li>Myanmar</li>
                    <li>Namibia</li>
                    <li>North Korea</li>
                    <li>Pakistan</li>
                    <li>Palestine</li>
                    <li>Russia</li>
                    <li>South Sudan</li>
                    <li>Sudan</li>
                    <li>Syria</li>
                </ul>
                <p>If you have been verified, however for the time being you are in a restricted country or region, you will still be able to buy our products.</p>
            </article>
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">Having problems logging in (lost password?)</p>
                <p>If you are unable to login, it is possible to recover your password at this <span class="text-amber-400"><a href="#">address</a></span>. Your email will be required.</p>
                <p>For any other connection problems, use the <span class="text-amber-400"><a href="{{route('contactuspage')}}">"Contact Us"</a></span> section.</p>
            </article>
            <article>
                <p class="text-amber-400 text-xl md:text-3xl font-semibold mb-2">My Account is banned can I recovery it?</p>
                <p>Unfortunately we don't unban accounts. We take our guidelines very seriously and any misconduct will result in a penalty.</p>
            </article>
        </section>
        <p class="mt-8 text-2xl font-semibold">Still haven't found an answer to your problem? Reach out to us via the <span class="text-amber-400 underline"><a href="{{route('contactuspage')}}">"Contact Us"</a></span> section</p>
    </section>
@endsection
