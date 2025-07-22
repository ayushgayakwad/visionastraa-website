<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: https://visionastraa.com/manpower/index.php');
    exit;
}
if (isset($required_role) && $_SESSION['role'] !== $required_role) {
    header('Location: https://visionastraa.com/manpower/index.php');
    exit;
} 