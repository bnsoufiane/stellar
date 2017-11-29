<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Incentive Reports</a></li>
        </ul>
    </nav>
@stop


@section('content')
    <div class="container-fluid member_roster_report">
        <div class="row">
            <div class="col-md-12">

                {{ Form::model($user, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}
                <div class="input-group input-group-lg sepH_a insurance_companies_area">
                    {{ Form::label('insurance_company', 'Select Insurance Company : ', array('class' => 'control-label')) }}
                    {{Form::select('insurance_company', $insurance_companies, Input::old('insurance_company'), array('class' => 'form-control'))}}
                </div>


                <div class="input-group input-group-lg sepH_a regions_area">
                    {{ Form::label('region', 'Select Region : ', array('class' => 'control-label')) }}
                    {{Form::select('region', $regions, Input::old('region'), array('class' => 'form-control'))}}
                </div>


                <div class="input-group input-group-lg sepH_a programs_area">
                    {{ Form::label('program', 'Select Program : ', array('class' => 'control-label')) }}
                    <select class="form-control" id="program" name="program">
                        <?php $first_program_type_is_pregnancy = false; ?>
                        @foreach($programs as $program)
                            <?php if ($program->type == \Program::TYPE_PREGNANCY) {
                                $first_program_type_is_pregnancy = true;
                            } ?>
                            <option value="{{$program->id}}" data-type="{{$program->type}}">{{$program->name}}</option>
                        @endforeach
                    </select>

                </div>

                <div class="input-group input-group-lg sepH_a date_range_area"
                     @if(count($programs)==0 || !$first_program_type_is_pregnancy)
                     style="display: none;"@endif>
                    {{ Form::label('date_range', 'Date Range : ', array('class' => 'control-label')) }}
                    {{ Form::text('date_range', Input::old('date_range'), array('class' => 'form-control daterange')) }}
                </div>

                <div class="input-group input-group-lg sepH_a all_dates_area @if ($errors->has('all_dates')) has-error @endif"
                     @if(count($programs)==0 || !$first_program_type_is_pregnancy)
                     style="display: none;"@endif>
                    {{ Form::checkbox('all_dates', null, null, array()) }}
                    {{ Form::label('all_dates', 'All Dates : ', array('class' => 'control-label control-label2')) }}
                    @if ($errors->has('all_dates'))
                        <span class="help-block">{{ $errors->first('all_dates') }}</span>
                    @endif
                </div>

                <div class="input-group input-group-lg sepH_a pregnancy_report_type_area">
                    {{ Form::label('report_version', 'Report Version : ', array('class' => 'control-label pregnancy_report_type')) }}
                    {{ Form::radio('report_version', \Program::MEMBER_ROSTER_REPORT_MEMBER_ROSTER, true) }}
                    Member Roster<br>
                    {{ Form::radio('report_version', \Program::MEMBER_ROSTER_REPORT_MEMBER_ENCOUNTERS) }}
                    Member Encounters
                </div>

                <div class="form-group sepH_c view_report_area">
                    <button type="submit" class="btn btn-lg btn-primary ">View Report</button>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>
@stop



@section('scripts')

    <script src="{{asset('assets/lib/date-range-picker/js/moment.min.js')}}"></script>
    <script src="{{asset('assets/lib/date-range-picker/js/jquery.daterangepicker.js')}}"></script>

    <script>
        var daterange = $('.daterange').dateRangePicker({}).bind('datepicker-change', function (event, obj) {
            $('.view_report_area').show();
            //$('input[name="startDate"]').val(obj.date1);
            //$('input[name="endDate"]').val(obj.date2);
        });
    </script>
@stop