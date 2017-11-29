<?php

class VisitDateVerified extends Eloquent
{
    protected $fillable = array('title');
    protected $table = 'visit_date_verified';

    protected static $rules = array(
        'title' => 'required|min:1|max:255|unique:visit_date_verified,title'
    );

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['title'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }
}
