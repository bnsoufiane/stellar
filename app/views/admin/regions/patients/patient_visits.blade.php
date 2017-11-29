@extends('admin.layouts.base')
@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.programs.index') }}">Regions</a></li>
            <li><a href="#">Patient Visits</a></li>
        </ul>
    </nav>
@stop


@section('content')
    @if (\Session::has('alert'))
        <div class="alert alert-danger">
            {{\Session::get('alert')}}
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            Region: {{$patient->region->abbreviation}}, {{$program->name}}
        </div>
    </div>

    <div class="row" style="margin-bottom: 30px !important; margin-top: 10px !important;">
        <div class="col-lg-2">
            Patient ID: {{$patient->username}}
        </div>
        <div class="col-lg-3">
            {{"$patient->last_name $patient->first_name"}}
        </div>

        <div class="col-lg-5">
            <a href="javascript:void(0);" class="btn btn-sm btn-primary edit_patient_info" type="button"
               patient_id="{{$patient->id}}" target="_blank">Edit Patient Info</a>
        </div>

    </div>

    @if(($program->type == \Program::TYPE_WC15_AHC) || ($program->type == \Program::TYPE_WC15_KF))
        <div class="row" style="margin-bottom: 30px !important; margin-top: -30px !important;">
            <div class="col-lg-5">
                Mother ID: {{\User::findUsernameById($patient->mother_id)}}
            </div>
        </div>
    @endif

    <div class="row" style="margin-bottom: 30px !important; margin-top: -30px !important;">
        <div class="col-lg-2">
            Date of Birth: {{\Helpers::format_date_display($patient->date_of_birth)}}
        </div>
        <div class="col-lg-3">
            {{"$patient->address1 $patient->address2, $patient->city, $patient->state, $patient->zip"}}
        </div>
    </div>

    <div class="row" style="margin-bottom: 30px !important; margin-top: -30px !important;">
        <div class="col-lg-2">
            Phone: {{$patient->phone1}}
        </div>
        <div class="col-lg-3">
            Trac Phone: {{$patient->trac_phone}}
        </div>
    </div>

    @if(($program->type == \Program::TYPE_PREGNANCY) && isset($last_closed_pregnancy) && $last_closed_pregnancy != null && (!isset($pregnancy) || $pregnancy == null))

        <div class="row">
            <div class="col-lg-2">
                Patient Notes :
            </div>
            <div class="col-lg-10">
                {{$program->patient_notes}}
            </div>
        </div>
        <div class="row" style="margin-top: 0px !important;">
            <div class="col-lg-2">
                Delivery Date :
            </div>
            <div class="col-lg-10">
                {{\Helpers::format_date_display($last_closed_pregnancy->delivery_date)}}
            </div>
        </div>
        <div class="row" style="margin-top: 0px !important;">
            <div class="col-lg-2">
                Discontinue Date :
            </div>
            <div class="col-lg-10">
                @if($last_closed_pregnancy->discontinue)
                    {{\Helpers::format_date_display($last_closed_pregnancy->discontinue_date)}}
                @endif
            </div>
        </div>
        <div class="row" style="margin-top: 0px !important;margin-bottom: 30px !important;">
            <div class="col-lg-2">
                Discontinue Reason :
            </div>
            <div class="col-lg-10">
                @if($last_closed_pregnancy->discontinue && !empty($last_closed_pregnancy->discontinue_reason_id))
                    {{$discontinue_tracking_reasons[$last_closed_pregnancy->discontinue_reason_id]}}
                @endif
            </div>
        </div>

        <div class="form-group sepH_c">
            {{--<a href="{{ URL::route('admin.programs.add_new_pregnancy', array($patient->id, $program->id)) }}"--}}
            <a id="add_new_pregnancy" class="btn btn-lg btn-primary" href="javascript:void(0)">Add New Pregnancy</a>
        </div>
    @elseif(($program->type == \Program::TYPE_PREGNANCY) && empty($pregnancy) && (!isset($last_closed_pregnancy) || $last_closed_pregnancy==null ))
        <div class="form-group sepH_c">
            <a id="add_new_pregnancy" href="javascript:void(0)" class="btn btn-lg btn-primary ">Add New Pregnancy</a>
        </div>
    @endif



    @if(!empty($previous_pregnancies))

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} previous pregnancies
                    for {{$program->region->name}} region</span><br/><br/>
                    <table id="datatable_previous_contacts" class="table table-striped table-bordered" cellspacing="0"
                           width="100%">
                        <thead>
                        <tr>
                            <th>Opt-in Date</th>
                            <th>Delivery Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($previous_pregnancies as $item)
                            <tr>
                                <td>
                                    <a href="{{ URL::route('admin.programs.patient_visits_program_instance', array($patient->id, $program->id, $item->id)) }}">{{\Helpers::format_date_display($item->date_added)}}</a>
                                </td>
                                <td>
                                    <a href="{{ URL::route('admin.programs.patient_visits_program_instance', array($patient->id, $program->id, $item->id)) }}">{{\Helpers::format_date_display($item->delivery_date)}}</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endif





    @if($program->type==Program::TYPE_POSTPARTUM)
        <div class="form-group sepH_c">
            <a id="add_new_post_partum"
               href="{{ URL::route('admin.programs.add_new_post_partum_instance', array($patient->id, $program->id)) }}"
               class="btn btn-lg btn-primary ">Add New Post Partum</a>
        </div>
    @endif

    @if(!empty($post_partum_instances))

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} Post Partum Instances
                    for {{$program->region->name}} region</span><br/><br/>
                    <table id="datatable_previous_contacts" class="table table-striped table-bordered" cellspacing="0"
                           width="100%">
                        <thead>
                        <tr>
                            <th>Creation Date</th>
                            <th>Delivery Date</th>
                            <th>Post Partum Start</th>
                            <th>Post Partum End</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($post_partum_instances as $item)
                            <tr>
                                <td>
                                    <a href="{{ URL::route('admin.programs.patient_visits_program_instance', array($patient->id, $program->id, $item->id)) }}">{{\Helpers::format_date_display($item->created_at)}}</a>
                                </td>
                                <td>
                                    <a href="{{ URL::route('admin.programs.patient_visits_program_instance', array($patient->id, $program->id, $item->id)) }}">{{\Helpers::format_date_display($item->delivery_date)}}</a>
                                </td>
                                <td>
                                    <a href="{{ URL::route('admin.programs.patient_visits_program_instance', array($patient->id, $program->id, $item->id)) }}">{{\Helpers::format_date_display($item->postpartum_start)}}</a>
                                </td>
                                <td>
                                    <a href="{{ URL::route('admin.programs.patient_visits_program_instance', array($patient->id, $program->id, $item->id)) }}">{{\Helpers::format_date_display($item->postpartum_end)}}</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endif







    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} all incentives
                    for {{$program->region->name}} region</span><br/><br/>
                <table id="datatable_previous_contacts" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>Program</th>
                        <th>Actual Visit Date</th>
                        <th>Incentive Type</th>
                        <th>Incentive Value</th>
                        <th>Gift Card Serial</th>
                        <th>Incentive Date Sent</th>
                        <th>Incentive Returned</th>
                        <th>Incentive Returned Date</th>
                        <th>Gift Card Returned Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($incentives_list as $item)
                        <tr>
                            <td>{{$item->name}}</td>
                            <td>{{$item->actual_visit_date}}</td>
                            <td>{{$item->incentive_type}}</td>
                            <td>{{$item->incentive_value}}</td>
                            <td>{{$item->gift_card_serial}}</td>
                            <td>{{\Helpers::format_date_display($item->incentive_date_sent)}}</td>
                            <td>
                                @if($item->gift_card_returned)
                                    {{"Yes"}}
                                @else
                                    {{"No"}}
                                @endif
                            </td>
                            <td>{{\Helpers::format_date_display($item->incentive_returned_date)}}</td>
                            <td>{{$item->gift_card_returned_notes}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="8">Total Incentives for this year :</th>
                        <th>{{"$$total_incentives"}}</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>









    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} contact dates
                    for {{$program->name}} program</span><br/><br/>
                <table id="datatable_previous_contacts" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Contact Tool</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($previous_contacts as $previous_contact)
                        <tr>
                            <td>{{$previous_contact->created_at}}</td>
                            <td>{{\twilio::contact_tool_toString($previous_contact->contact_tool)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <br/><br/>

                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} actual visit dates
                    for {{$program->name}} program</span><br/><br/>
                <table id="datatable_actual_visits" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Incentive Type</th>
                        <th>Gift Card Serial</th>
                        <th>Incentive Date Sent</th>
                        @if($program->type==Program::TYPE_A1C)
                            <th>Metric</th>
                        @endif
                        <th>Notes</th>
                        <th>Doctor ID</th>
                        <th style="width: 120px; text-align: center;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($actual_visits as $actual_visit)
                        @if(($actual_visit->actual_visit_date!=="0000-00-00 00:00:00") &&
                         ($actual_visit->actual_visit_date !=null)
                          // && !$actual_visit->sign_up ||
                          // ( $actual_visit->sign_up && $program->type!= \Program::TYPE_WC15_KF && $program->type!= \Program::TYPE_WC15_AHC)
                         )
                            <tr>
                                <td>{{\Helpers::format_date_display($actual_visit->actual_visit_date)}}</td>
                                <td>{{$actual_visit->incentive_type}}</td>
                                <td>{{$actual_visit->gift_card_serial}}</td>
                                <td>{{\Helpers::format_date_display($actual_visit->incentive_date_sent)}}</td>
                                @if($program->type==Program::TYPE_A1C)
                                    <?php
                                    $metric = 'Undefined';
                                    switch ($actual_visit->metric) {
                                        case \Program::METRIC_URINE:
                                            $metric = 'Urine';
                                            break;
                                        case \Program::METRIC_BLOOD:
                                            $metric = 'Blood';
                                            break;
                                        case \Program::METRIC_EYE:
                                            $metric = 'Eye';
                                            break;
                                        case \Program::METRIC_BLOOD_AND_URINE:
                                            $metric = 'Blood & Urine';
                                            break;
                                        case \Program::METRIC_BLOOD_URINE_EYE:
                                            $metric = 'Blood, Urine, Eye';
                                            break;
                                        case \Program::METRIC_URINE_EYE:
                                            $metric = "Urine & Eye";
                                            break;
                                        case \Program::METRIC_BLOOD_EYE:
                                            $metric = "Blood & Eye";
                                            break;
                                    }
                                    ?>

                                    <td>{{$metric}}</td>
                                @endif
                                <td>{{$actual_visit->visit_notes}}</td>
                                <td>{{$actual_visit->doctor_id}}</td>

                                <td style="text-align: center;">
                                    <a href="{{ URL::route('admin.patient_program_visits.edit', array($patient->id, $program->id, $actual_visit->id)) }}"
                                       class="btn btn-sm btn-primary" type="button">Edit</a>
                                    <a href="{{ URL::route('admin.patient_program_visits.destroy', array($actual_visit->id)) }}"
                                       class="btn btn-sm btn-primary delete_actual_visit" type="button"
                                       data-action="remove">Delete</a>
                                </td>

                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>

                <br/><br/>

                <span class="heading_text">{{"$patient->last_name $patient->first_name"}} returned gift cards
                    for {{$program->name}} program</span><br/><br/>
                <table id="datatable_returned_gift_cards" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">

                    <thead>
                    <tr>
                        <th>Actual Visit Date</th>
                        <th>Incentive Type</th>
                        <th>Gift Card Serial</th>
                        <th>Incentive Date Sent</th>
                        <th>Incentive Returned Date</th>
                        <th>Gift Card Returned Notes</th>
                        <th style="width: 150px; text-align: center;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($actual_visits as $actual_visit)
                        @if($actual_visit->actual_visit_date!=="0000-00-00 00:00:00" && $actual_visit->actual_visit_date !=null && $actual_visit->gift_card_returned)
                            <tr>
                                <td>{{\Helpers::format_date_display($actual_visit->actual_visit_date)}}</td>
                                <td>{{$actual_visit->incentive_type}}</td>
                                <td>{{$actual_visit->gift_card_serial}}</td>
                                <td>{{\Helpers::format_date_display($actual_visit->incentive_date_sent)}}</td>
                                <td>{{\Helpers::format_date_display($actual_visit->incentive_returned_date)}}</td>
                                <td>{{$actual_visit->gift_card_returned_notes}}</td>

                                <td style="text-align: center;">
                                    <a href="{{ URL::route('admin.patient_program_visits.edit', array($patient->id, $program->id, $actual_visit->id)) }}"
                                       class="btn btn-sm btn-primary" type="button">Edit</a>
                                    <a href="{{ URL::route('admin.patient_program_visits.destroy', array($actual_visit->id)) }}"
                                       class="btn btn-sm btn-primary delete_actual_visit" type="button"
                                       data-action="remove">Delete</a>
                                </td>

                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>


                @if($program->type==Program::TYPE_PREGNANCY)
                    <br/><br/>

                    <span class="heading_text">{{"$patient->last_name $patient->first_name"}} scheduled visit dates
                    for {{$program->name}} program</span><br/><br/>
                    <table id="datatable_actual_visits" class="table table-striped table-bordered" cellspacing="0"
                           width="100%">
                        <thead>
                        <tr>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($actual_visits as $actual_visit)
                            @if($actual_visit->scheduled_visit_date!=="0000-00-00 00:00:00" && $actual_visit->scheduled_visit_date !=null && !$actual_visit->sign_up)
                                <tr>
                                    <td>{{\Helpers::format_date_display($actual_visit->scheduled_visit_date)}}</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>

                @endif

                <br/><br/>

                <span class="heading_text">Manual Outreaches</span><br/><br/>
                <table id="datatable_manual_outreaches" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>Outreach Date</th>
                        <th>Outreach Code</th>
                        <th>Outreach Notes</th>
                        <th>Created By</th>
                        <th style="width: 120px; text-align: center;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($manual_outreaches as $manual_outreach)
                        <tr>
                            <td>{{\Helpers::format_date_display($manual_outreach->outreach_date)}}</td>
                            <td>{{$manual_outreach->code_name}}</td>
                            <td>{{$manual_outreach->outreach_notes}}</td>
                            <td>{{$manual_outreach->created_by}}</td>
                            <td style="text-align: center;">
                                <a href="{{ URL::route('admin.manual_outreaches.edit', array($patient->id, $program->id, $manual_outreach->id)) }}"
                                   class="btn btn-sm btn-primary" type="button">Edit</a>
                                <a href="{{ URL::route('admin.manual_outreaches.destroy', array($manual_outreach->id)) }}"
                                   class="btn btn-sm btn-primary" type="button" data-action="remove">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>


                @if($program->type==Program::TYPE_PREGNANCY)
                    @include('admin/regions/patients/visits/pregnancy')
                @elseif($program->type==Program::TYPE_POSTPARTUM)
                    @if($program_instance != null)
                        @include('admin/regions/patients/visits/post_partum')
                    @endif
                @elseif($program->type==Program::TYPE_A1C)
                    @include('admin/regions/patients/visits/a1c')
                @elseif(($program->type==Program::TYPE_WC15_AHC) || ($program->type==Program::TYPE_WC15_KF))
                    @include('admin/regions/patients/visits/wc15_ach')
                @elseif($program->type==Program::TYPE_FIRST_TRIMESTER)
                    @include('admin/regions/patients/visits/first_trimester')
                @else
                    @include('admin/regions/patients/visits/generic')
                @endif

            </div>
        </div>
    </div>

@stop



@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>

    <script>
        $(function () {
            var table = $('#datatable_previous_contacts').DataTable({
                "iDisplayLength": 25,
                "stateSave": true
            });

            var table2 = $('#datatable_actual_visits').DataTable({
                "iDisplayLength": 25,
                "stateSave": true
            });

            var table3 = $('#datatable_manual_outreaches').DataTable({
                "iDisplayLength": 25,
                "stateSave": true
            });
        })
    </script>
@stop