@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>{{$practice_group->name}}</li>
            <li>Edit Practice Group</li>
        </ul>
    </nav>
@stop


@section('content')
    @include('admin/regions/practice_groups/_form')

    <div class="form-group sepH_c">
        <button type="submit" class="btn btn-lg btn-primary ">Save</button>
        <a href="{{ URL::route('admin.regions.practice_groups_roster', array($region_id)) }}" class="btn btn-lg btn-default ">Cancel</a>
    </div>
    {{ Form::close() }}
    
@stop
