<?php
require 'vendor/autoload.php';
use Aws\S3\S3Client;

// --- 1. KONFIGURASI S3 (IAM ROLE) ---
$bucket = 'nugwebphps3'; 
$region = 'us-east-1'; 

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $region
]);

// --- 2. KONFIGURASI DATABASE ---
$db_host = 'localhost';
$db_user = 'root';      
$db_pass = '';          
$db_name = 'smk_cloud_db'; 

// Koneksi awal ke MySQL
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("Koneksi MySQL Gagal: " . $conn->connect_error);
}

// Buat Database jika belum ada
$conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
$conn->select_db($db_name);

// Buat Tabel jika belum ada
$tableQuery = "CREATE TABLE IF NOT EXISTS assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    s3_key VARCHAR(255) NOT NULL,
    file_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($tableQuery);
?>