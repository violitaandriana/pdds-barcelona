<?php
// koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "pdds_barcelona");

if (!$conn) {
    die("Connection Failed : " . mysqli_connect_error());
}

function query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
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