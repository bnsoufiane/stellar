<?php

class InsuranceCompany extends Eloquent
{
    protected $fillable = array('name');

    protected static $rules = array(
        'name' => 'required|min:5|max:255'
    );

    public static function validate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['name'] .= ',' . $id;
        }

        return Validator::make($input, $rules);
    }

    public function regions()
    {
        return $this->hasMany('Region');
    }

    public function patients()
    {
        return $this->hasMany('User')->where('region_id', '<>', 'NULL');
    }

    public function clients()
    {
        return $this->hasMany('User')->where('region_id', '=', Null);
    }

    public function get_regions_as_key_value_array()
    {
        $regions_obj = $this->regions;
        $regions = [];
        foreach ($regions_obj as $region) {
            $regions[$region->id] = $region->name;
        }

        return $regions;
    }

    public function get_pregnancy_regions_as_key_value_array()
    {
        return \DB::table('regions')
            ->where('insurance_company_id', '=', $this->id)
            ->join('programs', 'regions.id', '=', 'programs.region_id')
            ->where('programs.type', '=', Program::TYPE_PREGNANCY)
            ->select('regions.name', 'regions.id')
            ->lists('name', 'id');
    }

    public function get_wc15_regions_as_key_value_array()
    {
        return \DB::table('regions')
            ->where('insurance_company_id', '=', $this->id)
            ->join('programs', 'regions.id', '=', 'programs.region_id')
            ->where(function ($query) {
                $query->where('programs.type', '=', Program::TYPE_WC15_AHC)
                    ->orWhere('programs.type', '=', Program::TYPE_WC15_KF);
            })
            ->select('regions.name', 'regions.id')
            ->lists('name', 'id');
    }

    public function get_first_trimester_regions_as_key_value_array()
    {
        return \DB::table('regions')
            ->where('insurance_company_id', '=', $this->id)
            ->join('programs', 'regions.id', '=', 'programs.region_id')
            ->where('programs.type', '=', Program::TYPE_FIRST_TRIMESTER)
            ->select('regions.name', 'regions.id')
            ->lists('name', 'id');
    }

}
