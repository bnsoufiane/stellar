@extends('admin.layouts.base')

@section('title'){{"$patient->username, $patient->last_name, $patient->first_name - Patient Program Report"}}@stop

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.patients.index') }}">Patients</a></li>
            <li><a href="#">Report</a></li>
        </ul>
    </nav>
@stop


@section('content')

    <div class="container-fluid export_report_page">
        <div class="row">
            <div class="col-md-12">
                <table id="datatable_patients_report" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">

                    @include('admin/users/patient_report/headers')
                    </tr>
                    @foreach ($programs as $program)
                        <tr>
                            <th>{{"$program->name Program"}}</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <td></td>
                            <td></td>
                        </tr>

                        <?php
                        $patient_program_visits = $patient->patient_program_visits_for_selected_year($program->id, $year);
                        ?>

                        @if($program->type==Program::TYPE_PREGNANCY)
                            @include('admin/users/patient_report/pregnancy_report')
                        @elseif($program->type==Program::TYPE_POSTPARTUM)
                            @include('admin/users/patient_report/post_partum_report')
                        @elseif($program->type==Program::TYPE_A1C)
                            @include('admin/users/patient_report/a1c_report')
                        @else
                            @include('admin/users/patient_report/generic_report')
                        @endif

                        <?php
                        $manual_outreaches = $patient->manual_outreaches($program->id);
                        ?>

                        @foreach ($manual_outreaches as $manual_outreach)
                            <tr>
                                <td></td>
                                <td>Outreach
                                    date: {{\Helpers::format_date_display($manual_outreach->outreach_date)}}</td>
                                <td>Outreach code: {{$manual_outreach->code_name}}</td>
                                <td></td>
                                <td>Outreach notes: {{$manual_outreach->outreach_notes}}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach


                    @endforeach
                </table>
            </div>
        </div>
    </div>
@stop

<link href="{{asset('assets/lib/DataTables/media/css/new_jquery.dataTables.min.css')}}" rel="stylesheet" media="all">
<link href="{{asset('assets/lib/DataTables/media/css/buttons.dataTables.min.css')}}" rel="stylesheet" media="all">


@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/new_jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>
    <!-- <script src="asset('assets/lib/DataTables/extensions/TableTools/js/dataTables.tableTools.min.js')}}"></script>-->

    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.buttons.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/pdfmake.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/vfs_fonts.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/buttons.html5.min.js')}}"></script>

    <script>

        var buttonCommon = {
            exportOptions: {
                format: {
                    header: function (data, row, column, node) {
                        console.log(data);
                        return data.replace(/<br\s*\/?>/ig, "\n");
                    }
                }
            }
        };

        mytable = $('#datatable_patients_report').DataTable({
            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            "bSort": false,
            columns: [
                null, null, null, null, null, null, null, null
            ],
            dom: 'Bfrtip',
            buttons: [
                'csvHtml5',
                $.extend(true, {}, buttonCommon, {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                })
            ]
        });

                <?php

                $current_year = date("Y");
                $base_url = URL::route("admin.patients.report", array($patient->id));

                $_obj = '<select id="patients_report_year" style="margin-top: 5px; margin-right: 5px;">';
                $_obj .= '<option ' . (($year == $current_year) ? "selected" : "") . ' value="' . $current_year . '" href="' . $base_url . '?year=' . $current_year . '">' . $current_year . '</option>';
                $_obj .= '<option ' . (($year == $current_year - 1) ? "selected" : "") . ' value="' . ($current_year - 1) . '" href="' . $base_url . '?year=' . ($current_year - 1) . '">' . ($current_year - 1) . '</option>';
                $_obj .= '<option ' . (($year == $current_year - 2) ? "selected" : "") . ' value="' . ($current_year - 2) . '" href="' . $base_url . '?year=' . ($current_year - 2) . '">' . ($current_year - 2) . '</option>';
                $_obj .= '<option ' . (($year == $current_year - 3) ? "selected" : "") . ' value="' . ($current_year - 3) . '" href="' . $base_url . '?year=' . ($current_year - 3) . '">' . ($current_year - 3) . '</option>';
                $_obj .= '</select>';

                ?>

        var _obj = '<?php echo $_obj ?>';
        $(_obj).prependTo('.dataTables_filter');


    </script>
@stop