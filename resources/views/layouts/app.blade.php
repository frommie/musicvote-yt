<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <script src="{{ asset('js/app.js') }}" defer></script>

    <link type="text/css" rel="stylesheet" href="{{ mix('css/app.css') }}">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
  <section class="section">
    <div class="container" id="app">
      @yield('content')
    </div>
  </section>
</body>
</html>
