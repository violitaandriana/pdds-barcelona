<?php
$servername = "localhost";  // Update with your server details
$username = "root";         // Update with your database username
$password = "";             // Update with your database password
$dbname = "pdds_barcelona"; // Update with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch distinct district names
$districts = array();
$districtResult = $conn->query("SELECT DISTINCT district_name FROM accident");
while ($row = $districtResult->fetch_assoc()) {
    if (strtolower(trim($row['district_name'])) !== 'district name') {
        $districts[] = $row['district_name'];
    }
}

// Initialize variable for filtered neighborhoods
$filteredNeighborhoods = array();

// Initialize selected district and neighborhood variables
$selectedDistrict = isset($_POST['district']) ? $_POST['district'] : '';
$selectedNeighborhood = isset($_POST['neighborhood']) ? $_POST['neighborhood'] : '';
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $selectedDistrict) {
    // Fetch distinct neighborhood names based on the selected district
    $neighborhoodResult = $conn->query("SELECT DISTINCT neighborhood_name FROM accident WHERE district_name = '" . $conn->real_escape_string($selectedDistrict) . "'");
    while ($row = $neighborhoodResult->fetch_assoc()) {
        $filteredNeighborhoods[] = $row['neighborhood_name'];
    }
}

// Initialize variable for filtered results
$filteredResults = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    $query = "SELECT * FROM accident";
    if (count($whereClauses) > 0) {
        $query .= " WHERE " . implode(' AND ', $whereClauses);
    }

    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $filteredResults[] = $row;
    }
}

// Fetch data for the bar chart
$chartData = array(
    'Dawn' => 0,
    'Morning' => 0,
    'Afternoon' => 0,
    'Evening' => 0,
    'Night' => 0
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accident Report 2017</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="grid-container">
        <!-- Sidebar -->
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
            <i class='bx bx-menu'></i>
        </div>
        <div class="container">
            <form method="post" action="">
                <div class="row my-3">
                    <div class="col">
                        <input type="text" class="form-control" name="search" placeholder="Search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                    <div class="col">
                        <select class="form-select" name="district" aria-label="Select District" onchange="this.form.submit()">
                            <option value="">Select District</option>
                            <?php foreach ($districts as $district) : ?>
                                <option value="<?php echo htmlspecialchars($district); ?>" <?php if ($selectedDistrict == $district) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($district); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-select" name="neighborhood" aria-label="Select Neighborhood">
                            <option value="">Select Neighborhood</option>
                            <?php foreach ($filteredNeighborhoods as $neighborhood) : ?>
                                <option value="<?php echo htmlspecialchars($neighborhood); ?>" <?php if ($selectedNeighborhood == $neighborhood) echo 'selected="selected"'; ?>><?php echo htmlspecialchars($neighborhood); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($filteredResults)) : ?>
                <div class="row">
                    <div class="col">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>District Name</th>
                                    <th>Neighborhood Name</th>
                                    <th>Street</th>
                                    <th>Weekday</th>
                                    <th>Month</th>
                                    <th>Day</th>
                                    <th>Hour</th>
                                    <th>Part Of The Day</th>
                                    <th>Mild Injuries</th>
                                    <th>Serious Injuries</th>
                                    <th>Victims</th>
                                    <th>Vehicle Involved</th>
                                    <!-- Add more columns as needed -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filteredResults as $result) : ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['id']); ?></td>
                                        <td><?php echo htmlspecialchars($result['district_name']); ?></td>
                                        <td><?php echo htmlspecialchars($result['neighborhood_name']); ?></td>
                                        <td><?php echo htmlspecialchars($result['street']); ?></td>
                                        <td><?php echo htmlspecialchars($result['weekday']); ?></td>
                                        <td><?php echo htmlspecialchars($result['month']); ?></td>
                                        <td><?php echo htmlspecialchars($result['day']); ?></td>
                                        <td><?php echo htmlspecialchars($result['hour']); ?></td>
                                        <td><?php echo htmlspecialchars($result['part_of_the_day']); ?></td>
                                        <td><?php echo htmlspecialchars($result['mild_injuries']); ?></td>
                                        <td><?php echo htmlspecialchars($result['serious_injuries']); ?></td>
                                        <td><?php echo htmlspecialchars($result['victims']); ?></td>
                                        <td><?php echo htmlspecialchars($result['vehicles_involved']); ?></td>
                                        <!-- Add more columns as needed -->
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col">
                    <canvas id="accidentChart"></canvas>
                </div>
            </div>
        </div>

        <script>
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
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
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
        </script>

</body>

</html>