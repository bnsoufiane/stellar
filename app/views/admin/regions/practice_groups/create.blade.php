@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>Create Practice Group</li>
        </ul>
    </nav>
@stop


@section('content')
    @include('admin/regions/practice_groups/_form')

    <div class="form-group list-component" id="region-practice-groups">
        <a class="import_practice_groups" href="javascript:void(0);" region_id="{{$region_id}}"
           style="padding-left: 10px; margin-bottom: 15px;">Import Practice Groups</a>
    </div>

    <div class="form-group sepH_c">
        <button type="submit" class="btn btn-lg btn-primary ">Save</button>
        <a href="{{ URL::route('admin.regions.index') }}" class="btn btn-lg btn-default ">Cancel</a>
    </div>
    {{ Form::close() }}

@stop
