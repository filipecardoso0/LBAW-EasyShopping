<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
   <link href="{{ asset('css/app.css') }}" rel="stylesheet"> <!-- TailWind -->
   <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"> <!-- Font Awesome CDN -->

      <script type="text/javascript">
        // Fix for Firefox autofocus CSS bug
        // See: http://stackoverflow.com/questions/18943276/html-5-autofocus-messes-up-css-loading/18945951#18945951
    </script>
    <script type="text/javascript" src={{ asset('js/app.js') }} defer></script>
  </head>
  <body class="bg-gray-800">
      <nav class="flex flex-col md:flex-row flex-wrap bg-gray-900 text-neutral-50 p-2 items-center justify-between">
          <section class="flex flex-wrap items-center space-x-4">
              <a class="font-normal text-md" href="{{ url('/') }}">Easy Shopping</a>
              <a href="{{ url('/') }}"><img src="{{ url('/image/logo.png')}}" alt="Easy Shopping Logo Image" class="w-12 h-12"></a>
          </section>

          <ul class="flex flex-col md:flex-row md:space-x-4 md:space-y-0 space-x-0 space-y-2 font-normal text-md flex-wrap items-center">
              <li>
                  <a class="transition duration-150 border-b-4 border-transparent hover:border-amber-400" href="#">Shopping Cart</a>
              </li>
              @auth
                  <li>
                      <a class="transition duration-150 border-b-4 border-transparent hover:border-amber-400" href="#">{{ auth()->user()->username }}</a>
                  </li>
                  <li>
                      <a class="transition duration-150 border-b-4 border-transparent hover:border-amber-400" href="{{ route('logout') }}">Logout</a>
                  </li>
              @endauth
              @guest
              <li>
                  <a class="transition duration-150 border-b-4 border-transparent hover:border-amber-400" href="{{ url('/login') }}">Login</a>
              </li>
              <li>
                  <a class="transition duration-150 border-b-4 border-transparent hover:border-amber-400" href="{{ url('/register') }}">Register</a>
              </li>
              @endguest
          </ul>
      </nav>

      <section id="content">
        @yield('content')
      </section>

      <!-- Navbar position property is fixed on medium to large screens in order to have it on the bottom of the screen. On mobile devices its position property is default (static)-->
      <footer class="md:fixed static inset-x-0 bottom-0 flex flex-col md:flex-row font-normal text-sm text-neutral-50 bg-gray-900 items-center justify-between">
          <span class="md:ml-1 ml-0">
              <a href="#">Copyright &#169 Easy Shopping All Rights Reserved</a>
          </span>
          <ul class="flex flex-col content-center md:flex-row md:space-x-4 md:space-y-0 space-x-0 space-y-2 p-2 items-center">
                <li>
                    <a class="transition duration-150 border-b-2 border-transparent hover:border-amber-400" href="#">About Us</a>
                </li>
                <li>
                    <a class="transition duration-150 border-b-2 border-transparent hover:border-amber-400" href="#">Contact Us</a>
                </li>
                <li>
                    <a class="md:mr-1 mr-0 transition duration-150 border-b-2 border-transparent hover:border-amber-400" href="#">FAQ</a>
                </li>
          </ul>
      </footer>
  </body>
</html>
