<?php

class PatientProgramVisit extends Eloquent
{
    protected $fillable = array('patient_id', 'program_id', 'actual_visit_date', 'scheduled_visit_date',
        'scheduled_visit_date_notes', 'incentive_type', 'incentive_value', 'gift_card_serial', 'incentive_date_sent',
        'doctor_id', 'metric', 'visit_notes', 'gift_card_returned', 'incentive_returned_date', 'gift_card_returned_notes',
        'sign_up', 'manually_added');

    protected static $rules = array();


    public static function validate($input, $id = null)
    {
        $rules = self::$rules;
        return Validator::make($input, $rules);
    }

}
