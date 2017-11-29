@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.member_completed_required_visit_dates.index') }}">Member Completed
                    Required Visit Dates</a>
            </li>
        </ul>
    </nav>
@stop


@section('content')
    {{ Form::model(NULL, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

    <div class="">
        @foreach($member_completed_required_visit_dates as $item)
            <div class="input-group input-group-lg sepH_a @if ($errors->has('title')) has-error @endif">
                {{Form::hidden('member_completed_required_visit_dates_ids[]', $item->id)}}
                {{ Form::text('member_completed_required_visit_dates[]', $item->title, array('placeholder' => 'Member Completed Required Visit Dates', 'class' => 'form-control member_completed_required_visit_dates_input')) }}
                <a href="javascript:void(0);" class="actions_button add-option add_member_completed_required_visit_dates"><i
                            class="icon_plus_alt"></i></a>
                <a href="javascript:void(0);"
                   class="actions_button remove-option remove_member_completed_required_visit_dates"><i
                            class="icon_minus_alt"></i></a>
                @if ($errors->has('title'))
                    <span class="help-block">{{ $errors->first('title') }}</span>
                @endif
            </div>
        @endforeach

        <div class="sepH_c text-right"></div>
        <a type="button" class="btn btn-link add_member_completed_required_visit_dates">Add a new Member Completed
            Required Visit Dates</a>

        <div class="form-group sepH_c">
            <button type="submit" class="btn btn-lg btn-primary">Save</button>
            <a href="{{ URL::route('admin.member_completed_required_visit_dates.index') }}"
               class="btn btn-lg btn-default ">Cancel</a>
        </div>
    {{ Form::close() }}
@stop
