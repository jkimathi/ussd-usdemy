<?php

header("content-type:text/plain");
include("connection.php");
include("functions.php");

// $session_id = $_POST['sessionId'];
// $service_code = $_POST['serviceCode'];
$phone = $_GET['phoneNumber'];
$text = $_GET['text'];

//create an array
$data = explode("*", $text);

$level = 0;
$level = count($data);

// echo $level;


if($level == 0 || $level == 1){
    main_menu();
}

if($level>1){
    switch($data[1]){
        case 1:
        check_customer_exist($phone);
        customer_register($data, $phone);
        break;

        case 2:
        check_password($data,$phone);
        transferPoints($data, $phone);
        break;

        case 3:
        check_password($data, $phone);
        purchaseItem($data,  $phone);
        break;

        case 4:
        check_password($data, $phone);
        checkPoints($data, $phone);
        break;

        case 5:
        check_user($phone);
        updatePassword($data, $phone);
        break;
        
        

        default:
        $text = "Invalid text Input\nplease insert a valid menu option";
        ussd_stop($text);
    }

}



?>