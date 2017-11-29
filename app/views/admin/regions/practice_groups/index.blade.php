<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>Practice Groups List</li>
        </ul>
    </nav>
@stop


@section('content')
    @if ($user->isSysAdmin())
        <a href="{{ URL::route('admin.regions.create_practice_group', $region->id) }}" class="btn btn-sm btn-primary"
           type="button">Add a New Practice Group</a>
        <a href="{{ URL::route('admin.regions.all_doctors', $region->id) }}" class="btn btn-sm btn-primary"
           type="button">View All Doctors</a>
        <br/><br/>
    @endif

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

                {{ Datatable::table()
->setUrl(route('admin.regions.practice_groups_roster', array($region->id)))
->setOptions('ordering', false)
->setId('datatable_practice_groups_roster')
->setCustomValues('isSysAdmin', $isSysAdmin)
->setCustomValues('region_id', $region->id)
->render('admin/regions/practice_groups/practice_groups_datatable') }}

            </div>
        </div>
    </div>
@stop


