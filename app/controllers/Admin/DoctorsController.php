<?php

namespace Admin;

use View;

class DoctorsController extends BaseController
{

    public function index($regionId, $practiceGroupId)
    {
        $region = \Region::find($regionId);
        $insurance_company = $region->insurance_company()->first();

        $practice_group = \PracticeGroup::find($practiceGroupId);
        $doctors = $practice_group->doctors()->get();

        $this->layout->content = View::make('admin/regions/practice_groups/doctors/index')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('practice_group', $practice_group)
            ->with('region_id', $regionId)
            ->with('practice_group_id', $practiceGroupId)
            ->with('doctors', $doctors)
            ->with('route', array('admin.doctors.update'))
            ->with('method', 'POST');
    }

    public function create($regionId, $practiceGroupId)
    {
        $practice_group = \PracticeGroup::find($practiceGroupId);
        $region = \Region::find($regionId);
        $insurance_company = $region->insurance_company()->first();

        $this->layout->content = View::make('admin/regions/practice_groups/doctors/create')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('region_id', $regionId)
            ->with('practice_group', $practice_group)
            ->with('practice_group_id', $practiceGroupId)
            ->with('doctor', new \Doctor())
            ->with('route', 'admin.doctors.store');
    }

    public function store()
    {
        $input = \Input::all();
        $validator = \Doctor::validate($input);

        if ($validator->fails()) {
            return \Redirect::route('admin.doctors.create')
                ->withInput()
                ->withErrors($validator);
        } else {
            $practice_group = \PracticeGroup::find($input['practice_group_id']);
            $doctor = \Doctor::create($input);
            $doctor->practice_group()->associate($practice_group);
            $doctor->save();

            return \Redirect::route('admin.regions.practice_groups_roster', $input['region_id'])
                ->with('success', 'A doctor has been successfully created.');
        }
    }

    public function edit($regionId, $practiceGroupId, $doctorId)
    {
        $practice_group = \PracticeGroup::find($practiceGroupId);
        $region = \Region::find($regionId);
        $insurance_company = $region->insurance_company()->first();

        $doctor = \Doctor::find($doctorId);

        $this->layout->content = View::make('admin/regions/practice_groups/doctors/edit')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('region_id', $regionId)
            ->with('practice_group', $practice_group)
            ->with('practice_group_id', $practiceGroupId)
            ->with('doctor', $doctor)
            ->with('route', array('admin.doctors.update', $doctorId))
            ->with('method', 'PUT');
    }

    public function update($id)
    {
        $input = \Input::all();
        $validator = \Doctor::validate($input, $id);

        if ($validator->fails()) {
            return \Redirect::route('admin.doctors.edit', $id)
                ->withInput()
                ->withErrors($validator);
        } else {
            $doctor = \Doctor::find($id);
            $doctor->fill($input);
            $doctor->practice_group()->associate(\PracticeGroup::find($input['practice_group']));
            $doctor->save();

            return \Redirect::route('admin.doctors.index')
                ->with('success', 'A doctor has been successfully updated.');
        }
    }

    public function destroy($id)
    {
        $doctor = \Doctor::find($id);

        if (!$doctor) {
            \App::abort(404);
        }

        $doctor->delete();
        return array('ok' => 1);
    }

    public function import_doctors($regionId)
    {
        $region = \Region::find($regionId);

        $this->layout = \View::make('admin.layouts.base_iframe');

        $this->layout->content = View::make('admin/regions/practice_groups/doctors/import_doctors')
            ->with('region', $region);
    }

    public function store_imported_doctors()
    {
        if (strpos(getcwd(), 'public') !== false) {
            $baselink = 'uploads/';
        } else {
            $baselink = 'public/uploads/';
        }
        $filename = $baselink . (\Input::get('file'));

        $data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        unset($data[0]);
        $data = array_values($data);

        $region = \Region::find(\Input::get('region'));
        $insurance_company = $region->insurance_company()->first();
        $insurance_company_id = $insurance_company->id;
        $region_id = $region->id;

        $practice_groups_ids = [];
        $duplicatedDoctors = [];

        foreach ($data as $row) {
            $rowData = explode(",", $row);
            if (count($rowData) == 4 && !empty($rowData[0]) && !empty($rowData[1])) {
                $practice_groups_ids[] = $rowData[0];
            }
        }

        $practice_groups = \PracticeGroup::whereIn('group_id', $practice_groups_ids)->lists('id', 'group_id');
        // deduce not matching practice groups
        $not_matching_practice_groups = array_unique(array_diff($practice_groups_ids, array_keys($practice_groups)));

        \DB::beginTransaction();

        $now = \Carbon\Carbon::now()->toDateTimeString();
        foreach ($data as $row) {
            $patientInfo = explode(",", $row);

            if (count($patientInfo) == 4 && !empty($rowData[0]) && !empty($rowData[1])) {

                if (in_array(trim($patientInfo[0]), $not_matching_practice_groups)) {
                    continue;
                }

                $tab = array();
                $tab['practice_group_id'] = trim($patientInfo[0]);
                $tab['pcp_id'] = trim($patientInfo[1]);
                $tab['first_name'] = trim($patientInfo[2]);
                $tab['last_name'] = trim($patientInfo[3]);
                $tab['created_at'] = $now;
                $tab['updated_at'] = $now;

                try {

                    \DB::table('doctors')->insert(
                        array('pcp_id' => $tab['pcp_id'], 'first_name' => $tab['first_name'], 'last_name' => $tab['last_name'],
                            'practice_group_id' => $practice_groups[$tab['practice_group_id']], 'created_at' => $now, 'updated_at' => $now));
                } catch (\Exception $e) {
                    //var_dump($e->getMessage());
                    $duplicatedDoctors[] = $tab['pcp_id'];
                }
            }
        }

        \DB::commit();

        $message = '';
        if (count($duplicatedDoctors) > 0) {
            $message .= 'These doctors already exist in the database: <br/>';
            foreach ($duplicatedDoctors as $item) {
                $message .= ($item . '<br/>');
            }
            $message .= '<br/>';
        }

        if (count($not_matching_practice_groups) > 0) {
            $message .= 'These practice groups IDs do not exist: <br/>';
            foreach ($not_matching_practice_groups as $item) {
                $message .= ($item . '<br/>');
            }
        }

        if (count($duplicatedDoctors) > 0 || count($not_matching_practice_groups) > 0) {
            // Create a csv file based on $message Begin
            if (strpos(getcwd(), 'public') !== false) {
                $baselink = 'uploads/';
            } else {
                $baselink = 'public/uploads/';
            }
            $filename = $baselink . uniqid() . ".csv";

            $delimiter = ",";
            $f = fopen("$filename", 'w');


            if (count($duplicatedDoctors) > 0) {
                $line = array("These doctors already exist in the database:");
                fputcsv($f, $line, $delimiter);
                foreach ($duplicatedDoctors as $item) {
                    fputcsv($f, array("$item"), $delimiter);
                }
            }

            if (count($not_matching_practice_groups) > 0) {
                $line = array("These practice groups IDs do not exist:");
                fputcsv($f, $line, $delimiter);
                foreach ($not_matching_practice_groups as $item) {
                    fputcsv($f, array("$item"), $delimiter);
                }
            }
            fclose($f);
            // Create a csv file based on $message End

            //return \Response::make(array('ok' => json_encode($message)), 201);
            return \Response::make(array('ok' => $message, 'result_file' => str_replace("public/", "", $filename)), 201);
        } else {
            return \Response::make(array('ok' => true), 201);
        }
    }


}
