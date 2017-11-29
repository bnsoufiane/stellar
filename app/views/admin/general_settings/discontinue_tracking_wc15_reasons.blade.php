@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.discontinue_tracking_wc15_reasons.index') }}">Discontinue Tracking Reasons</a>
            </li>
        </ul>
    </nav>
@stop


@section('content')
    {{ Form::model(NULL, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

    <div class="">
        @foreach($discontinue_tracking_wc15_reasons as $discontinue_tracking_wc15_reason)
            <div class="input-group input-group-lg sepH_a @if ($errors->has('reason')) has-error @endif">
                {{Form::hidden('reason_ids[]', $discontinue_tracking_wc15_reason->id)}}
                {{ Form::text('reason[]', $discontinue_tracking_wc15_reason->reason, array('placeholder' => 'Phone Number', 'class' => 'form-control discontinue_tracking_wc15_reasons_input')) }}
                <a href="javascript:void(0);" class="actions_button add-option add_discontinue_tracking_reason"><i
                            class="icon_plus_alt"></i></a>
                <a href="javascript:void(0);" class="actions_button remove-option remove_discontinue_tracking_reason"><i
                            class="icon_minus_alt"></i></a>
                @if ($errors->has('reason'))
                    <span class="help-block">{{ $errors->first('reason') }}</span>
                @endif
            </div>
        @endforeach

        <div class="sepH_c text-right"></div>
        <a type="button" class="btn btn-link add_discontinue_tracking_reason">Add a new discontinue tracking wc15 reason</a>

        <div class="form-group sepH_c">
            <button type="submit" class="btn btn-lg btn-primary">Save</button>
            <a href="{{ URL::route('admin.discontinue_tracking_wc15_reasons.index') }}" class="btn btn-lg btn-default ">Cancel</a>
        </div>
        {{ Form::close() }}
@stop
