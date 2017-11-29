<?php

class Doctor extends Eloquent
{
    protected $fillable = array('pcp_id', 'first_name', 'last_name');

    protected static $rules = array(
        'pcp_id' => 'required|min:3|max:255|unique:doctors,pcp_id',
        'first_name' => 'required|min:2|max:255',
        'last_name' => 'required|min:2|max:255'
    );


    public function practice_group()
    {
        return $this->belongsTo('PracticeGroup');
    }

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['pcp_id'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }

}
