<div>
    <div class="row sepH_a">
        <div class="col-lg-4">

            <div class="input-group input-group-lg sepH_a">
                <?php
                $scheduled_visit_date = ($actual_visits[0]->scheduled_visit_date == "0000-00-00 00:00:00") ? '' : date_format(date_create($actual_visits[0]->scheduled_visit_date), 'm/d/Y');
                ?>
                {{ Form::label('sign_up', 'Sign Up: ', array('class' => 'control-label control-label2')) }}
                {{ Form::text('sign_up', $sign_up_not_set?$scheduled_visit_date:null, array('class' => 'form-control form-control2 datepicker disallow_future_dates', 'autocomplete' => 'off')) }}
            </div>
        </div>
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
                $incentive_date_sent = \Helpers::format_date_display($actual_visits[0]->incentive_date_sent);
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
                {{ Form::label('sign_up_notes', 'Sign Up Notes ', array('class' => 'control-label')) }}
                {{ Form::text('sign_up_notes', $sign_up_not_set?$actual_visits[0]->visit_notes:null, array('class' => 'form-control form-control2', 'autocomplete' => 'off')) }}
            </div>
        </div>
    </div>


</div>