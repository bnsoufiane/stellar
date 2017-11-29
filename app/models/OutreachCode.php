<?php

class OutreachCode extends Eloquent
{
    protected $fillable = array('code', 'code_name');

    protected static $rules = array(
        'code' => 'required|min:1|max:255|unique:outreach_codes,code'
    );

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['code'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }
}
