@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li><a href="#">Create Patients</a></li>
        </ul>
    </nav>
@stop


@section('content')

    @include('admin/regions/patients/_form')

    <div class="form-group list-component" id="region-patients">
        <a class="import_patients" href="javascript:void(0);" region_id="{{$region->id}}"
           style="padding-left: 10px; margin-bottom: 15px;">Import Patients</a>
    </div>

    <button class="btn btn-primary" type="submit">Save Patient Roster</button>
    <a href="{{ URL::route('admin.regions.index') }}" class="btn btn-default" name="cancel" value="cancel">Cancel</a>
    {{ Form::close() }}
@stop
