<?php

class ManualOutreach extends Eloquent
{
    protected $fillable = array('patient_id', 'program_id', 'outreach_date', 'outreach_code', 'outreach_notes', 'created_by');

    protected static $rules = array();


    public static function validate($input, $id = null)
    {
        $rules = self::$rules;
        return Validator::make($input, $rules);
    }

}
