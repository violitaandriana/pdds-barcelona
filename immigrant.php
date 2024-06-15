<?php
include "./includes/head.php";
require "functions.php";

$year = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["year"])) {
  $year = $_POST["year"];
}

$topTenCountries = findTopCountries($year);

$countries = [];
foreach ($topTenCountries as $country) {
  array_push($countries, $country["nationality"]);
}

$totalImmigrants = [];
foreach ($topTenCountries as $country) {
  array_push($totalImmigrants, $country["total_immigrants"]);
}

?>

<style>
body {
  overflow: hidden;
}

.immigrant-container {
  margin: 20px 100px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.immigrant-filter {
  border-radius: 6px;
  border: 1px solid #a9a9a9;
  width: 120px;
  margin-bottom: 10px;
}

.immigrant-filter-form {
  display: flex;
  gap: 8px;
}

.chart-container {
  width: 1000px !important;
  height: 500px !important;
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
            Immigrant by Nationality
          </a>
        </li>
        <li class="nav-item">
          <a href="birth.php" class="nav-link">
            Birth Rate Pattern
          </a>
        </li>
        <li class="nav-item">
          <a href="air_quality.php" class="nav-link">
            Air Quality by Neighborhood
          </a>
        </li>
      </ul>
      <hr>
    </div>
    <div class="menu-btn">
      <i class="bx bx-menu"></i>
    </div>
    <!-- Dashboard -->
    <div class="immigrant-container">
      <h3 class="text-center">Top 10 Immigrant by Nationality</h3><br>
      <form method="post" class="immigrant-filter-form">
        <h5>Filter by year</h5>
        <select name="year" id="year" class="immigrant-filter" onchange="submitForm()">
          <option value="All">All</option>
          <option value="2015">2015</option>
          <option value="2016">2016</option>
          <option value="2017">2017</option>
        </select>
      </form>
      <div class="chart-container">
        <canvas id="bar-chart" width="260" height="130"></canvas>
      </div>
    </div>
  </div>

  
  <script>
  // sidebar
  const sidebarContainer = document.querySelector(".sidebar-container");
  const gridContainer = document.querySelector(".grid-container");
  const closeButton = document.querySelector(".close-btn");
  const menuButton = document.querySelector(".menu-btn");
  const option = document.getElementById("year");

  option.value = "<?php if(isset($_POST["year"])) { echo $_POST["year"]; } else { echo "All"; } ?>";

  menuButton.addEventListener("click", openSidebar);
  closeButton.addEventListener("click", closeSidebar);
  window.addEventListener("click", function(event) {
    if (!menuButton.contains(event.target) && !sidebarContainer.contains(event.target)) {
      closeSidebar();
    }
  });

  function closeSidebar() {
    console.log("close");
    sidebarContainer.style.display = "none";
    menuButton.style.display = "block";
    gridContainer.style.gridTemplateColumns = "auto";
  }

  function openSidebar() {
    sidebarContainer.style.display = "block";
    menuButton.style.display = "none";
    gridContainer.style.gridTemplateColumns = "1fr 3fr";
  }

  // submit form
  function submitForm() {
    closeSidebar();
    document.querySelector(".immigrant-filter-form").submit();
  }

  // bar chart
  const ctx = document.getElementById("bar-chart").getContext("2d");
  const data = <?php echo json_encode($totalImmigrants) ?>;
  const labels = <?php echo json_encode($countries) ?>;
  console.log(data, labels)
  const myChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [{
        label: "Total Immigrant by Nationality",
        data: data,
        backgroundColor: [
          "rgba(255, 99, 132, 0.2)",
          "rgba(255, 159, 64, 0.2)",
          "rgba(255, 205, 86, 0.2)",
          "rgba(75, 192, 192, 0.2)",
          "rgba(54, 162, 235, 0.2)",
          "rgba(153, 102, 255, 0.2)",
          "rgba(185, 198, 232, 0.2)",
          "rgba(75, 192, 192, 0.2)",
          "rgba(255, 205, 86, 0.2)",
          "rgba(255, 159, 64, 0.2)",
        ],
        borderColor: [
          "rgb(255, 99, 132)",
          "rgb(255, 159, 64)",
          "rgb(255, 205, 86)",
          "rgb(75, 192, 192)",
          "rgb(54, 162, 235)",
          "rgb(153, 102, 255)",
          "rgb(54, 162, 235)",
          "rgb(75, 192, 192)",
          "rgb(255, 205, 86)",
          "rgb(255, 159, 64)",
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

  closeSidebar();
  </script>
</body>