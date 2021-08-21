<?php

$connection = mysqli_connect("localhost", "root", "", "loyalty");

if (!$connection) {
    echo "Could not connect to the database" . mysqli_error($connection);
}