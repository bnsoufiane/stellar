<?php

class Twilio
{
    const Contact_Tool_SMS = 0;
    const Contact_Tool_Phone = 1;
    const Contact_Tool_Email = 2;

    public static function contact_tool_toString($contact_tool)
    {
        switch ($contact_tool) {
            case self::Contact_Tool_SMS:
                return 'SMS';
            case self::Contact_Tool_Phone:
                return 'Phone';
            case self::Contact_Tool_Email:
                return 'Email';
        }
    }

    public static function send_sms($from, $to, $body)
    {
        $AccountSid = Config::get('app.twillio.AccountSid');
        $AuthToken = Config::get('app.twillio.AuthToken');

        $client = new Services_Twilio($AccountSid, $AuthToken);

        try {
            $message = $client->account->messages->create(array("From" => $from, "To" => $to, "Body" => $body));
            return true;
        } catch (Exception $e) {
            Log::error("From: " . $from . " | To: " . $to . " | Send Failed: " . $e->getMessage());
            return $e->getMessage();
        }
    }

    public static function make_call($from, $to, $twiml)
    {
        $AccountSid = Config::get('app.twillio.AccountSid');
        $AuthToken = Config::get('app.twillio.AuthToken');

        $client = new Services_Twilio($AccountSid, $AuthToken);

        try {
            $call = $client->account->calls->create($from, $to, "http://demo.twilio.com/docs/voice.xml", array());
            return true;
        } catch (Exception $e) {
            Log::error("From: " . $from . " | To: " . $to . " | Send Failed: " . $e->getMessage());
            return $e->getMessage();
        }
    }

}