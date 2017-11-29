<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>Doctors List</li>
        </ul>
    </nav>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                {{ Datatable::table()
->setUrl(route('admin.regions.all_doctors', $region->id))
->setOptions('ordering', false)
->setId('datatable_all_doctors')
->setCustomValues('isSysAdmin', $isSysAdmin)
->setCustomValues('region_id', $region->id)
->render('admin/regions/all_doctors/doctors_datatable') }}

            </div>
        </div>
    </div>
@stop
