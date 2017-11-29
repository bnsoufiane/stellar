<div style="margin-bottom: 50px;">
    <div class="row sepH_a">
        <div class="col-lg-4">

            <div class="input-group input-group-lg sepH_a">
                <?php
                if (!isset($actual_visits[0])) {
                    $scheduled_visit_date = null;
                } else {
                    $scheduled_visit_date = ($actual_visits[0]->scheduled_visit_date == "0000-00-00 00:00:00") ? '' : date_format(date_create($actual_visits[0]->scheduled_visit_date), 'm/d/Y');
                }
                ?>
                {{ Form::label('sign_up', 'Sign Up: ', array('class' => 'control-label control-label2')) }}
                {{ Form::text('sign_up', $sign_up_not_set?$scheduled_visit_date:null, array('class' => 'form-control form-control2 datepicker disallow_future_dates', 'autocomplete' => 'off')) }}
            </div>
        </div>
        <div class="col-lg-8">
            <div class="input-group input-group-lg sepH_a">
                {{ Form::label('sign_up_notes', 'Sign Up Notes ', array('class' => 'control-label')) }}
                {{ Form::text('sign_up_notes', $sign_up_not_set?$actual_visits[0]->visit_notes:null, array('class' => 'form-control form-control2', 'autocomplete' => 'off')) }}
            </div>
        </div>
    </div>

    @if($program->type == \Program::TYPE_WC15_KF)

        <div class="row sepH_a">
            <div class="col-lg-4"></div>
            <div class="col-lg-8">
                <div class="input-group input-group-lg sepH_a">
                    {{ Form::label('sign_up_incentive_type', 'Incentive Type: ', array('class' => 'control-label')) }}
                    {{ Form::text('sign_up_incentive_type', $sign_up_not_set?$actual_visits[0]->incentive_type:null, array('class' => 'form-control form-control2')) }}
                </div>
            </div>
        </div>

        <div class="row sepH_a">
            <div class="col-lg-4"></div>
            <div class="col-lg-8">
                <div class="input-group input-group-lg sepH_a">
                    {{ Form::label('sign_up_incentive_value', 'Incentive Value: ', array('class' => 'control-label')) }}
                    {{ Form::text('sign_up_incentive_value', $sign_up_not_set?$actual_visits[0]->incentive_value:null, array('class' => 'form-control form-control2')) }}
                </div>
            </div>
        </div>

        <div class="row sepH_a">
            <div class="col-lg-4"></div>
            <div class="col-lg-8">
                <div class="input-group input-group-lg sepH_a">
                    {{ Form::label('sign_up_gift_card_serial', 'Gift Card Serial: ', array('class' => 'control-label')) }}
                    {{ Form::text('sign_up_gift_card_serial', $sign_up_not_set?$actual_visits[0]->gift_card_serial:null, array('class' => 'form-control form-control2')) }}
                </div>
            </div>
        </div>

        <div class="row sepH_a">
            <div class="col-lg-4"></div>
            <div class="col-lg-8">
                <div class="input-group input-group-lg sepH_a">
                    <?php
                    if (!isset($actual_visits[0])) {
                        $incentive_date_sent = null;
                    } else {
                        $incentive_date_sent = \Helpers::format_date_display($actual_visits[0]->incentive_date_sent);
                    }
                    ?>
                    {{ Form::label('sign_up_incentive_date', 'Incentive Date: ', array('class' => 'control-label')) }}
                    {{ Form::text('sign_up_incentive_date', $sign_up_not_set?$incentive_date_sent:null, array('class' => 'form-control form-control2 datepicker disallow_future_dates', 'autocomplete' => 'off')) }}
                </div>
            </div>
        </div>

        <div class="row sepH_a">
            <div class="col-lg-4"></div>
            <div class="col-lg-8">
                <div class="input-group input-group-lg sepH_a">

                    {{ Form::label('sign_up_gift_card_returned', 'Gift Card Returned', array('class' => 'control-label control-label2')) }}
                    {{ Form::checkbox('sign_up_gift_card_returned', 'true', $sign_up_not_set?$actual_visits[0]->gift_card_returned:null, array('class' => 'gift_card_returned')) }}
                </div>
            </div>
        </div>

        <div class="row sepH_a">
            <div class="col-lg-4"></div>
            <div class="col-lg-8">
                <div class="input-group input-group-lg sepH_a">
                    <?php
                    if (!isset($actual_visits[0])) {
                        $incentive_date_sent = null;
                    } else {
                        $gift_card_returned_date = isset($actual_visits[0]->incentive_returned_date) ? \Helpers::format_date_display($actual_visits[0]->incentive_returned_date) : null;

                    }
                    ?>
                    {{ Form::label('sign_up_gift_card_returned_date', 'Incentive Returned Date: ', array('class' => 'control-label')) }}
                    {{ Form::text('sign_up_gift_card_returned_date', $sign_up_not_set?$gift_card_returned_date:null, array('class' => 'form-control form-control2 datepicker disallow_future_dates', 'autocomplete' => 'off')) }}
                </div>
            </div>
        </div>

        <div class="row sepH_a">
            <div class="col-lg-4"></div>
            <div class="col-lg-8">
                <div class="input-group input-group-lg sepH_a">
                    {{ Form::label('sign_up_gift_card_returned_notes', 'Incentive Returned Notes ', array('class' => 'control-label')) }}
                    {{ Form::text('sign_up_gift_card_returned_notes', $sign_up_not_set?$actual_visits[0]->gift_card_returned_notes:null, array('class' => 'form-control form-control2', 'autocomplete' => 'off')) }}
                </div>
            </div>
        </div>

        <div class="manual_outreach_rows">

            <div class="manual_outreach_row">

                <div class="row sepH_a">
                    <div class="col-lg-4"></div>
                    <div class="col-lg-8">
                        <div class="input-group input-group-lg sepH_a">
                            <?php $field_name = 'sign_up_manual_outreach[]'  ?>
                            {{ Form::checkbox($field_name, 'true', null, array('class' => 'manual_outreach_field')) }}
                            {{ Form::label($field_name, 'Manual Outreach', array('class' => 'control-label control-label2')) }}
                        </div>
                    </div>
                </div>

                <div class="row sepH_a">
                    <div class="col-lg-4"></div>
                    <div class="col-lg-8">
                        <div class="input-group input-group-lg sepH_a">
                            <?php $field_name = 'sign_up_manual_outreach_date[]'  ?>
                            {{ Form::label($field_name, 'Outreach Date', array('class' => 'control-label')) }}
                            {{ Form::text($field_name, Input::old('manual_outreach_date'), array('class' => 'form-control datepicker disallow_future_dates manual_outreach_related_field', 'disabled')) }}
                        </div>
                    </div>
                </div>

                <div class="row sepH_a">
                    <div class="col-lg-4"></div>
                    <div class="col-lg-8">
                        <div class="input-group input-group-lg sepH_a">
                            <?php $field_name = 'sign_up_manual_outreach_code[]'  ?>
                            {{ Form::label($field_name, 'Outreach Code', array('class' => 'control-label')) }}
                            {{Form::select($field_name, $outreach_codes, Input::old('manual_outreach_code'), array('class' => 'form-control form-control2 manual_outreach_related_field', 'disabled'))}}
                        </div>
                    </div>
                </div>

                <div class="row sepH_a">
                    <div class="col-lg-4"></div>
                    <div class="col-lg-8">
                        <div class="input-group input-group-lg sepH_a">
                            <?php $field_name = 'sign_up_manual_outreach_notes[]'  ?>
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

                    {{ Form::label('sign_up_manually_added', 'Manually Added', array('class' => 'control-label control-label2')) }}
                    {{ Form::checkbox('sign_up_manually_added', 'true', $sign_up_not_set?$actual_visits[0]->manually_added:null, array('class' => 'control-label control-label2')) }}
                </div>
            </div>
        </div>

    @endif

</div>