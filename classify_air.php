<?php
include './includes/head.php';
require_once 'autoload.php';

$client = new MongoDB\Client();
$collection = $client->barcelona->air_qualities;
$districtNames = $collection->distinct('district_name');

// Function to classify quality based on value and ranges
function classify_quality($value, $ranges) {
    foreach ($ranges as $range) {
        if ($value >= $range['min'] && $value <= $range['max']) {
            return $range['quality'];
        }
    }
    return 'Unknown';
}

// Define the ranges for O3, NO2, and PM10
$o3Ranges = [
    ['min' => 0, 'max' => 54, 'quality' => 'Good'],
    ['min' => 55, 'max' => 70, 'quality' => 'Moderate'],
    ['min' => 71, 'max' => 85, 'quality' => 'Unhealthy for Sensitive Groups'],
    ['min' => 86, 'max' => 105, 'quality' => 'Unhealthy'],
    ['min' => 106, 'max' => 200, 'quality' => 'Very Unhealthy'],
    ['min' => 201, 'max' => 604, 'quality' => 'Hazardous']
];

$no2Ranges = [
    ['min' => 0, 'max' => 53, 'quality' => 'Good'],
    ['min' => 54, 'max' => 100, 'quality' => 'Moderate'],
    ['min' => 101, 'max' => 360, 'quality' => 'Unhealthy for Sensitive Groups'],
    ['min' => 361, 'max' => 649, 'quality' => 'Unhealthy'],
    ['min' => 650, 'max' => 1249, 'quality' => 'Very Unhealthy'],
    ['min' => 1250, 'max' => 2049, 'quality' => 'Hazardous']
];

$pm10Ranges = [
    ['min' => 0, 'max' => 54, 'quality' => 'Good'],
    ['min' => 55, 'max' => 154, 'quality' => 'Moderate'],
    ['min' => 155, 'max' => 254, 'quality' => 'Unhealthy for Sensitive Groups'],
    ['min' => 255, 'max' => 354, 'quality' => 'Unhealthy'],
    ['min' => 355, 'max' => 424, 'quality' => 'Very Unhealthy'],
    ['min' => 425, 'max' => 604, 'quality' => 'Hazardous']
];

// Define a priority for the quality levels
$qualityPriority = [
    'Good' => 0,
    'Moderate' => 1,
    'Unhealthy for Sensitive Groups' => 2,
    'Unhealthy' => 3,
    'Very Unhealthy' => 4,
    'Hazardous' => 5,
    'Unknown' => 6
];

// Function to determine the overall quality
function determine_overall_quality($qualities, $qualityPriority) {
    $worstQuality = 'Good';
    $worstPriority = $qualityPriority['Good'];

    foreach ($qualities as $quality) {
        if ($qualityPriority[$quality] > $worstPriority) {
            $worstQuality = $quality;
            $worstPriority = $qualityPriority[$quality];
        }
    }

    return $worstQuality;
}

// Filter by district
$districtFilter = isset($_POST["district"]) ? $_POST["district"] : "All";
if ($districtFilter == "All") {
    $documents = $collection->find()->toArray();
} else {
    $filter['district_name'] = $districtFilter;
    $documents = $collection->find($filter)->toArray();
}

// Update documents with classified quality
foreach ($documents as $document) {
    $updateData = [];

    if (!empty($document['o3_value'])) {
        $o3Quality = classify_quality($document['o3_value'], $o3Ranges);
        $updateData['o3_quality'] = $o3Quality;
    } else {
        $o3Quality = 'Unknown';
    }

    if (!empty($document['no2_value'])) {
        $no2Quality = classify_quality($document['no2_value'], $no2Ranges);
        $updateData['no2_quality'] = $no2Quality;
    } else {
        $no2Quality = 'Unknown';
    }

    if (!empty($document['pm10_value'])) {
        $pm10Quality = classify_quality($document['pm10_value'], $pm10Ranges);
        $updateData['pm10_quality'] = $pm10Quality;
    } else {
        $pm10Quality = 'Unknown';
    }

    $overallQuality = determine_overall_quality([$o3Quality, $no2Quality, $pm10Quality], $qualityPriority);
    $updateData['quality'] = $overallQuality;

    if (!empty($updateData)) {
        $collection->updateOne(
            ['_id' => $document['_id']],
            ['$set' => $updateData]
        );
    }
}

echo "Air quality data has been updated with classifications.";

