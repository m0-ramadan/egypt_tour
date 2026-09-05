<?php 
namespace App\Traits;
use App\Models\Client;
use Illuminate\Support\Facades\Notification;
trait SendNotificationTrait {
use FcmNotificationTrait; 
    public function sendNotify($action,$title,$firebase_id){
        // $users=Client::where('id',$user_id)->where('is_active',1)->first();
          $body=  $action;
          $token=$firebase_id;
     $msg = array(
    'title'     =>  $title,
   'body'    =>$action,
    'vibrate'   => 1,
    'sound'     => 1,
    'largeIcon' => 'https://taqiviolet.com/public/images/settings/CjNXMk2j7aZvFbC5k5LbDOoBhYylZTRxrSx4jSVU.png'
    );

   echo 
   
 $this->send_notification_user($token,$msg,2);
        //  foreach($tokens as $token){
        //  }
    }

}