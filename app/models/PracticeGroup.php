<?php

class PracticeGroup extends Eloquent
{
    protected $fillable = array('group_id', 'name', 'specialty', 'phone', 'fax', 'address', 'city', 'state', 'zip');

    protected static $rules = array(
        'group_id' => 'required|min:3|max:255|unique:doctors,pcp_id'
    );

    public function region()
    {
        return $this->belongsTo('Region');
    }

    public function doctors()
    {
        return $this->hasMany('Doctor');
    }

    public function programs()
    {
        return $this->belongsToMany('Program', 'practice_group_program', 'practice_group_id', 'program_id');
    }

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['group_id'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }

}
