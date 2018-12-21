<?php

require_once 'vendor/autoload.php';
use GuzzleHttp\Client;

class Slack
{
    private static $base_url = 'https://slack.com/api/';
    private static $token = 'xoxp-xxxxxxxxxxxx-xxxxxxxxxxxx-xxxxxxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    public static function post($channel, $message)
    {
        $message = trim($message);

        // nao_sec
        $client = new Client();
        $data = [
            'token' => self::$nao_token,
            'channel' => $channel,
            'text' => $message,
            'username' => 'Aoba Suzukaze',
        ];
        $response = $client->post(self::$base_url . 'chat.postMessage', ['form_params' => $data]);

        // adi
        $client = new Client();
        $data = [
            'token' => self::$adi_token,
            'channel' => $channel,
            'text' => $message,
            'username' => 'Aoba Suzukaze',
        ];
        $response = $client->post(self::$base_url . 'chat.postMessage', ['form_params' => $data]);

        return $response->getBody()->getContents();
    }
}
