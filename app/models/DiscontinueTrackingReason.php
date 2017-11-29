<?php

class DiscontinueTrackingReason extends Eloquent
{
    protected $fillable = array('reason');

    protected static $rules = array(
        'reason' => 'required|min:5|max:255|unique:discontinue_tracking_reasons,reason'
    );

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['reason'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }
}
