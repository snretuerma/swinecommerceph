<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSMS implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $recepient;

    /**
     * Create a new job instance.
     *
     * @param  String   $message
     * @param  String   $recepient
     * @return void
     */
    public function __construct($message, $recepient)
    {
        $this->message = $message;
        $this->recepient = $recepient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $arr_post_body = array(
            "message_type" => "SEND",
            "mobile_number" => $this->str_replace_first('0', '63', $this->recepient),
            "shortcode" => "292909000",
            "message_id" => rand(0,1000000), //to be improved if messages will be stored in db
            "message" => $this->message,
            "client_id" => config('services.chikka.id'),
            "secret_key" => config('services.chikka.secret')
        );

        $query_string = http_build_query($arr_post_body);
        $URL = config('services.chikka.url');

        $curl_handler = curl_init($URL);

        curl_setopt($curl_handler, CURLOPT_URL, $URL);
        curl_setopt($curl_handler, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_handler, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_handler, CURLOPT_POST, count($arr_post_body));
        curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $query_string);
        curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, TRUE);

        $response = curl_exec($curl_handler);
        $info = curl_getinfo($curl_handler, CURLINFO_HTTP_CODE);

        curl_close($curl_handler);
    }

    /**
     * Replace a substring of a string to a desired substring
     * Ex. '0' to '63' for mobile numbers
     *
     * @param   String      $from
     * @param   String      $to
     * @param   String      $subject
     * @return  String
     */
    private function str_replace_first($from, $to, $subject){
        $from = '/'.preg_quote($from, '/').'/';
        return preg_replace($from, $to, $subject, 1);
    }
}
