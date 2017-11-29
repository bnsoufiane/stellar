<?php $scheduled_visit_id = isset($actual_visit->id) ? $actual_visit->id : 'new_visit_0' ?>

<div class="scheduled_visit_fields_row">
    <div class="row sepH_a">
        <div class="col-lg-4">

            <div class="input-group input-group-lg sepH_a">
                <a data_href="#" class="remove_scheduled_row remove-option"><i
                            class="icon_minus_alt"></i></a>
                {{Form::hidden('scheduled_visit_ids[]', $scheduled_visit_id)}}
                {{ Form::label('scheduled_visit_'.$scheduled_visit_id, 'Scheduled Visit: ', array('class' => 'control-label control-label2')) }}
                {{ Form::text('scheduled_visit_'.$scheduled_visit_id, \Helpers::format_date_display($actual_visit->scheduled_visit_date), array('class' => 'form-control form-control2 datepicker', 'autocomplete' => 'off')) }}
            </div>
        </div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('actual_visit_'.$scheduled_visit_id, 'Actual Visit', array('class' => 'control-label')) }}
                {{ Form::text('actual_visit_'.$scheduled_visit_id, \Helpers::format_date_display($actual_visit->actual_visit_date), array('class' => 'form-control form-control2 datepicker disallow_future_dates', 'autocomplete' => 'off')) }}
            </div>
        </div>
    </div>


    @if(isset($actual_visit->estimated_date))
        <div class="row sepH_a">
            <div class="col-lg-4">
                <div class="input-group input-group-lg sepH_a">
                    {{ Form::label('estimated_date_'.$scheduled_visit_id, $visit_number.'. Estimated Date: ', array('class' => 'control-label control-label2')) }}
                    {{ Form::label('estimated_date_'.$scheduled_visit_id, \Helpers::format_date_display($actual_visit->estimated_date), array('class' => 'control-label control-label2', 'autocomplete' => 'off')) }}
                </div>
            </div>
            <div class="col-lg-8">
                <div class="input-group input-group-lg sepH_a">
                    {{ Form::label('visit_date_verified_by_'.$scheduled_visit_id, 'Visit Date Verified By', array('class' => 'control-label')) }}
                    {{Form::select('visit_date_verified_by_'.$scheduled_visit_id, $visit_date_verified, !empty($actual_visit->visit_date_verified_by)?$actual_visit->visit_date_verified_by:null, array('class' => 'form-control form-control2'))}}
                </div>
            </div>
        </div>
    @endif

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('julian_date_'.$scheduled_visit_id, 'Julian Date', array('class' => 'control-label')) }}
                {{ Form::text('julian_date_'.$scheduled_visit_id, !empty($actual_visit->julian_date)?$actual_visit->julian_date:null, array('class' => 'form-control form-control2 julian_date_field', 'readonly')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('doctor_id_'.$scheduled_visit_id, 'Doctor Id', array('class' => 'control-label')) }}
                {{ Form::text('doctor_id_'.$scheduled_visit_id, !empty($actual_visit->doctor_id)?$actual_visit->doctor_id:null, array('class' => 'form-control form-control2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('incentive_type_'.$scheduled_visit_id, 'Incentive Type', array('class' => 'control-label')) }}
                {{ Form::text('incentive_type_'.$scheduled_visit_id, $actual_visit->incentive_type, array('class' => 'form-control form-control2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('incentive_value_'.$scheduled_visit_id, 'Incentive Value: ', array('class' => 'control-label')) }}
                {{ Form::text('incentive_value_'.$scheduled_visit_id, $actual_visit->incentive_value, array('class' => 'form-control form-control2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('gift_card_serial_'.$scheduled_visit_id, 'Gift Card Serial: ', array('class' => 'control-label')) }}
                {{ Form::text('gift_card_serial_'.$scheduled_visit_id, $actual_visit->gift_card_serial, array('class' => 'form-control form-control2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('incentive_date_'.$scheduled_visit_id, 'Incentive Date: ', array('class' => 'control-label')) }}
                {{ Form::text('incentive_date_'.$scheduled_visit_id, \Helpers::format_date_display($actual_visit->incentive_date_sent), array('class' => 'form-control form-control2 datepicker disallow_future_dates', 'autocomplete' => 'off')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('visit_notes_'.$scheduled_visit_id, 'Visit Notes ', array('class' => 'control-label')) }}
                {{ Form::text('visit_notes_'.$scheduled_visit_id, $actual_visit->visit_notes, array('class' => 'form-control form-control2', 'autocomplete' => 'off')) }}
            </div>
        </div>
    </div>


    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::checkbox('gift_card_returned_'.$scheduled_visit_id, 'true', isset($actual_visit->gift_card_returned)?$actual_visit->gift_card_returned:null, array('class' => 'gift_card_returned')) }}
                {{ Form::label('gift_card_returned_'.$scheduled_visit_id, 'Gift Card Returned', array('class' => 'control-label control-label2')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('gift_card_returned_date_'.$scheduled_visit_id, 'Incentive Returned Date', array('class' => 'control-label')) }}
                {{ Form::text('gift_card_returned_date_'.$scheduled_visit_id, isset($actual_visit->incentive_returned_date)?\Helpers::format_date_display($actual_visit->incentive_returned_date):null, array('class' => 'form-control datepicker gift_card_returned_related_field', isset($actual_visit->gift_card_returned) && $actual_visit->gift_card_returned? '' : 'disabled')) }}
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('gift_card_returned_notes_'.$scheduled_visit_id, 'Incentive Returned Notes', array('class' => 'control-label')) }}
                {{ Form::text('gift_card_returned_notes_'.$scheduled_visit_id, isset($actual_visit->gift_card_returned_notes)?$actual_visit->gift_card_returned_notes:null, array('class' => 'form-control gift_card_returned_related_field', isset($actual_visit->gift_card_returned) &&$actual_visit->gift_card_returned? '' : 'disabled')) }}
            </div>
        </div>
    </div>

    <div class="manual_outreach_rows">

        <div class="manual_outreach_row">

            <div class="row sepH_a">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <div class="input-group input-group-lg sepH_a">
                        <?php $field_name = 'manual_outreach_' . $scheduled_visit_id . '[]'  ?>
                        {{ Form::checkbox($field_name, 'true', null, array('class' => 'manual_outreach_field')) }}
                        {{ Form::label($field_name, 'Manual Outreach', array('class' => 'control-label control-label2')) }}
                    </div>
                </div>
            </div>

            <div class="row sepH_a">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <div class="input-group input-group-lg sepH_a">
                        <?php $field_name = 'manual_outreach_date_' . $scheduled_visit_id . '[]'  ?>
                        {{ Form::label($field_name, 'Outreach Date', array('class' => 'control-label')) }}
                        {{ Form::text($field_name, Input::old('manual_outreach_date'), array('class' => 'form-control datepicker disallow_future_dates manual_outreach_related_field', 'disabled')) }}
                    </div>
                </div>
            </div>

            <div class="row sepH_a">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <div class="input-group input-group-lg sepH_a">
                        <?php $field_name = 'manual_outreach_code_' . $scheduled_visit_id . '[]'  ?>
                        {{ Form::label($field_name, 'Outreach Code', array('class' => 'control-label')) }}
                        {{Form::select($field_name, $outreach_codes, Input::old('manual_outreach_code'), array('class' => 'form-control form-control2 manual_outreach_related_field', 'disabled'))}}
                    </div>
                </div>
            </div>

            <div class="row sepH_a">
                <div class="col-lg-4"></div>
                <div class="col-lg-8">
                    <div class="input-group input-group-lg sepH_a">
                        <?php $field_name = 'manual_outreach_notes_' . $scheduled_visit_id . '[]'  ?>
                        {{ Form::label($field_name, 'Outreach Notes', array('class' => 'control-label')) }}
                        {{ Form::text($field_name, Input::old('manual_outreach_notes'), array('class' => 'form-control manual_outreach_related_field', 'disabled')) }}
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                <a class="btn btn-sm btn-primary add_pregnancy_outreach">Add New Outreach</a>
            </div>
        </div>
    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::checkbox('manually_added[]', 'true') }}
                {{ Form::label('manually_added[]', 'Manually Added', array('class' => 'control-label control-label2')) }}
            </div>
        </div>
    </div>

</div>