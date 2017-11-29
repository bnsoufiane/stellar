<?php

class Program extends Eloquent
{
    const PER_WEEK = 1;
    const PER_MONTH = 2;
    const PER_YEAR = 3;

    const TYPE_OTHER = 0;
    const TYPE_PREGNANCY = 1;
    const TYPE_POSTPARTUM = 2;
    const TYPE_A1C = 3;
    const TYPE_WC15_AHC = 4;
    const TYPE_WC15_KF = 5;
    const TYPE_FIRST_TRIMESTER = 6;

    const METRIC_NULL = 0;
    const METRIC_URINE = 1;
    const METRIC_BLOOD = 2;
    const METRIC_EYE = 3;
    const METRIC_BLOOD_AND_URINE = 4;
    const METRIC_BLOOD_URINE_EYE = 5;
    const METRIC_URINE_EYE = 6;
    const METRIC_BLOOD_EYE = 7;


    const PREGNANCY_REPORT_ACTIVE_PATIENT_OPT_IN = 0;
    const PREGNANCY_REPORT_ACTIVE_PATIENT_OUTREACH = 1;
    const PREGNANCY_REPORT_DELIVERY = 2;
    const PREGNANCY_REPORT_DELIVERY_OUTREACH = 3;
    const PREGNANCY_REPORT_DISCONTINUE = 4;
    const PREGNANCY_REPORT_DISCONTINUE_WITH_OUTREACHES = 5;
    const PREGNANCY_REPORT_BLANK_PREGNANCIES = 6;

    const FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OPT_IN = 1;
    const FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OUTREACH = 2;
    const FIRST_TRIMESTER_REPORT_DISCONTINUE = 3;
    const FIRST_TRIMESTER_REPORT_DISCONTINUE_WITH_OUTREACHES = 4;

    const WC15_REPORT_ACTIVE_CONFIRMED_OPT_IN = 1;
    const WC15_REPORT_ACTIVE_CONFIRMED_OUTREACH = 2;
    const WC15_REPORT_ACTIVE_NONCONFIRMED_OPT_IN = 3;
    const WC15_REPORT_ACTIVE_NONCONFIRMED_OUTREACH = 4;
    const WC15_REPORT_DISCONTINUE = 5;
    const WC15_REPORT_DISCONTINUE_WITH_OUTREACHES = 6;

    const PREGNANCY_38_WEEK_REPORT_All_Visits = 1;
    const PREGNANCY_38_WEEK_REPORT_Date_Only = 2;
    const PREGNANCY_36_WEEK_REPORT_All_Visits = 3;
    const PREGNANCY_36_WEEK_REPORT_Date_Only = 4;

    const INCENTIVE_REPORT_VERSION_OLD = 1;
    const INCENTIVE_REPORT_VERSION_NEW = 2;
    const INCENTIVE_REPORT_VERSION_OLD_DOS = 3; // Dos report ammendment 
    const INCENTIVE_REPORT_VERSION_NEW_DOS = 4; // DOS report ammendment
    

    const ENROLLED_BY_UNDEFINED = 0;
    const ENROLLED_BY_HC = 1;
    const ENROLLED_BY_STELLAR = 2;

    const USER_ACTIVITY_REPORT_OUTREACH = 1;
    const USER_ACTIVITY_REPORT_INCENTIVE = 2;
    const USER_ACTIVITY_REPORT_ALL = 3;

    const CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE = 1;
    const CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT = 2;
    const CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE_DOS = 3;
    const CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT_DOS = 4;

    const MEMBER_ROSTER_REPORT_MEMBER_ROSTER = 1;
    const MEMBER_ROSTER_REPORT_MEMBER_ENCOUNTERS = 2;


    protected $fillable = array('name', 'abbreviation', 'type', 'notes', 'sms_content', 'call_text', 'call_mp3', 'email_content', 'contact_frequency_times', 'contact_frequency_period', 'visit_requirement_times', 'visit_requirement_period');

    protected static $rules = array(
        'name' => 'required|min:2|max:255',
        'abbreviation' => 'min:1|max:20',
    );

    public function region()
    {
        return $this->belongsTo('Region');
    }

    public function patients()
    {
        return $this->belongsToMany('User', 'patient_program', 'program_id', 'patient_id');
    }

    public function practice_groups()
    {
        return $this->belongsToMany('PracticeGroup', 'practice_group_program', 'program_id', 'practice_group_id');
    }

    public function patient_notes($patient_id)
    {
        return \DB::table('patient_program')->select('patient_notes')->where('patient_id', '=', $patient_id)->where('program_id', '=', $this->id)->first();
    }

    public function patient_program($patient_id)
    {
        return \DB::table('patient_program')
            ->where('patient_id', '=', $patient_id)
            ->where('program_id', '=', $this->id)
            ->first();
    }

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['name'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }

    public function contact_frequency()
    {
        return $this->contact_frequency_times . " time" . (($this->contact_frequency_times !== 1) ? 's' : '') . " per " . $this->getPeriod($this->contact_frequency_period);
    }

    public function visit_requirement()
    {
        return $this->visit_requirement_times . " time" . (($this->visit_requirement_times !== 1) ? 's' : '') . " per " . $this->getPeriod($this->visit_requirement_period);
    }

    public function type()
    {
        switch ($this->type) {
            case self::TYPE_OTHER:
                return 'Other';
            case self::TYPE_PREGNANCY:
                return 'Pregnancy';
            case self::TYPE_POSTPARTUM:
                return 'Postpartum';
            case self::TYPE_A1C:
                return 'A1C';
            case self::TYPE_WC15_AHC:
                return 'WC15-AHC';
            case self::TYPE_WC15_KF:
                return 'WC15-KF';
            case self::TYPE_FIRST_TRIMESTER:
                return 'First Trimester';
        }

        return 'Undefined';
    }

    private function getPeriod($period)
    {
        if ($period == $this::PER_WEEK) {
            return 'week';
        } else if ($period == $this::PER_MONTH) {
            return 'month';
        } else if ($period == $this::PER_YEAR) {
            return 'year';
        }
        return '';
    }

    public function get_patients_as_key_value_array()
    {
        $patients_obj = $this->patients()->orderBy('username')->lists('username', 'id');

        $arWrapper['k'] = array_keys($patients_obj);
        $arWrapper['v'] = array_values($patients_obj);

        return json_encode($arWrapper);
    }

}
