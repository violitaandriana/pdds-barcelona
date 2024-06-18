<?php
// koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "pdds_barcelona");

if (!$conn) {
    die("Connection Failed : " . mysqli_connect_error());
}

function query($query)
{
    // biar bisa ambil $conn di luar function
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        // append row ke dalam array rows
        $rows[] = $row;
    }
    return $rows;
}

function getTopCountries($selectedYear)
{
    if ($selectedYear == "All" || $selectedYear == "") {
        $query = "SELECT nationality, SUM(total) AS total_immigrant FROM immigrant GROUP BY nationality ORDER BY total_immigrant DESC LIMIT 10";
    } else {
        $selectedYear = intval($selectedYear);
        $query = "SELECT nationality, SUM(total) AS total_immigrant FROM immigrant WHERE year = $selectedYear GROUP BY nationality ORDER BY total_immigrant DESC LIMIT 10";
    }

    $data = query($query);

    return $data;
}

function getNationality()
{
    $query = "SELECT DISTINCT(nationality) FROM immigrant ORDER BY nationality";
    $data = query($query);

    return $data;
}
function getTotalDistrict()
{
    $query = "SELECT COUNT(DISTINCT(district_name)) AS total_district FROM immigrant";
    $data = query($query);

    return $data[0]['total_district'];
}

function getDistrictTotalByNationality($nationality)
{
    if ($nationality == "All" || $nationality == "") {
        $query = "SELECT district_name, SUM(total) AS total_immigrant FROM immigrant GROUP BY district_name";
    } else {
        $nationality = strval($nationality);
        $query = "SELECT district_name, SUM(total) AS total_immigrant FROM immigrant WHERE nationality = '$nationality' GROUP BY district_name";
    }

    $data = query($query);

    return $data;
}

function getTotalImmigrant($year)
{
    if ($year == "All" || $year == "") {
        $query = "SELECT SUM(total) as total_immigrant FROM immigrant";
    } else {
        $query = "SELECT SUM(total) as total_immigrant FROM immigrant WHERE year = $year";
    }

    $data = query($query);

    return $data[0]['total_immigrant'];
}

function determineQuality($value, $indicator)
{
    if ($indicator == 'O3') {
        if ($value >= 0 && $value <= 54) return 'Good';
        if ($value >= 55 && $value <= 70) return 'Moderate';
        if ($value >= 71 && $value <= 85) return 'Unhealthy for Sensitive Groups';
        if ($value >= 86 && $value <= 105) return 'Unhealthy';
        if ($value >= 106 && $value <= 200) return 'Very Unhealthy';
        return 'Hazardous';
    } elseif ($indicator == 'NO2') {
        if ($value >= 0 && $value <= 53) return 'Good';
        if ($value >= 54 && $value <= 100) return 'Moderate';
        if ($value >= 101 && $value <= 360) return 'Unhealthy for Sensitive Groups';
        if ($value >= 361 && $value <= 649) return 'Unhealthy';
        if ($value >= 650 && $value <= 1249) return 'Very Unhealthy';
        return 'Hazardous';
    } elseif ($indicator == 'PM10') {
        if ($value >= 0 && $value <= 54) return 'Good';
        if ($value >= 55 && $value <= 154) return 'Moderate';
        if ($value >= 155 && $value <= 254) return 'Unhealthy for Sensitive Groups';
        if ($value >= 255 && $value <= 354) return 'Unhealthy';
        if ($value >= 355 && $value <= 424) return 'Very Unhealthy';
        return 'Hazardous';
    }
}
