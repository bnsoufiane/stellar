<?php

namespace Admin;

use View;

class InsuranceCompaniesController extends BaseController
{

    public function index()
    {
        $insurance_companies = \InsuranceCompany::all();

        $this->layout->content = View::make('admin/insurance_companies/index')
            ->with('insurance_companies', $insurance_companies);
    }

    public function create()
    {
        $this->layout->content = View::make('admin/insurance_companies/create')
            ->with('insurance_company', new \InsuranceCompany())
            ->with('route', 'admin.insurance_companies.store');
    }

    public function store()
    {
        $input = \Input::all();
        $validator = \InsuranceCompany::validate($input);

        if ($validator->fails()) {
            return \Redirect::route('admin.insurance_companies.create')
                ->withInput()
                ->withErrors($validator);
        } else {
            $insurance_company = \InsuranceCompany::create($input);

            return \Redirect::route('admin.insurance_companies.index')
                ->with('success', 'An insurance company has been successfully created.');
        }
    }

    public function show()
    {
        return 'show';
    }

    public function edit($id)
    {
        $insurance_company = \InsuranceCompany::find($id);
        $this->layout->content = View::make('admin/insurance_companies/edit')
            ->with('insurance_company', $insurance_company)
            ->with('route', array('admin.insurance_companies.update', $id))
            ->with('method', 'PUT');
    }

    public function update($id)
    {
        $input = \Input::all();
        $validator = \InsuranceCompany::validate($input, $id);

        if ($validator->fails()) {
            return \Redirect::route('admin.insurance_companies.edit', $id)
                ->withInput()
                ->withErrors($validator);
        } else {
            $insurance_company = \InsuranceCompany::find($id);
            $insurance_company->fill($input);
            $insurance_company->save();

            return \Redirect::route('admin.insurance_companies.index')
                ->with('success', 'An insurance company has been successfully updated.');
        }
    }

    public function destroy($id)
    {
        $insurance_company = \InsuranceCompany::find($id);

        if (!$insurance_company) {
            \App::abort(404);
        }

        try {
            $insurance_company->delete();
        } catch (\Exception $e) {
            return array('error' => 1);
        }

        return array('ok' => 1);
    }

    public function get_regions($id)
    {
        $insurance_company = \InsuranceCompany::find($id);

        return $insurance_company->get_regions_as_key_value_array();
    }

    public function get_first_trimester_regions($id)
    {
        $insurance_company = \InsuranceCompany::find($id);

        return $insurance_company->get_first_trimester_regions_as_key_value_array();
    }

    public function get_pregnancy_regions($id)
    {
        $insurance_company = \InsuranceCompany::find($id);

        return $insurance_company->get_pregnancy_regions_as_key_value_array();
    }

    public function get_wc15_regions($id)
    {
        $insurance_company = \InsuranceCompany::find($id);

        return $insurance_company->get_wc15_regions_as_key_value_array();
    }

}
