@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.users.index') }}">Users</a></li>
            <li><a href="#">Create</a></li>
        </ul>
    </nav>
@stop


@section('content')
    @include('admin/users/_form')
@stop
