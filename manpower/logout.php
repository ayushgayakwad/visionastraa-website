<?php
session_start();
session_unset();
session_destroy();
header('Location: https://visionastraa.com/manpower/index.php');
exit; 