<?php

//Script is gonna run for infinite amount of time (hence 0)
set_time_limit(0);
ini_set('default_socket_timeout', 300);
session_start();

//Instagram Client Keys
define("clientID", 'YOUR CLIENT ID HERE');
define("clientSecret", 'YOUR CLIENT SECRET HERE');
define("redirectURI", 'YOUR DOMAIN HERE/index.php');
define("imageDirectory", 'pics/');

//Function to connect to instagram (called 2 times in this code)
function connectToInstagram($url){
    $ch=curl_init();
    
    curl_setopt_array($ch, array(
       CURLOPT_URL => $url,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_SSL_VERIFYPEER => false,
       CURLOPT_SSL_VERIFYHOST => 2
    ));
    
    $result=curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

//Function to get user ID
function getUserID($userName){
    $url='https://api.instagram.com/v1/users/search?q='.$userName.'&client_id='.clientID;
    $instagramInfo=connectToInstagram($url);
    $results=json_decode($instagramInfo, true);
    
    return $results['data'][0]['id'];    
}

//Function to display instagram images from the profile to the webpage (clicking on them will give fb url of the img
function printImages($userID){
    $url='https://api.instagram.com/v1/users/'.$userID.'/media/recent?client_id='.clientID.'&count=5';
    $instagramInfo=connectToInstagram($url);
    $results=json_decode($instagramInfo, true);
    
    foreach($results['data'] as $items){
        $image_url=$items['images']['low_resolution']['url'];
        echo '<img src="'.$image_url.'"/> <br/>';
        savePicture($image_url);
    }
}

//Function to automatically save all this displayed instagram images to pics folder
function savePicture($image_url){
    echo $image_url .'<br />';
    $filename=basename($image_url);
    echo $filename .'<br />';
    
    $destination=imageDirectory.$filename;
    file_put_contents($destination, file_get_contents($image_url));
}

//If Instagram api verification is valid then proceed
if($_GET['code']){
    $code=$_GET['code'];
    $url="https://api.instagram.com/oauth/access_token";
    $acess_token_settings=array(
        'client_id'     => clientID,
        'client_secret' => clientSecret,
        'grant_type'    => 'authorization_code',
        'redirect_uri'  => redirectURI,
        'code'          => $code
    );
    $curl=curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $acess_token_settings);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $result=curl_exec($curl);
    curl_close($curl);
    
    $results=json_decode($result, true);
    $userName=$results['user']['username'];
    $userID=getUserID($userName);
    printImages($userID);
}

//Else redirect back to the index.php
else{     
?>   
 
<!doctype html>
<html>
    <body>
        <a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo clientID; ?>&redirect_uri=<?php echo redirectURI; ?>&response_type=code">Login</a>
    </body>
</html>



<?php
}
?>
