<?php
session_start();
header('Content-Type: application/json');

$isLoggedIn = isset($_SESSION['user_id']);

echo json_encode(['isLoggedIn' => $isLoggedIn]);
