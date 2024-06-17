<?php
include './includes/head.php';
require_once 'autoload.php';

$client = new MongoDB\Client();
$collection = $client->barcelona->air_qualities;
$districtNames = $collection->distinct('district_name');

$filter; $o3Counts; $no2Counts; $pm10Counts = [];

$districtFilter = isset($_POST["district"]) ? $_POST["district"] : "All";
if ($districtFilter == "All") {
  $documents = $collection->find()->toArray();
} else {
  $filter['district_name'] = $districtFilter;
  $documents = $collection->find($filter)->toArray();
}

// get values 
$o3Values = array_column($documents, 'o3_value');
$no2Values = array_column($documents, 'no2_value');
$pm10Values = array_column($documents, 'pm10_value');

$o3Hours = array_column($documents, 'o3_hour');
$no2Hours = array_column($documents, 'no2_hour');
$pm10Hours = array_column($documents, 'pm10_hour');

// convert hours to int (before: ..h)
$o3Hours = array_map(function($hour) {
  return intval(str_replace('h', '', $hour));
}, $o3Hours);
$no2Hours = array_map(function($hour) {
  return intval(str_replace('h', '', $hour));
}, $no2Hours);
$pm10Hours = array_map(function($hour) {
  return intval(str_replace('h', '', $hour));
}, $pm10Hours);

// print_r($o3Values);
// print_r($o3Hours);
?>

<body>
  <div class="grid-container">
    <!-- Sidebar -->
    <!-- <div class="sidebar-container">
      <div class="sidebar-title">
        <div class="text-end close-btn">
          <i class="bx bx-x"></i>
        </div>
        <h4 class="text-center">
          Barcelona Datasets
        </h4>
      </div>
      <hr>
      <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
          <a href="index.php" class="nav-link">
            Accident by Location
          </a>
        </li>
        <li class="nav-item">
          <a href="immigrant.php" class="nav-link">
            Immigrant Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a href="birth.php" class="nav-link">
            Birth Rate Pattern
          </a>
        </li>
        <li class="nav-item">
          <a href="air_quality.php" class="nav-link active">
            Air Quality by Neighborhood
          </a>
        </li>
      </ul>
      <hr>
    </div> -->
    <div class="menu-btn">
      <i class="bx bx-menu"></i>
    </div>

    <!-- Dashboard -->
    <div class="air-quality-dashboard">
      <h2 class="text-center">Air Quality Dashboard</h2><br>
      <form method="post" class="air-quality-filter-form" id="districtForm">
        <h5>Filter by district</h5>
        <select name="district" id="district" class="district-filter wider" onchange="document.getElementById('districtForm').submit()">
          <option value="All">All</option>
          <?php foreach ($districtNames as $district) { ?>
            <option value="<?php echo htmlspecialchars($district); ?>" <?php echo ($district == $districtFilter) ? 'selected' : ''; ?>><?php echo htmlspecialchars($district); ?></option>
          <?php } ?>
        </select>
      </form>

      <div class="chart-container-1">
        <canvas id="scatterPlotChart" width="1200" height="600"></canvas>
      </div>
    </div>
</body>

<script>
  // sidebar
  // const sidebarContainer = document.querySelector(".sidebar-container");
  // const gridContainer = document.querySelector(".grid-container");
  // const dashboard = document.querySelector(".immigrant-dashboard");
  // const closeButton = document.querySelector(".close-btn");
  // const menuButton = document.querySelector(".menu-btn");

  // menuButton.addEventListener("click", openSidebar);
  // closeButton.addEventListener("click", closeSidebar);
  // window.addEventListener("click", function(event) {
  //   if (!menuButton.contains(event.target) && !sidebarContainer.contains(event.target)) {
  //     closeSidebar();
  //   }
  // });

  // function closeSidebar() {
  //   sidebarContainer.style.display = "none";
  //   menuButton.style.display = "block";
  //   gridContainer.style.gridTemplateColumns = "auto";
  //   dashboard.style.marginTop = "0px";
  // }

  // function openSidebar() {
  //   sidebarContainer.style.display = "block";
  //   menuButton.style.display = "none";
  //   gridContainer.style.gridTemplateColumns = "1fr 3fr";
  //   dashboard.style.marginTop = "40px";
  // }

  // closeSidebar();

  const ctx = document.getElementById('scatterPlotChart').getContext('2d');
  const dataO3 = <?php echo json_encode($o3Values); ?>;
  const dataNO2 = <?php echo json_encode($no2Values); ?>;
  const dataPM10 = <?php echo json_encode($pm10Values); ?>;

  const dataO3Hours = <?php echo json_encode($o3Hours); ?>;
  const dataNO2Hours = <?php echo json_encode($no2Hours); ?>;
  const dataPM10Hours = <?php echo json_encode($pm10Hours); ?>;

  const data = {
    datasets: [{
        label: 'O3',
        data: dataO3.map((value, index) => ({x: dataO3Hours[index], y: value})),
        backgroundColor: 'rgba(255, 99, 132, 0.5)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1,
        pointRadius: 5
      },
      {
        label: 'NO2',
        data: dataNO2.map((value, index) => ({x: dataNO2Hours[index], y: value})),
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1,
        pointRadius: 5
      },
      {
        label: 'PM10',
        data: dataPM10.map((value, index) => ({x: dataPM10Hours[index], y: value})),
        backgroundColor: 'rgba(75, 192, 192, 0.5)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1,
        pointRadius: 5
      }
    ]
  };

  const config = {
    type: 'scatter',
    data: data,
    options: {
      responsive: true,
      plugins: {
        tooltip: {
          mode: 'index',
          intersect: false,
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Time (hours)'
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Concentration (µg/m³)'
          }
        }
      }
    }
  };

  const scatterPlotChart = new Chart(ctx, config);
</script>
