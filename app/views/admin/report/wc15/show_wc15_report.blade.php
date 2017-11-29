@extends('admin.layouts.base')

@section('title'){{"$insurance_company->name, $region->name, $program->name - Program Patient Report"}}@stop

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Report</a></li>
        </ul>
    </nav>
@stop


@section('content')

    <div class="container-fluid export_report_page">
        <div class="row">
            <div class="col-md-12">

                @if(($input['pregnancy_report_type'] == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OPT_IN)
                || ($input['pregnancy_report_type'] == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OPT_IN))

                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_wc15_report'))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('insurance_company', $insurance_company)
->setCustomValues('region', $region)
->setCustomValues('program', $program)
->setCustomValues('input', $input)
->render('admin/report/wc15/show_wc15_report_active_opt_in_datatable') }}

                @elseif(($input['pregnancy_report_type'] == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OUTREACH)
                || ($input['pregnancy_report_type'] == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OUTREACH))

                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_wc15_report'))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('insurance_company', $insurance_company)
->setCustomValues('region', $region)
->setCustomValues('program', $program)
->setCustomValues('input', $input)
->render('admin/report/wc15/show_wc15_report_active_outreach_datatable') }}

                @elseif($input['pregnancy_report_type'] == \Program::WC15_REPORT_DISCONTINUE)

                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_wc15_report'))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('insurance_company', $insurance_company)
->setCustomValues('region', $region)
->setCustomValues('program', $program)
->setCustomValues('input', $input)
->render('admin/report/wc15/show_wc15_report_discontinue_datatable') }}

                @elseif($input['pregnancy_report_type'] == \Program::WC15_REPORT_DISCONTINUE_WITH_OUTREACHES)

                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_wc15_report'))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('insurance_company', $insurance_company)
->setCustomValues('region', $region)
->setCustomValues('program', $program)
->setCustomValues('input', $input)
->render('admin/report/wc15/show_wc15_report_discontinue_with_outreaches_datatable') }}

                @endif


            </div>
        </div>
    </div>
@stop

