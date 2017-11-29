<?php

class Phone extends Eloquent
{
    protected $fillable = array('phone_number');

    protected static $rules = array(
        'phone_number' => 'required|min:5|max:255|unique:phones,phone_number'
    );

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['phone_number'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }

    public function totalMessagesSentToday()
    {
        return \DB::table('patient_contacted')->where('phone_id', '=', $this->id)->where('created_at', '>=', new DateTime('today'))->count();
    }

    public static function getTotalMessagesSentToday($phone_id)
    {
        return \DB::table('patient_contacted')->where('phone_id', '=', $phone_id)->where('created_at', '>=', new DateTime('today'))->count();
    }
}
