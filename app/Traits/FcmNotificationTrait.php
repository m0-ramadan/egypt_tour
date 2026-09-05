<?php
namespace App\Traits;


trait FcmNotificationTrait {


    function send_notification_user($token,$message,$type)
	{

	//$API_ACCESS_KEY = 'AAAA9wSF4xo:APA91bErFARII9-QqLsJ7Jb3ZTCxugn6cIGNoHi8sFmzbKK0JbQbwsQ6hwoMfC-_reDhHxZNYIeXD8wePBElJwNDoAwbOVZ9RtQJO3uCtPc_F99tRpZjLffT8Wc7kWEldyuIvPzuma6B';
        $API_ACCESS_KEY = 'AAAAHRtT4Cg:APA91bHMIG6dzse3vOIROtJftdTTBUY5RINvxNlQeQpawd5MkNt9QKUWgj-Es2OKzf_i1U9XynPbWEdgAvYZP__PeGs25B-f_P_hx6UGHhjgjY0J2P6rZA2VryUqmq2W1x6_o9lUgoLa';

	   //  $ci= & get_instance();
		$fcmUrl = 'https://fcm.googleapis.com/fcm/send';
		$notification = [
            'title' =>$message['title'],
            'body' => $message['body'],
            'sound' => $message['sound'],
            'vibrate' => $message['vibrate'],
            'image' => $message['largeIcon'],
            'date' => date("Y-m-d")
        ];
        $extraNotificationData = ["message" => $notification,"type" =>$type,"click_action" =>'FLUTTER_NOTIFICATION_CLICK'];

        $fcmNotification = [
            //'registration_ids' => $tokenList, //multple token array
            'to'        => $token, //single token
            'notification' => $notification,
            'data' => $extraNotificationData
        ];

        $headers = [
            'Authorization: key=' . $API_ACCESS_KEY,
            'Content-Type: application/json'
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);
	return $result;
	}

    public function sendNotifications($body,$token,$title){

        $SERVER_API_KEY = 'AAAAHRtT4Cg:APA91bHMIG6dzse3vOIROtJftdTTBUY5RINvxNlQeQpawd5MkNt9QKUWgj-Es2OKzf_i1U9XynPbWEdgAvYZP__PeGs25B-f_P_hx6UGHhjgjY0J2P6rZA2VryUqmq2W1x6_o9lUgoLa';

        $data = [

            "registration_ids" => [
                $token
            ],

            "notification" => [
                "title" => $title,
                "body" => $body,
                "sound"=> "default" // required for sound on ios

            ],

        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        $response =curl_exec($ch);
        curl_close($ch);
        // dd($response);
        // $res = json_decode($response);
        return $response;
    }
}
