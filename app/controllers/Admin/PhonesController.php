<?php

namespace Admin;

use View;

class PhonesController extends BaseController {

    public function index()
    {
        $phones = \Phone::all();
        $this->layout->content = View::make('admin/general_settings/phones')
            ->with('phones', $phones)
            ->with('route', array('admin.phones.update'))
            ->with('method', 'POST');
    }

    public function update()
    {
        $phone_numbers = \Input::get('phone_number');
        $phone_numbers_ids = \Input::get('phone_number_ids');

        try {
            if(isset($phone_numbers)){
                $i=0;
                $updated_phones = [];
                foreach($phone_numbers as $phone_number){
                    if(strlen ($phone_number)<5){
                        continue;
                    }
                    if($phone_numbers_ids[$i]!=0){
                        $phone = \Phone::find($phone_numbers_ids[$i]);
                    }else{
                        $phone = new \Phone();
                    }
                    $phone->phone_number = $phone_number;
                    $phone->save();
                    $updated_phones[] = $phone->id;
                    $i++;
                }
                if(count($updated_phones)){
                    \Phone::whereNotIn('id', $updated_phones)->delete();
                }
            }else{
                \Phone::whereNotIn('id', [])->delete();
            }

            return \Redirect::route('admin.phones.index')
                ->with('success', 'phones have been successfully updated.');

        } catch (\Exception $e) {
            return \Redirect::route('admin.phones.index')
                ->with('error', 'An error has occurred.');
        }


    }

}
