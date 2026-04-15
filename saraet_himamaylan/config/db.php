<?php
// config/db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'saraet_barangay_db');
define('SITE_NAME', 'Saraet Barangay Service Center');
define('SITE_LOCATION', 'Himamaylan City, Negros Occidental');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'DB connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function clean($db, $val) {
    return $db->real_escape_string(strip_tags(trim($val)));
}

function jsonOut($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}