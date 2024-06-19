<?php
include './includes/head.php';

$conn = mysqli_connect("localhost", "root", "", "pdds_barcelona");

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// Fetch data from MySQL and prepare it for Chart.js
$dataPoints = [];
$tableData = []; // Data for the table

$startYear = isset($_GET['start_year']) ? (int)$_GET['start_year'] : null;
$endYear = isset($_GET['end_year']) ? (int)$_GET['end_year'] : null;
$genderFilter = isset($_GET['gender']) ? $_GET['gender'] : null;

$query = "SELECT * FROM birth";
$conditions = [];

if ($startYear && $endYear) {
    $conditions[] = "Year BETWEEN $startYear AND $endYear";
}
if ($genderFilter) {
    $conditions[] = "Gender = '$genderFilter'";
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $year = $row['Year'];
        $districtName = $row['District Name'];
        $neighborhoodCode = $row['Neighborhood Code'];
        $neighborhoodName = $row['Neighborhood Name'];
        $gender = $row['Gender'];
        $number = $row['Number'];

        // Add data to dataPoints for each district
        if (!isset($dataPoints[$districtName])) {
            $dataPoints[$districtName] = 0;
        }
        $dataPoints[$districtName] += $number;

        // Add data to tableData for the table
        $tableData[] = [
            'Year' => $year,
            'District Code' => $row['District Code'],
            'District Name' => $districtName,
            'Neighborhood Code' => $neighborhoodCode,
            'Neighborhood Name' => $neighborhoodName,
            'Gender' => $gender,
            'Number' => $number
        ];
    }
} else {
    echo "<script>alert('Error retrieving data: " . mysqli_error($conn) . "');</script>";
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Birth Rate Pattern</title>
  <!-- Include DataTables CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
  <style>
    body {
      background-color: #edf2fa;
    }

    .birth-content h1 {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .birth-filter {
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .chart {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 80%; /* Adjust width as needed */
      margin: 0 auto;
    }

    .table-responsive {
      display: flex;
      justify-content: center;
      width: 80%; /* Adjust width as needed to match chart width */
      margin: 0 auto;
    }

    #birthTable {
      width: 100%; /* Make the table take the full available width */
      background-color: #edf2fa; /* Match the background color of the body */
      border-radius: 10px; /* Optional: to round the corners */
    }

    #birthTable thead th {
      background-color: #d1e3f9; /* A slightly darker color for the header */
      color: #000; /* Text color for the header */
      border: 1px solid #ddd; /* Border for header cells */
    }

    #birthTable tbody td {
      background-color: #edf2fa; /* Match the background color of the body */
      border: 1px solid #ddd; /* Border for table cells */
    }

    .dataTables_wrapper .dataTables_filter input {
      background-color: #edf2fa; /* Match the background color of the body */
      border: 1px solid #ddd; /* Border for the search input */
      border-radius: 5px; /* Optional: to round the corners */
      padding: 5px; /* Padding for the search input */
    }

    .sidebar-container {
      height: auto !important;
    }

    .filter-box {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 20px;
      background: #edf2fa;
      padding: 15px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 80%; /* Adjust width as needed */
      margin: 0 auto;
    }

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
</head>
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
          <a href="immigrant.php" class="nav-link">Immigrant Dashboard</a>
        </li>
        <li class="nav-item">
          <a href="birth.php" class="nav-link active">Birth Rate Pattern</a>
        </li>
        <li class="nav-item">
          <a href="air_quality.php" class="nav-link">Air Quality Dashboard</a>
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
      <!-- Add canvas for chart -->
      <div class="chart">
        <canvas id="myChart"></canvas>
      </div>

      <!-- Data table -->
      <div class="table-responsive">
        <table class="table table-striped table-bordered" id="birthTable">
          <thead>
            <tr>
              <th>Year</th>
              <th>District Code</th>
              <th>District Name</th>
              <th>Neighborhood Code</th>
              <th>Neighborhood Name</th>
              <th>Gender</th>
              <th>Number</th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($tableData as $row) {
              echo '<tr>';
              echo '<td>' . htmlspecialchars($row['Year']) . '</td>';
              echo '<td>' . htmlspecialchars($row['District Code']) . '</td>';
              echo '<td>' . htmlspecialchars($row['District Name']) . '</td>';
              echo '<td>' . htmlspecialchars($row['Neighborhood Code']) . '</td>';
              echo '<td>' . htmlspecialchars($row['Neighborhood Name']) . '</td>';
              echo '<td>' . htmlspecialchars($row['Gender']) . '</td>';
              echo '<td>' . htmlspecialchars($row['Number']) . '</td>';
              echo '</tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- Include jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Include DataTables JS -->
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
  <!-- Include Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    $(document).ready( function () {
        $('#birthTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true
        });
    });

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

    // Prepare data for use in Chart.js
    const dataPoints = <?php echo json_encode($dataPoints); ?>;

    // Get district names and total births
    const labels = Object.keys(dataPoints);
    const data = Object.values(dataPoints);

    const config = {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Total Births',
          data: data,
          fill: false,
          borderColor: 'rgba(75, 192, 192, 1)',
          tension: 0.1,
        }]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false
        },
        scales: {
          x: {
            title: {
              display: true,
              text: 'District Name'
            }
          },
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Number of Births'
            }
          }
        }
      }
    };

    const myChart = new Chart(
      document.getElementById('myChart'),
      config
    );

    closeSidebar();
  </script>
</body>
</html>
