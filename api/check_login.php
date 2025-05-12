<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

echo json_encode(['isLoggedIn' => $isLoggedIn]);
