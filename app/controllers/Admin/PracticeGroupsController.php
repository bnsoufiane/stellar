<?php

namespace Admin;

use View;

class PracticeGroupsController extends BaseController
{

    public function index($region_id)
    {
        $region = \Region::find($region_id);
        $insurance_company = $region->insurance_company()->first();

        $user = \User::find(\Sentry::getUser()->id);
        $isSysAdmin = $user->isSysAdmin();

        $query = \DB::table('practice_groups')->where('region_id', '=', $region->id);

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($query)
                ->showColumns('group_id', 'name', 'specialty', 'phone', 'fax', 'address', 'city', 'state', 'zip')
                ->showColumns('id', 'region_id')
                ->searchColumns('group_id', 'name', 'specialty', 'phone', 'fax', 'address', 'city', 'state', 'zip')
                ->orderColumns('group_id', 'name', 'specialty', 'phone', 'fax', 'address', 'city', 'state', 'zip')
                ->make();
        }

        $this->layout->content = View::make('admin/regions/practice_groups/index')
            ->with('isSysAdmin', $isSysAdmin)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region);
    }

    public function full_list_cvs($regionId)
    {
        $region = \Region::where('id', $regionId)->first();
        $practice_groups = \DB::table('practice_groups')->get();

        $delimiter = ",";
        $filename = $region->name . " - Practice Groups Full List.csv";

        $f = fopen('php://memory', 'w');

        $line = array('Group ID', 'Name', 'Specialty', 'Phone', 'Fax', 'Address', 'City', 'State', 'Zip');
        fputcsv($f, $line, $delimiter);

        foreach ($practice_groups as $practice_group) {
            $line = array("$practice_group->group_id",
                "$practice_group->name",
                "$practice_group->specialty",
                "$practice_group->phone",
                "$practice_group->fax",
                "$practice_group->address",
                "$practice_group->city",
                "$practice_group->state",
                "$practice_group->zip");

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function create($regionId)
    {
        $region = \Region::find($regionId);
        $insurance_company = $region->insurance_company()->first();

        $this->layout->content = View::make('admin/regions/practice_groups/create')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('region_id', $regionId)
            ->with('practice_group', new \PracticeGroup())
            ->with('programs', array())
            ->with('route', 'admin.practice_groups.store');
    }

    public function store()
    {
        $input = \Input::all();
        $validator = \PracticeGroup::validate($input);

        if ($validator->fails()) {
            return \Redirect::route('admin.regions.create_practice_group', array($input['region_id']))
                ->withInput()
                ->withErrors($validator);
        } else {
            $region = \Region::find($input['region_id']);
            $practice_group = \PracticeGroup::create($input);
            $practice_group->region()->associate($region);
            $practice_group->save();

            return \Redirect::route('admin.regions.index')
                ->with('success', 'A practice group has been successfully created.');
        }
    }

    public function edit($region_id, $practice_group_id)
    {
        $region = \Region::find($region_id);
        $insurance_company = $region->insurance_company()->first();

        $practice_group = \PracticeGroup::find($practice_group_id);

        $this->layout->content = View::make('admin/regions/practice_groups/edit')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('practice_group', $practice_group)
            ->with('region_id', $region_id)
            ->with('route', array('admin.practice_groups.update', $practice_group_id))
            ->with('method', 'PUT');
    }

    public function update($practice_group_id)
    {
        $input = \Input::all();
        $validator = \PracticeGroup::validate($input, $practice_group_id);

        if ($validator->fails()) {
            return \Redirect::route('admin.practice_groups.edit', array($input['region_id'], $practice_group_id))
                ->withInput()
                ->withErrors($validator);
        } else {
            $practice_group = \PracticeGroup::find($practice_group_id);
            $practice_group->fill($input);
            $practice_group->save();

            return \Redirect::route('admin.regions.practice_groups_roster', $input['region_id'])
                ->with('success', 'A practice group has been successfully updated.');
        }
    }

    public function destroy($id)
    {
        $practice_group = \PracticeGroup::find($id);

        if (!$practice_group) {
            \App::abort(404);
        }

        $practice_group->delete();
        return array('ok' => 1);
    }

    public function import_practice_groups($regionId)
    {
        $region = \Region::find($regionId);
        $all_programs = $region->programs()->get();

        $this->layout = \View::make('admin.layouts.base_iframe');

        $this->layout->content = View::make('admin/regions/practice_groups/import_practice_groups')
            ->with('region', $region)
            ->with('programs', array())
            ->with('all_programs', $all_programs)
            ->with('route', array('admin.regions.store_patients', $regionId));
    }

    public function store_imported_practice_groups()
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

        $region = \Region::where('id', \Input::get('region'))->first();
        $insurance_company = $region->insurance_company()->first();
        $insurance_company_id = $insurance_company->id;
        $region_id = $region->id;

        \DB::beginTransaction();

        $now = \Carbon\Carbon::now()->toDateTimeString();
        foreach ($data as $row) {
            $patientInfo = explode(",", $row);

            if (count($patientInfo) == 9) {
                $tab = array();
                $tab['group_id'] = trim($patientInfo[0]);
                $tab['name'] = trim($patientInfo[1]);
                $tab['specialty'] = trim($patientInfo[2]);
                $tab['phone'] = trim($patientInfo[3]);
                $tab['fax'] = trim($patientInfo[4]);
                $tab['address'] = trim($patientInfo[5]);
                $tab['city'] = trim($patientInfo[6]);
                $tab['state'] = trim($patientInfo[7]);
                $tab['zip'] = trim($patientInfo[8]);
                $tab['created_at'] = $now;
                $tab['updated_at'] = $now;

                try {
                    $practice_group = \PracticeGroup::create($tab);
                    $practice_group->region()->associate($region);
                    $practice_group->save();

                } catch (\Exception $e) {
                    //var_dump($e->getMessage());
                    $duplicatedPracticeGroups[] = $tab['group_id'];
                }
            }
        }

        \DB::commit();

        $message = '';
        if (count($duplicatedPracticeGroups) > 0) {
            $message .= 'These practice groups are duplicated: <br/>';
            foreach ($duplicatedPracticeGroups as $item) {
                $message .= ($item . '<br/>');
            }
            $message .= '<br/>';
        }

        if (isset($duplicatedPracticeGroups)) {
            // Create a csv file based on $message Begin
            if (strpos(getcwd(), 'public') !== false) {
                $baselink = 'uploads/';
            } else {
                $baselink = 'public/uploads/';
            }
            $filename = $baselink . uniqid() . ".csv";

            $delimiter = ",";
            $f = fopen("$filename", 'w');

            $line = array("These practice groups are duplicated:");
            fputcsv($f, $line, $delimiter);

            foreach ($duplicatedPracticeGroups as $item) {
                fputcsv($f, array("$item"), $delimiter);
            }

            fclose($f);
            // Create a csv file based on $message End
            return \Response::make(array('ok' => $message, 'result_file' => str_replace("public/", "", $filename)), 201);
        } else {
            return \Response::make(array('ok' => true), 201);
        }
    }

}
