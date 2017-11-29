<?php

class MainController extends BaseController
{

    public function index()
    {
        $user = Sentry::getUser();

        if ($user) {
            return Redirect::route('admin.index');
        } else {
            return Redirect::route('sign-in');
        }
    }

    public function cron_job()
    {
        $this->layout = \View::make('layouts.sign-in');

        $this->layout->content = View::make('cron-job')
            ->with('error', Session::get('error'));
    }

    public function outreach()
    {
        $response = '';

        $patients_group = Sentry::findGroupByName('Patient');
        $patients = \User::ofGroup($patients_group)->get();
        $phones_pool = \Phone::all()->toArray();
        $current_phone_index = 0;

        foreach ($patients as $patient) {

            $response .= "<br/><br/>" . $patient->last_name . ", " . $patient->first_name . "<br/>";

            $programs = $patient->programs()->get();
            foreach ($programs as $program) {
                $response .= $program->name . "<br/>";

                if ($patient->contactNow($program)) {
                    $response .= "Should be contacted now<br/>";

                    /* TODO */
                    /*
                     * - check cell phone and send sms
                     * - check phone then call
                     * - check email then send email
                     * - mark patient as 'not able to contact'
                     */

                } else {
                    $response .= "Don\'t contact yet<br/>";
                }
            }
        }

        $response .= "<br/><br/><br/>------------------------------------------------------------------------------------------------<br/><br/><br/>";

        return array('response' => $response);
    }

    public function send_sms()
    {
        $body = "Test message! twillio";
        $from = "+13346895468";
        //$to = "+212697585364";
        //$to = '+14849958305';
        //$to = '+212624830016';
        $to = '+212662152302';
        //$to = "+16789296440";

        //$isOk = \Twilio::send_sms($from, $to, $body);
        $isOk = \Twilio::make_call($from, $to, '');
        if ($isOk !== true) {
            return $isOk;
        }

        return 'send sms';


        $patients_group = Sentry::findGroupByName('Patient');
        $patients = \User::ofGroup($patients_group)->get();
        $phones_pool = \Phone::all()->toArray();
        $current_phone_index = 0;

        /* Begin ******** send some sample messages (22) and mark them as sent with dates and phone used */
        /*
        $i = 0;
        foreach ($patients as $patient) {

            $programs = $patient->programs()->get();
            foreach ($programs as $program) {
                var_dump($program->name);


                \DB::table('patient_contacted')->insert(
                    array('patient_id' => $patient->id, 'program_id' => $program->id, 'contact_tool' => Twilio::Contact_Tool_SMS,
                        'phone_id' => $phones_pool[$current_phone_index]->id, 'status' => 1,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
            }

            if ($i > 9) {
                break;
            }

        }

        /* End ******** send some sample messages (22) and mark them as sent with dates and phone used */

        echo '<pre>';
        /* Begin ****** Find amount of msgs sent by each number */

        $i = 0;
        while (true) {

            if (!isset($phones_pool[0])) {
                return 'no available phones for today';
            }

            if (\Phone::getTotalMessagesSentToday($phones_pool[0]['id']) >= Config::get('app.twillio.maxDailyNumberUse')) {
                unset ($phones_pool[0]);
                $phones_pool = array_values($phones_pool);
            } else {
                break;
            }
        }

        $remainingMessagesForThisPhone = Config::get('app.twillio.maxDailyNumberUse') - \Phone::getTotalMessagesSentToday($phones_pool[0]['id']);

        //var_dump(count($patients));die();

        $i = 1;
        foreach ($patients as $patient) {
            $programs = $patient->programs()->get();
            foreach ($programs as $program) {
                $remainingMessagesForThisPhone--;
                echo "message sent : $remainingMessagesForThisPhone  : $i <br/>";

                if ($remainingMessagesForThisPhone == 0) {
                    unset ($phones_pool[0]);
                    $phones_pool = array_values($phones_pool);
                    if (!isset($phones_pool[0])) {
                        return 'no more available phones for today';
                    } else {
                        $remainingMessagesForThisPhone = Config::get('app.twillio.maxDailyNumberUse');
                    }

                }

            }

            $i++;
        }

        /* End ****** Find amount of msgs sent by each number */
        echo '</pre>';

        return '';
        $body = "Test message! twillio";
        $from = "+13346895468";
        //$to = "+212697585364";
        //$to = '+212624830016';
        $to = '+212662152302';
        //$to = "+16789296440";

        $isOk = \Twilio::send_sms($from, $to, $body);
        if ($isOk !== true) {
            return $isOk;
        }

        return 'send sms';
    }
}
