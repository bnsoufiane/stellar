<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Patients</a></li>
        </ul>
    </nav>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">

                {{ Datatable::table()
->setUrl(route('admin.programs.patients_list', array($program_id)))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('isSysAdmin', $isSysAdmin)
->setCustomValues('program_id', $program_id)
->render('admin/programs/patients_datatable') }}

            </div>
        </div>
    </div>
@stop
