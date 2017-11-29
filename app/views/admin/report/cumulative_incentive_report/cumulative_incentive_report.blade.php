<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Cumulative Incentive Report</a></li>
        </ul>
    </nav>
@stop


@section('content')
    <div class="container-fluid program_report pregnancy_report">
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

                <div class="input-group input-group-lg sepH_a date_range_area">
                    {{ Form::label('date_range', 'Date Range : ', array('class' => 'control-label')) }}
                    {{ Form::text('date_range', Input::old('date_range'), array('class' => 'form-control daterange')) }}
                </div>

                <div class="input-group input-group-lg sepH_a pregnancy_report_type_area" style="display: none;">
                    {{ Form::label('pregnancy_report_type', 'Report Type : ', array('class' => 'control-label pregnancy_report_type')) }}

                    {{ Form::radio('cumulative_incentive_report_type', \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE, true) }}
                    Incentive Date: Cumulative Incentive Report<br>
                    {{ Form::radio('cumulative_incentive_report_type', \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT) }}
                    Incentive Date: Gift Card Count Report<br>
                     {{ Form::radio('cumulative_incentive_report_type', \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE_DOS) }}
                    DOS: Cumulative Incentive Report<br>
                     {{ Form::radio('cumulative_incentive_report_type', \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT_DOS) }}
                    DOS: Gift Card Count Report<br>
                </div>

                <div class="form-group sepH_c view_report_area" style="display: none;">
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
            $('.pregnancy_report_type_area').show();
            //$('input[name="startDate"]').val(obj.date1);
            //$('input[name="endDate"]').val(obj.date2);
        });
    </script>
@stop