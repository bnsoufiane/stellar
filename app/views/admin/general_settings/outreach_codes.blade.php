@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.outreach_codes.index') }}">Outreach Codes</a>
            </li>
        </ul>
    </nav>
@stop


@section('content')
    {{ Form::model(NULL, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

    <div class="">
        @foreach($outreach_codes as $outreach_code)
            <div class="input-group input-group-lg sepH_a @if ($errors->has('code')) has-error @endif">
                {{Form::hidden('code_ids[]', $outreach_code->id)}}
                {{ Form::text('code[]', $outreach_code->code, array('placeholder' => 'Outreach Code', 'class' => 'form-control outreach_codes_input')) }}
                {{ Form::text('code_name[]', $outreach_code->code_name, array('placeholder' => 'Code Name', 'class' => 'form-control outreach_codes_name_input')) }}
                <a href="javascript:void(0);" class="actions_button add-option add_outreach_code"><i
                            class="icon_plus_alt"></i></a>
                <a href="javascript:void(0);" class="actions_button remove-option remove_outreach_code"><i
                            class="icon_minus_alt"></i></a>
                @if ($errors->has('code'))
                    <span class="help-block">{{ $errors->first('code') }}</span>
                @endif
            </div>
        @endforeach

        <div class="sepH_c text-right"></div>
        <a type="button" class="btn btn-link add_outreach_code">Add a new outreach code</a>

        <div class="form-group sepH_c">
            <button type="submit" class="btn btn-lg btn-primary">Save</button>
            <a href="{{ URL::route('admin.outreach_codes.index') }}" class="btn btn-lg btn-default ">Cancel</a>
        </div>
        {{ Form::close() }}
@stop
