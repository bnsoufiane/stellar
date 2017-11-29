@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.visit_date_verified.index') }}">Discontinue Tracking Reasons</a>
            </li>
        </ul>
    </nav>
@stop


@section('content')
    {{ Form::model(NULL, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

    <div class="">
        @foreach($visit_date_verified as $item)
            <div class="input-group input-group-lg sepH_a @if ($errors->has('title')) has-error @endif">
                {{Form::hidden('visit_date_verified_ids[]', $item->id)}}
                {{ Form::text('visit_date_verified[]', $item->title, array('placeholder' => 'Visit Date Verified By', 'class' => 'form-control visit_date_verified_input')) }}
                <a href="javascript:void(0);" class="actions_button add-option add_visit_date_verified"><i
                            class="icon_plus_alt"></i></a>
                <a href="javascript:void(0);" class="actions_button remove-option remove_visit_date_verified"><i
                            class="icon_minus_alt"></i></a>
                @if ($errors->has('title'))
                    <span class="help-block">{{ $errors->first('title') }}</span>
                @endif
            </div>
        @endforeach

        <div class="sepH_c text-right"></div>
        <a type="button" class="btn btn-link add_visit_date_verified">Add a new Visit Date Verified By</a>

        <div class="form-group sepH_c">
            <button type="submit" class="btn btn-lg btn-primary">Save</button>
            <a href="{{ URL::route('admin.visit_date_verified.index') }}" class="btn btn-lg btn-default ">Cancel</a>
        </div>
    {{ Form::close() }}
@stop
