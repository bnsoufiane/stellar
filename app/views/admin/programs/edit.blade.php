@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>{{$program->name}}</li>
            <li><a href="#">Edit Program</a></li>
        </ul>
    </nav>
@stop


@section('content')
    @include('admin/programs/_form')
@stop
