<?php

//main menu
function main_menu()
{
    $text = "Welcome to Loyalty, Reply with\n1. Register\n2. Transfer Points\n3. Purchase Item with Points\n4. Check Points Balance\n5. Update your password";
    ussd_proceed($text);
}



function check_customer_exist($phone)
{
    global $connection;
    $select_phone = "select * from customer where phoneNumber = '$phone'";
    $query = mysqli_query($connection, $select_phone) or die("There is an error" . mysqli_error($connection));

    $check = mysqli_num_rows($query);

    if ($check > 0) {
        $text = "This phonenumber $phone is already registerd";
        ussd_stop($text);
    }
}

//customer register
function customer_register($data)
{
    global $connection;
    if (count($data) == 2) {
        $text = "Enter your firstname";
        ussd_proceed($text);
    }

    if (count($data) == 3) {
        $text = "Enter your middlename";
        ussd_proceed($text);
    }
    if (count($data) == 4) {
        $text = "Enter your lastname";
        ussd_proceed($text);
    }
    if (count($data) == 5) {
        $text = "Enter your gender(Male or Female)";
        ussd_proceed($text);
    }
    if (count($data) == 6) {
        $text = "Enter your id number";
        ussd_proceed($text);
    }
    if (count($data) == 7) {
        $text = "Enter your email address";
        ussd_proceed($text);
    }
    if (count($data) == 8) {
        $text = "Enter your four digit password";
        ussd_proceed($text);
    }
    if (count($data) == 9) {
        $phone = $_GET['phoneNumber'];
        $firstname = $data[2];
        $middlename = $data[3];
        $lastname = $data[4];
        $gender = $data[5];
        $idNumber = $data[6];
        $email = $data[7];
        $password = $data[8];

        // $password = md5($password);

        $sql = "insert into customer(phoneNumber, firstname, middlename, lastname, gender, idNumber, email, password, registerDate) values('$phone', '$firstname', '$middlename', '$lastname', '$gender', '$idNumber', '$email', '$password', Now())";

        $result = mysqli_query($connection, $sql) or die("There was an error" . mysqli_error($connection));

        if ($result == 1) {
            $text = "You have successfully registered";
            ussd_stop($text);
        }
    }
}


function check_password($data, $phone)
{
    global $connection;

    if (count($data) == 2) {
        $text = "Please enter your password";
        ussd_proceed($text);
    }

    if (count($data) == 3) {
        $phone = $_GET['phoneNumber'];
        $password = $data[2];

        // $password = md5($password);

        $statement = "select * from customer where phoneNumber='$phone' and password='$password'";
        $result = mysqli_query($connection, $statement) or die("There is an error" . mysqli_error($connection));
        $check = mysqli_num_rows($result);

        if ($check > 0) {
            return true;
        } else {
            $text = "Please check your password or try to register again";
            ussd_stop($text);
        }
    }
}


//added points
function addedPoints($phone)
{
    global $connection;

    $added = "select sum(points) from points where phonenumber= '$phone'";
    $received = "select sum(points) from transferpoints where toPhonenumber='$phone'";

    $query = mysqli_query($connection, $added) or die("There is an error " . mysqli_error($connection));
    $query1 = mysqli_query($connection, $received) or die("There is an error" . mysqli_error($connection));

    $row = mysqli_fetch_array($query);
    $row1 = mysqli_fetch_array($query1);

    $added = $row[0];
    $received = $row1[0];

    $addedPoints = $added + $received;
    return $addedPoints;
}

//used points 
function usedPoints($phone)
{
    global $connection;

    $purchaseItem = "select sum(points) from purchaseitem where phonenumber='$phone'";
    $transferedPoints = "select sum(points) from transferpoints where fromPhonenumber='$phone'";

    $query = mysqli_query($connection, $purchaseItem) or die("there is an error" . mysqli_error($connection));
    $query1 = mysqli_query($connection, $transferedPoints) or die("There is an error" . mysqli_error($connection));

    $row = mysqli_fetch_array($query);
    $row1 = mysqli_fetch_array($query1);

    $purchaseItem = $row[0];
    $transferedPoints = $row1[0];

    $usedPoints = $purchaseItem + $transferedPoints;

    return $usedPoints;
}

//check if customer exist
function check_user($phone)
{
    global $connection;
    $select = "select * from customer where phoneNumber = '$phone'";
    $query = mysqli_query($connection, $select) or die("There is an error" . mysqli_error($connection));

    $result = mysqli_num_rows($query);
    if ($result == 1) {
        return true;
    } else {
        $text = "This account phonenumber $phone is not registered";
        ussd_stop($text);
    }
}

//reference number
function referenceNumber($length)
{
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($chars), 0, $length);
}



//transfer points
function transferPoints($data, $phone)
{
    //we should suffient points when transfering
    //should not transfer points to your own account
    //should not transfer less than 5 points
    global $connection;
    $available = addedPoints($phone) - usedPoints($phone);
    if (count($data) == 3) {
        $text = "Please enter the phonenumber\nyou wish to transfer points to";
        ussd_proceed($text);
    }
    if (count($data) ==  4) {
        check_user($data[3]);
    }
    if (count($data) == 4) {
        $text = "Please enter the amount of points\nyou wish to transfer";
        ussd_proceed($text);
    }
    if (count($data) == 5) {
        $fromPhonenumber = $_GET['phoneNumber'];
        $toPhonenumber = $data[3];
        $points = $data[4];
        $referenceNumber = "TPL" . referenceNumber(9);

        if ($points > $available) {
            $text = "You have insufficient balance\nyour balance is $available";
            ussd_stop($text);
        }
        if ($toPhonenumber == $fromPhonenumber) {
            $text = "You cannot transfer points to your own account";
            ussd_stop($text);
        }
        if ($points < 5) {
            $text = "you cannot transfer less than 5 points";
            ussd_stop($text);
        } else {
            $sql = "insert into transferpoints(fromPhonenumber, toPhonenumber, points, referenceNumber, dateTime) values('$fromPhonenumber', '$toPhonenumber', '$points', '$referenceNumber', Now())";
            $result = mysqli_query($connection, $sql) or die("There is an error" . mysqli_error($connection));

            if ($result == 1) {
                $text = "You have successfully transfered $points to $toPhonenumber. Your confirmation code is $referenceNumber";
                ussd_stop($text);
            }
        }
    }
}

//purchasing points
function purchaseItem($data,  $phone)
{
    global $connection;
    $available = addedPoints($phone) - usedPoints($phone);

    if (count($data) == 3) {
        $text  = "Please Enter the amount of points you wish to use";
        ussd_proceed($text);
    }
    if (count($data) == 4) {
        $phone  = $_GET['phoneNumber'];
        $points = $data[3];
        $referenceNumber = "PIW" . referenceNumber(7);

        if ($points > $available) {
            $text = "You have insufficient point balance\nYour current balance is $available";
            ussd_stop($text);
        } else {
            $sql = "insert into purchaseitem(phonenumber,points,referenceNumber,dateTime)  values('$phone','$points','$referenceNumber', Now()) ";
            $result = mysqli_query($connection, $sql) or die("There is an error" . mysqli_error($connection));
            if ($result == 1) {
                $text = "You have purchased an item with $points points. Your confirmation code is $referenceNumber";
                ussd_stop($text);
            }
        }
    }
}

//check points balance
function checkPoints($data, $phone)
{
    if (count($data) == 3) {
        $availbale = addedPoints($phone) - usedPoints($phone);
        $text = "Your points balance is $availbale";
        ussd_stop($text);
    }
}

///update your password
function updatePassword($data, $phone)
{
    global $connection;

    if (count($data) == 2) {
        $text = "Please enter your new password";
        ussd_proceed($text);
    }
    if (count($data) == 3) {
        $phone = $_GET['phoneNumber'];
        $password = $data[2];

        // $password = md5($password);

        $sql = "UPDATE customer SET password ='$password' WHERE phoneNumber = '$phone' ";
        $result = mysqli_query($connection, $sql);

        if ($result == 1) {
            $text = "You have successfully update your password";
            ussd_stop($text);
        }
    }
}


//continue with the session
function ussd_proceed($text)
{
    echo "CON " . $text;
}

//end the session
function ussd_stop($text)
{
    echo "END " . $text;
    exit(0);
}