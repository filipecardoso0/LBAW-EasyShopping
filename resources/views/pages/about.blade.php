@extends('layouts.app')

@section('title', 'About Us')

@section('content')

@include('partials.breadcrumbs', $path = array('About Us' => ''))

<p>This is the About Us PAGE</p>

@endsection