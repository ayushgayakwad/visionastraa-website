<?php
session_start();
session_unset();
session_destroy();
header('Location: https://visionastraa.com/erp/index.php');
exit;