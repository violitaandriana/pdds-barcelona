<?php
// koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "pdds_barcelona");

if(!$conn) {
    die("Connection Failed : " .mysqli_connect_error());
}

function query($query) {
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

function findTopCountries($selectedYear) {
    if ($selectedYear == "default" || $selectedYear == "") {
        $query = "SELECT nationality, SUM(total) AS total_immigrants FROM immigrant GROUP BY nationality ORDER BY total_immigrants DESC LIMIT 10";
    }
    else {
        $selectedYear = intval($selectedYear);
        $query = "SELECT nationality, SUM(total) AS total_immigrants FROM immigrant WHERE year = $selectedYear GROUP BY nationality ORDER BY total_immigrants DESC LIMIT 10";
    }
    $data = query($query);

    return $data;
}