<?php
include './includes/head.php';
require_once 'autoload.php';

$client = new MongoDB\Client();
$collection = $client->barcelona->air_qualities;
$districtNames = $collection->distinct('district_name');

// filter by district
$districtFilter = isset($_POST["district"]) ? $_POST["district"] : "All";
if ($districtFilter == "All") {
  $documents = $collection->find()->toArray();
} else {
  $filter['district_name'] = $districtFilter;
  $documents = $collection->find($filter)->toArray();
}

// convert hours to int (before: ..h) & get the value
$o3Data = [];
$no2Data = [];
$pm10Data = [];

$o3QualityCounts = [];
$no2QualityCounts = [];
$pm10QualityCounts = [];

foreach ($documents as $document) {
  if (!empty($document['o3_hour'])) {
    $o3Data[] = [
      'hour' => (int) str_replace('h', '', $document['o3_hour']),
      'value' => $document['o3_value']
    ];
    if (!empty($document['o3_quality'])) {
      if (!isset($o3QualityCounts[$document['o3_quality']])) {
        $o3QualityCounts[$document['o3_quality']] = 0;
      }
      $o3QualityCounts[$document['o3_quality']]++;
    }
  }
  if (!empty($document['no2_hour'])) {
    $no2Data[] = [
      'hour' => (int) str_replace('h', '', $document['no2_hour']),
      'value' => $document['no2_value']
    ];
    if (!empty($document['no2_quality'])) {
      if (!isset($no2QualityCounts[$document['no2_quality']])) {
        $no2QualityCounts[$document['no2_quality']] = 0;
      }
      $no2QualityCounts[$document['no2_quality']]++;
    }
  }
  if (!empty($document['pm10_hour'])) {
    $pm10Data[] = [
      'hour' => (int) str_replace('h', '', $document['pm10_hour']),
      'value' => $document['pm10_value']
    ];
    if (!empty($document['pm10_quality'])) {
      if (!isset($pm10QualityCounts[$document['pm10_quality']])) {
        $pm10QualityCounts[$document['pm10_quality']] = 0;
      }
      $pm10QualityCounts[$document['pm10_quality']]++;
    }
  }
}
?>

<style>
  body {
    background-color: rgb(255, 252, 246);
  }

  .sidebar-container {
    height: auto !important;
  }

  .air-quality-container {
    margin: 20px;
    display: flex;
    flex-direction: column;
    align-items: center !important;
    justify-content: center !important;
    gap: 30px;
  }

  .district-filter {
    border-radius: 6px;
    border: 1px solid #a9a9a9;
    width: 120px;
    height: 28px;
    margin-bottom: 10px;
  }

  .wider {
    width: 200px;
  }

  .air-quality-filter-form {
    display: flex;
    gap: 8px;
  }

  .chart-container-1 {
    width: 1200px !important;
    height: 600px !important;
  }

  .chart-container-2 {
    width: 900px !important;
    height: 400px !important;
    margin-top: 30px;
  }

  .line {
    border: 1px solid #dedede;
    width: 90%;
  }

  .chart-2 {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 20px;
  }
</style>

<body>
  <div class="grid-container">
    <!-- Sidebar -->
    <div class="sidebar-container">
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
            Air Quality Dashboard
          </a>
        </li>
      </ul>
      <hr>
    </div>
    <div class="menu-btn">
      <i class="bx bx-menu"></i>
    </div>

    <!-- Dashboard -->
    <div class="air-quality-dashboard">
      <h2 class="text-center">Air Quality Dashboard</h2><br>
      <div class="air-quality-container">
        <form method="post" class="air-quality-filter-form" id="districtForm">
          <h5>Filter by district</h5>
          <select name="district" id="district" class="district-filter wider" onchange="document.getElementById('districtForm').submit()">
            <option value="All">All</option>
            <?php foreach ($districtNames as $district) { ?>
              <option value="<?php echo htmlspecialchars($district); ?>" <?php echo ($district == $districtFilter) ? 'selected' : ''; ?>><?php echo htmlspecialchars($district); ?></option>
            <?php } ?>
          </select>
        </form>
        <div class="line"></div>
        <div class="chart">
          <h4 class="text-center">Average Value of Indicators (O3, NO2, PM10)</h4>
          <div class="chart-container-1">
            <canvas id="bubbleChart"></canvas>
          </div>
        </div>
        <div class="line"></div>
        <h4 class="text-center">Quality of Indicators (O3, NO2, PM10)</h4>
        <div class="chart-2">
            <div class="chart-container-2">
              <canvas id="barChart"></canvas>
            </div>
        </div>
      </div>
    </div>
</body>

<script>
  // sidebar
  const sidebarContainer = document.querySelector(".sidebar-container");
  const gridContainer = document.querySelector(".grid-container");
  const dashboard = document.querySelector(".air-quality-dashboard");
  const closeButton = document.querySelector(".close-btn");
  const menuButton = document.querySelector(".menu-btn");

  menuButton.addEventListener("click", openSidebar);
  closeButton.addEventListener("click", closeSidebar);
  window.addEventListener("click", function(event) {
    if (!menuButton.contains(event.target) && !sidebarContainer.contains(event.target)) {
      closeSidebar();
    }
  });

  function closeSidebar() {
    sidebarContainer.style.display = "none";
    menuButton.style.display = "block";
    gridContainer.style.gridTemplateColumns = "auto";
    dashboard.style.marginTop = "0px";
  }

  function openSidebar() {
    sidebarContainer.style.display = "block";
    menuButton.style.display = "none";
    gridContainer.style.gridTemplateColumns = "1fr 3fr";
    dashboard.style.marginTop = "40px";
  }


  // bubble chart
  const ctx = document.getElementById('bubbleChart').getContext('2d');

  const o3Data = <?php echo json_encode($o3Data); ?>;
  const no2Data = <?php echo json_encode($no2Data); ?>;
  const pm10Data = <?php echo json_encode($pm10Data); ?>;

  // aggregate data by hour & count of hour
  function aggregateData(data) {
    const aggregatedData = [];
    const counts = {};

    data.forEach((element) => {
      const hour = element.hour;
      if (!counts[hour]) {
        counts[hour] = {
          count: 0,
          sum: 0
        };
      }
      counts[hour].count++;
      counts[hour].sum += element.value;
    });

    for (const hour in counts) {
      if (counts.hasOwnProperty(hour)) {
        const averageValue = counts[hour].sum / counts[hour].count;
        aggregatedData.push({
          x: parseInt(hour),
          y: averageValue,
          r: counts[hour].count * 0.1 // to make the radius smaller
        });
      }
    }

    return aggregatedData;
  }

  // aggregate data for each indicators
  const aggregatedO3Data = aggregateData(o3Data);
  const aggregatedNO2Data = aggregateData(no2Data);
  const aggregatedPM10Data = aggregateData(pm10Data);

  const scatterPlotChart = new Chart(ctx, {
    type: 'bubble',
    data: {
      datasets: [{
          label: 'O3',
          data: aggregatedO3Data,
          backgroundColor: 'rgba(255, 99, 132, 0.5)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1
        },
        {
          label: 'NO2',
          data: aggregatedNO2Data,
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        },
        {
          label: 'PM10',
          data: aggregatedPM10Data,
          backgroundColor: 'rgba(75, 192, 192, 0.5)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        tooltip: {
          mode: 'index',
          intersect: false
        }
      },
      scales: {
        x: {
          type: 'linear',
          position: 'bottom',
          title: {
            display: true,
            text: 'Time (hour)'
          }
        },
        y: {
          title: {
            display: true,
            text: 'Average Value (µg/m³)'
          },
          beginAtZero: true
        }
      }
    }
  });

  // bar chart
  const barCtx = document.getElementById('barChart').getContext('2d');

  const o3QualityCounts = <?php echo json_encode($o3QualityCounts); ?>;
  const no2QualityCounts = <?php echo json_encode($no2QualityCounts); ?>;
  const pm10QualityCounts = <?php echo json_encode($pm10QualityCounts); ?>;

  const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: ['Good', 'Moderate', 'Unhealthy'],
      datasets: [{
          label: 'O3',
          data: [o3QualityCounts['Good'] || 0, o3QualityCounts['Moderate'] || 0, o3QualityCounts['Unhealthy'] || 0],
          backgroundColor: 'rgba(255, 99, 132, 0.5)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1
        },
        {
          label: 'NO2',
          data: [no2QualityCounts['Good'] || 0, no2QualityCounts['Moderate'] || 0, no2QualityCounts['Unhealthy'] || 0],
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        },
        {
          label: 'PM10',
          data: [pm10QualityCounts['Good'] || 0, pm10QualityCounts['Moderate'] || 0, pm10QualityCounts['Unhealthy'] || 0],
          backgroundColor: 'rgba(75, 192, 192, 0.5)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        tooltip: {
          mode: 'index',
          intersect: false
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Air Quality Category'
          }
        },
        y: {
          title: {
            display: true,
            text: 'Count'
          },
          beginAtZero: true
        }
      }
    }
  });

  closeSidebar();
</script>