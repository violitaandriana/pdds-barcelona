<?php
require 'vendor/autoload.php'; // Memuat autoload Composer

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->air_quality_db->air_quality_data;

$cursor = $collection->find();

$data = [];
foreach ($cursor as $document) {
    $data[] = $document;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
