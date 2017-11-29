@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Edit Patient Visit</a></li>
        </ul>
    </nav>
@stop


@section('content')
    @include('admin/regions/patients/patient_program_visits/_form')

    <div class="form-group sepH_c">
        <button type="submit" class="btn btn-lg btn-primary ">Save</button>
        <a href="{{ URL::route('admin.programs.patient_visits', array($patient_id, $program_id)) }}"
           class="btn btn-lg btn-default ">Cancel</a>
    </div>
    {{ Form::close() }}
@stop
