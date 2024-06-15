<?php
// MongoDB Connection
require 'autoload.php'; // Memuat MongoDB PHP Library
use MongoDB\Client;
$client = new Client("mongodb://localhost:27017");
$collection = $client->pdds_barcelona->Birth_Rate;

// Ambil data dari MongoDB dan persiapkan untuk Chart.js
$dataPoints = [];

$startYear = isset($_GET['start_year']) ? (int)$_GET['start_year'] : null;
$endYear = isset($_GET['end_year']) ? (int)$_GET['end_year'] : null;
$genderFilter = isset($_GET['gender']) ? $_GET['gender'] : null;

$filter = [];
if ($startYear && $endYear) {
  $filter['Year'] = ['$gte' => $startYear, '$lte' => $endYear];
}
if ($genderFilter) {
  $filter['Gender'] = $genderFilter;
}

try {
  $cursor = $collection->find($filter);
  foreach ($cursor as $document) {
    $year = $document['Year'];
    $districtName = $document['District Name'];
    $gender = $document['Gender'];
    $number = $document['Number'];

    // Masukkan data ke dalam dataPoints untuk setiap distrik
    if (!isset($dataPoints[$districtName][$year])) {
      $dataPoints[$districtName][$year] = 0;
    }
    $dataPoints[$districtName][$year] += $number;
  }
} catch (Exception $e) {
  echo "<script>alert('Error retrieving data: " . $e->getMessage() . "');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDDS | Barcelona Datasets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="styles.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<style>
  .filter-box {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      background: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Filter item styles */
    .filter-item {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
    }

    .filter-item label {
      margin-right: 10px;
      font-weight: bold;
    }

    .filter-item select {
      padding: 5px;
      border-radius: 5px;
      border: 1px solid #ddd;
      min-width: 150px;
      margin-right: 20px;
    }

    .filter-item button {
      padding: 5px 10px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .filter-item button:hover {
      background-color: #0056b3;
    }
</style>
<body>
  <div class="grid-container">
    <!-- Sidebar -->
    <div class="sidebar-container">
      <div class="sidebar-title">
        <div class="text-end close-btn">
          <i class='bx bx-x'></i>
        </div>
        <h4 class="text-center">Barcelona Datasets</h4>
      </div>
      <hr>
      <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
          <a href="index.php" class="nav-link">Accident by Location</a>
        </li>
        <li class="nav-item">
          <a href="immigrant.php" class="nav-link">Immigrant by Nationality</a>
        </li>
        <li class="nav-item">
          <a href="birth.php" class="nav-link active">Birth Rate Pattern</a>
        </li>
        <li class="nav-item">
          <a href="air_quality.php" class="nav-link">Air Quality by Neighborhood</a>
        </li>
      </ul>
      <hr>
    </div>
    <div class="menu-btn">
      <i class='bx bx-menu'></i>
    </div>
    <div class="birth-content">
      <h1>Birth Rate Pattern</h1>
      <div class="birth-filter">
        <div class="filter-box">
          <form method="GET" action="birth.php" class="filter-item">
            <label for="start_year">Start Year:</label>
            <select name="start_year" id="start_year">
              <option value="">Select Start Year</option>
              <?php
              for ($i = 2013; $i <= 2017; $i++) {
                $selected = (isset($_GET['start_year']) && $_GET['start_year'] == $i) ? 'selected' : '';
                echo "<option value=\"$i\" $selected>$i</option>";
              }
              ?>
            </select>
            <label for="end_year" style="margin-left: 20px;">End Year:</label>
            <select name="end_year" id="end_year">
              <option value="">Select End Year</option>
              <?php
              for ($i = 2013; $i <= 2017; $i++) {
                $selected = (isset($_GET['end_year']) && $_GET['end_year'] == $i) ? 'selected' : '';
                echo "<option value=\"$i\" $selected>$i</option>";
              }
              ?>
            </select>
            <label for="gender" style="margin-left: 20px;">Gender:</label>
            <select name="gender" id="gender">
              <option value="">All Genders</option>
              <option value="Boys" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Boys') ? 'selected' : '' ?>>Boys</option>
              <option value="Girls" <?= (isset($_GET['gender']) && $_GET['gender'] == 'Girls') ? 'selected' : '' ?>>Girls</option>
            </select>
            <button type="submit">Filter</button>
          </form>
        </div>
      </div>
      <!-- Tambahkan canvas untuk chart -->
      <canvas id="myChart"></canvas>
    </div>
  </div>

  <script>
    const sidebarContainer = document.querySelector(".sidebar-container");
    const gridContainer = document.querySelector(".grid-container");
    const closeButton = document.querySelector(".close-btn");
    const menuButton = document.querySelector(".menu-btn");

    closeButton.addEventListener('click', closeSidebar);
    menuButton.addEventListener('click', openSidebar);

    function closeSidebar() {
      sidebarContainer.style.display = 'none';
      menuButton.style.display = 'block';
      gridContainer.style.gridTemplateColumns = 'auto';
    }

    function openSidebar() {
      sidebarContainer.style.display = 'block';
      menuButton.style.display = 'none';
      gridContainer.style.gridTemplateColumns = '1fr 3fr';
    }

    // Prepare data untuk digunakan dalam Chart.js
    const dataPoints = <?php echo json_encode($dataPoints); ?>;

    // Dapatkan semua tahun dari dataPoints
    const allYears = [];
    for (const district in dataPoints) {
      for (const year in dataPoints[district]) {
        if (!allYears.includes(year)) {
          allYears.push(year);
        }
      }
    }

    // Sort the years numerically
    allYears.sort((a, b) => a - b);

    const labels = allYears;

    const datasets = [];
    Object.keys(dataPoints).forEach((district) => {
      const data = allYears.map(year => dataPoints[district][year] || 0);
      const dataset = {
        label: district,
        data: data,
        fill: false,
        borderColor: '#' + (Math.random() * 0xFFFFFF << 0).toString(16), // Random color
        tension: 0.1
      };
      datasets.push(dataset);
    });

    const config = {
      type: 'line',
      data: {
        labels: labels,
        datasets: datasets
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    };

    const myChart = new Chart(
      document.getElementById('myChart'),
      config
    );
  </script>
</body>
</html>
