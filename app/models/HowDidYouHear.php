<?php

class HowDidYouHear extends Eloquent
{
    protected $fillable = array('label');

    protected static $rules = array(
        'label' => 'required|min:5|max:255|unique:how_did_you_hears,label'
    );

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['label'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }
}
