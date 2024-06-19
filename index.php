<?php
include "./includes/head.php";

// KONEKSI KE DB
$conn = mysqli_connect("localhost", "root", "", "pdds_barcelona");

// CEK KONEKSI
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selectedDistrict = isset($_POST['district']) ? $_POST['district'] : '';
$selectedNeighborhood = isset($_POST['neighborhood']) ? $_POST['neighborhood'] : '';
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

// AMBIL DATA DISTRICT NAME
$districts = array();
$districtResult = $conn->query("SELECT DISTINCT district_name FROM accident");
while ($row = $districtResult->fetch_assoc()) {
    if (strtolower(trim($row['district_name'])) !== 'district name') {
        $districts[] = $row['district_name'];
    }
}

// AMBIL DATA NEIGHBORHOOD NAME BERDASAR DISTRICT NAME
$neighborhood = array(); 
if ($selectedDistrict) {
    $neighborhoodResult = $conn->query("SELECT DISTINCT neighborhood_name FROM accident WHERE district_name = '" . $conn->real_escape_string($selectedDistrict) . "'");
    while ($row = $neighborhoodResult->fetch_assoc()) {
        $neighborhood[] = $row['neighborhood_name'];
    }
}

// FILTER
$filteredResults = array();
$whereClauses = array();

if ($selectedDistrict) {
    $whereClauses[] = "district_name = '" . $conn->real_escape_string($selectedDistrict) . "'";
}
if ($selectedNeighborhood) {
    $whereClauses[] = "neighborhood_name = '" . $conn->real_escape_string($selectedNeighborhood) . "'";
}
if ($searchTerm) {
    $whereClauses[] = "(street LIKE '%" . $conn->real_escape_string($searchTerm) . "%' OR part_of_the_day LIKE '%" . $conn->real_escape_string($searchTerm) . "%')";
}

// DATA
$query = "SELECT * FROM accident";
if (count($whereClauses) > 0) {
    $query .= " WHERE " . implode(' AND ', $whereClauses);
}

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $filteredResults[] = $row;
}

// PERHITUNGAN TOTAL KECELAKAAN
$totalAccidentsQuery = "SELECT SUM(victims) as total FROM accident";
if (count($whereClauses) > 0) {
    $totalAccidentsQuery .= " WHERE " . implode(' AND ', $whereClauses);
}
$totalAccidentsResult = $conn->query($totalAccidentsQuery);
$totalAccidents = $totalAccidentsResult->fetch_assoc()['total'];

// PERHITUNGAN JUMLAH CEDERA SERIUS DAN RINGAN
$injuryQuery = "SELECT 
    SUM(serious_injuries) as seriousInjuries,
    SUM(mild_injuries) as mildInjuries
    FROM accident";

if (count($whereClauses) > 0) {
    $injuryQuery .= " WHERE " . implode(' AND ', $whereClauses);
}
$injuryResult = $conn->query($injuryQuery);
$injuryData = $injuryResult->fetch_assoc();
$seriousInjuries = $injuryData['seriousInjuries'];
$mildInjuries = $injuryData['mildInjuries'];

// BAR CHART
$chartData = array(
    'Dawn' => 0,
    'Morning' => 0,
    'Afternoon' => 0,
    'Evening' => 0,
    'Night' => 0
);

// PERHITUNGAN KORBAN BERDASAR PART OF DAY
$chartQuery = "SELECT part_of_the_day, sum(victims) as count FROM accident";
if (count($whereClauses) > 0) {
    $chartQuery .= " WHERE " . implode(' AND ', $whereClauses);
}
$chartQuery .= " GROUP BY part_of_the_day";

$chartResult = $conn->query($chartQuery);
while ($row = $chartResult->fetch_assoc()) {
    $partOfTheDay = $row['part_of_the_day'];
    $count = $row['count'];
    if (isset($chartData[$partOfTheDay])) {
        $chartData[$partOfTheDay] = $count;
    }
}

// TOP 5 KECELAKAAN
$topDistrictQuery = "SELECT district_name, SUM(victims) AS totalAccidents 
                     FROM accident 
                     GROUP BY district_name 
                     ORDER BY totalAccidents DESC 
                     LIMIT 5";
$topDistrictResult = $conn->query($topDistrictQuery);
$topDistricts = array();

while ($row = $topDistrictResult->fetch_assoc()) {
    $topDistricts[$row['district_name']] = $row['totalAccidents'];
}

$conn->close();
?>

<style>
    .sidebar-container {
        height: 100vh;
    }

    .container-chart {
        width: 100% !important;
        margin: 0 auto;
    }

    .chart-container {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        gap: 10px; /* Mengurangi jarak antar card */
    }

    .chart-container .col-right {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px; /* Mengurangi jarak antar card */
        width: 30%; /* Lebar card pie chart dan total korban */
    }

    .pie-chart-container,
    .total-korban-container {
        width: 100%; /* Menyamakan lebar card pie chart dan total korban */
        max-width: 300px;
    }

    .chart-card {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }
</style>


</head>

<body>
    <div class="grid-container">
        <!-- SIDEBAR -->
        <div class="sidebar-container">
            <div class="sidebar-title">
                <div class="text-end close-btn">
                    <i class='bx bx-x'></i>
                </div>
                <h4 class="text-center">
                    Barcelona Datasets
                </h4>
            </div>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        Accident by Location
                    </a>
                </li>
                <li class="nav-item">
                    <a href="immigrant.php" class="nav-link">
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
            <i class='bx bx-menu'></i>
        </div>

        <!-- HASIL -->
        <div class="container">
            <h3 class="text-center">ACCIDENT BY LOCATION</h3>
            <!-- FILTER -->
            <form method="post" action="" id="filterForm">
                <div class="row my-3">
                    <div class="col">
                        <label for="">FIND BY SEARCH</label>
                        <input type="text" class="form-control" name="search" placeholder="Search" value="<?php echo htmlspecialchars($searchTerm); ?>" id="searchInput">
                    </div>
                    <div class="col">
                        <label for="">SELECT DISCTRICT</label>
                        <select class="form-select" name="district" aria-label="Select District" id="districtSelect">
                            <option value="">Select District</option>
                            <?php foreach ($districts as $district) : ?>
                                <option value="<?php echo htmlspecialchars($district); ?>" <?php if ($selectedDistrict == $district) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($district); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label for="">SELECT NEIGHBORHOOD</label>
                        <select class="form-select" name="neighborhood" aria-label="Select Neighborhood" id="neighborhoodSelect">
                            <option value="">Select Neighborhood</option>
                            <?php foreach ($neighborhood as $neighborhood) : ?>
                                <option value="<?php echo htmlspecialchars($neighborhood); ?>" <?php if ($selectedNeighborhood == $neighborhood) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($neighborhood); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <br>
                        <a href="accident.php" class="btn btn-form" role="button" style="background-color: pink;">Lihat Data</a>
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </form>

            <div class="chart-container">
    <!-- TOP 5 KECELAKAAN -->
    <div class="col container-chart chart-card" style="text-align: center;">
        <p class="card-title">TOP 5 ACCIDENT BY DISTRICT</p><br>
        <canvas id="topDistrictsChart" height="210"></canvas>
    </div>

    <!-- CARD DAN PIE CHART -->
    <div class="col col-right">
        <!-- CARD TOTAL KORBAN -->
        <div class="card-body chart-card total-korban-container" style="text-align: center;">
            <p class="card-title">TOTAL KORBAN</p>
            <p class="card-text" style="font-size: 30px;"><?php echo $totalAccidents; ?></p>
        </div>

        <!-- PIE CHART PERBANDINGAN JUMLAH CEDERA RINGAN DAN SERIUS  -->
        <div class="pie-chart-container chart-card">
            <canvas id="injuryChart"></canvas>
        </div>
    </div>

    <!-- KECELAKAAN BY PART OF THE DAY -->
    <div class="col container-chart chart-card" style="text-align: center;">
        <p class="card-title">ACCIDENT BY PART OF THE DAY</p><br>
        <canvas id="accidentChart" height="280"></canvas>
    </div>
</div>


        </div>

        <script>
            // RESET FILTER
            function resetFilters() {
                document.getElementById('searchInput').value = '';
                document.getElementById('districtSelect').value = '';
                document.getElementById('neighborhoodSelect').value = '';
                document.getElementById('filterForm').submit();
            }

            // AGAR FILTER BISA OTOMATIS
            document.getElementById('searchInput').addEventListener('input', function() {
                document.getElementById('filterForm').submit();
            });
            document.getElementById('districtSelect').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
            document.getElementById('neighborhoodSelect').addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });

            // BAR CHART
            const ctx = document.getElementById('accidentChart').getContext('2d');
            const accidentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Dawn', 'Morning', 'Afternoon', 'Evening', 'Night'],
                    datasets: [{
                        label: 'Number of accident',
                        data: [
                            <?php echo $chartData['Dawn']; ?>,
                            <?php echo $chartData['Morning']; ?>,
                            <?php echo $chartData['Afternoon']; ?>,
                            <?php echo $chartData['Evening']; ?>,
                            <?php echo $chartData['Night']; ?>
                        ],
                        backgroundColor: ["rgba(255, 99, 132, 0.2)",
                            "rgba(255, 159, 64, 0.2)",
                            "rgba(255, 205, 86, 0.2)",
                            "rgba(75, 192, 192, 0.2)",
                            "rgba(54, 162, 235, 0.2)"
                        ],
                        borderColor: ["rgb(255, 99, 132)",
                            "rgb(255, 159, 64)",
                            "rgb(255, 205, 86)",
                            "rgb(75, 192, 192)",
                            "rgb(54, 162, 235)"
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // PIE CHART
            const pieCtx = document.getElementById('injuryChart').getContext('2d');
            const injuryChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['Serious Injuries', 'Mild Injuries'],
                    datasets: [{
                        label: 'Injuries',
                        data: [<?php echo $seriousInjuries; ?>, <?php echo $mildInjuries; ?>],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)'
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });

            // BAR CHART TOPIK DISTRICT
            const topDistrictsData = <?php echo json_encode($topDistricts); ?>;

            const ctxTopDistricts = document.getElementById('topDistrictsChart').getContext('2d');
            const topDistrictsChart = new Chart(ctxTopDistricts, {
                type: 'polarArea', // Ubah type menjadi 'polarArea'
                data: {
                    labels: <?php echo json_encode(array_keys($topDistricts)); ?>,
                    datasets: [{
                        label: 'Top 5 Accident by District',
                        data: <?php echo json_encode(array_values($topDistricts)); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 205, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)'
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // SCRIPT SIDE BAR
            const sidebarContainer = document.querySelector(".sidebar-container");
            const gridContainer = document.querySelector(".grid-container");
            const closeButton = document.querySelector(".close-btn");
            const menuButton = document.querySelector(".menu-btn");

            closeButton.addEventListener('click', closeSidebar);
            menuButton.addEventListener('click', openSidebar);

            function closeSidebar() {
                console.log('close');
                sidebarContainer.style.display = 'none';
                menuButton.style.display = 'block';
                gridContainer.style.gridTemplateColumns = 'auto';
            }

            function openSidebar() {
                sidebarContainer.style.display = 'block';
                menuButton.style.display = 'none';
                gridContainer.style.gridTemplateColumns = '1fr 3fr';
            }

            closeSidebar();
        </script>
    </div>
</body>