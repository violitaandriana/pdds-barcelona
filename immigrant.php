<?php
include "./includes/head.php";
require "functions.php";

// nationality
$yearInput = isset($_POST["year"]) ? $_POST["year"] : "All";

$topTenCountries = getTopCountries($yearInput);
$countries = array_column($topTenCountries, 'nationality');
$totalImmigrant = array_column($topTenCountries, 'total_immigrant');

// district & nationality
$nationalityInput = isset($_POST["nationality"]) ? $_POST["nationality"] : "All";

$nationalitiesArr = getNationality();
$nationalities = array_column($nationalitiesArr, 'nationality');

$districtTotalArr = getDistrictTotalByNationality($nationalityInput);
$districtNames = array_column($districtTotalArr, 'district_name');
$totalImmigrant2 = array_column($districtTotalArr, 'total_immigrant');

// card
$totalImmigrantByYear = getTotalImmigrant($yearInput);
$totalDistrict = getTotalDistrict();
$totalNationality = count($nationalities);
?>

<style>
  body {
    background-color: rgb(255, 252, 246);
    margin-bottom: 40px;
  }

  .sidebar-container {
    height: auto !important;
  }

  .immigrant-container {
    margin: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 30px;
  }

  .immigrant-cards {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
  }

  .card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
    width: 260px;
    background-color: rgba(255, 205, 86, 0.2);
  }

  .immigrant-filter {
    border-radius: 6px;
    border: 1px solid #a9a9a9;
    width: 120px;
    height: 28px;
    margin-bottom: 10px;
  }

  .wider {
    width: 200px;
  }

  .immigrant-filter-form {
    display: flex;
    gap: 8px;
  }

  .chart-container-1,
  .chart-container-2 {
    width: 550px !important;
    height: 400px !important;
  }

  .immigrant-left,
  .immigrant-right {
    height: 78vh;
    border: 1px solid black;
    border-radius: 10px;
    padding: 30px;
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
          <a href="immigrant.php" class="nav-link active">
            Immigrant Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a href="birth.php" class="nav-link">
            Birth Rate Pattern
          </a>
        </li>
        <li class="nav-item">
          <a href="air_quality.php" class="nav-link">
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
    <div class="immigrant-dashboard">
      <h2 class="text-center">Immigrant Dashboard</h2><br>
      <div class="immigrant-cards">
        <div class="card">
          <h5>Total Immigrant</h5>
          <?php echo htmlspecialchars(number_format($totalImmigrantByYear)); ?>
        </div>
        <div class="card">
          <h5>Total Nationality</h5>
          <?php echo htmlspecialchars(number_format($totalNationality)); ?>
        </div>
        <div class="card">
          <h5>Total District</h5>
          <?php echo htmlspecialchars($totalDistrict); ?>
        </div>
      </div>
      <div class="immigrant-container">
        <!-- Nationality -->
        <div class="immigrant-left">
          <h4 class="text-center">Top 10 Immigrant by Nationality</h4><br>
          <form method="post" class="immigrant-filter-form" id="yearForm">
            <h5>Filter by year</h5>
            <select name="year" id="year" class="immigrant-filter" onchange="document.getElementById('yearForm').submit()">
              <option value="All">All</option>
              <option value="2015">2015</option>
              <option value="2016">2016</option>
              <option value="2017">2017</option>
            </select>
          </form>
          <br>
          <div class="chart-container-1">
            <canvas id="bar-chart" width="260" height="130"></canvas>
          </div>
        </div>
        <!-- District -->
        <div class="immigrant-right">
          <h4 class="text-center">District Name by Nationality</h4><br>
          <form method="post" class="immigrant-filter-form" id="nationalityForm">
            <h5>Filter by nationality</h5>
            <select name="nationality" id="nationality" class="immigrant-filter wider" onchange="document.getElementById('nationalityForm').submit()">
              <option value="All">All</option>
              <?php foreach ($nationalities as $nationality) { ?>
                <option value="<?php echo htmlspecialchars($nationality); ?>"><?php echo htmlspecialchars($nationality); ?></option>
              <?php } ?>
            </select>
          </form>
          <br>
          <div class="chart-container-2">
            <canvas id="bar-chart-2" width="260" height="130"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>


  <script>
    // sidebar
    const sidebarContainer = document.querySelector(".sidebar-container");
    const gridContainer = document.querySelector(".grid-container");
    const dashboard = document.querySelector(".immigrant-dashboard");
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


    const setOptionValue = (elementId, value) => {
      document.getElementById(elementId).value = value;
    };

    setOptionValue("year", "<?php echo htmlspecialchars($yearInput); ?>");
    setOptionValue("nationality", "<?php echo htmlspecialchars($nationalityInput); ?>");

    // bar chart left
    const ctx = document.getElementById("bar-chart").getContext("2d");
    const data = <?php echo json_encode($totalImmigrant) ?>;
    const labels = <?php echo json_encode($countries) ?>;
    const myChart = new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [{
          label: "Total Immigrant by Nationality",
          data: data,
          backgroundColor: [
            "rgba(255, 159, 64, 0.2)",
            "rgba(255, 205, 86, 0.2)",
          ],
          borderColor: [
            "rgb(255, 159, 64)",
            "rgb(255, 205, 86)",
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    })

    // radar chart right
    const ctx2 = document.getElementById("bar-chart-2").getContext("2d");
    const data2 = <?php echo json_encode($totalImmigrant2) ?>;
    const labels2 = <?php echo json_encode($districtNames) ?>;
    const myChart2 = new Chart(ctx2, {
      type: "radar",
      data: {
        labels: labels2,
        datasets: [{
          label: "Total Immigrant in District",
          data: data2,
          backgroundColor: [
            "rgba(255, 205, 86, 0.2)",
          ],
          borderColor: [
            "rgb(255, 159, 64)",
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      }
    })

    closeSidebar();
  </script>
</body>