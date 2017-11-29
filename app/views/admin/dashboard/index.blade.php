@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
        </ul>
    </nav>
@stop


@section('content')
    Dashboard content
@stop
