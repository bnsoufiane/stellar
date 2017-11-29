@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>{{$practice_group->name}}</li>
            <li>Edit Doctor</li>
        </ul>
    </nav>
@stop


@section('content')
    @include('admin/regions/practice_groups/doctors/_form')

    <div class="form-group sepH_c">
        <button type="submit" class="btn btn-lg btn-primary ">Save</button>
        <a href="{{ URL::route('admin.practice_groups.doctors_roster', array($region_id, $practice_group_id)) }}" class="btn btn-lg btn-default ">Cancel</a>
    </div>
    {{ Form::close() }}
@stop
