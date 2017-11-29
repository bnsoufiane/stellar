@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Create Region</a></li>
        </ul>
    </nav>
@stop


@section('content')
    @include('admin/regions/_form')
@stop
