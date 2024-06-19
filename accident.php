<?php
// include "./includes/head.php";

// KONEKSI KE DB
$conn = mysqli_connect("localhost", "root", "", "pdds_barcelona");

// CEK KONEKSI
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$selectedDistrict = isset($_POST['district']) ? $_POST['district'] : (isset($_GET['district']) ? $_GET['district'] : '');
$selectedNeighborhood = isset($_POST['neighborhood']) ? $_POST['neighborhood'] : (isset($_GET['neighborhood']) ? $_GET['neighborhood'] : '');
$searchTerm = isset($_POST['search']) ? $_POST['search'] : (isset($_GET['search']) ? $_GET['search'] : '');
$resultsPerPage = isset($_POST['resultsPerPage']) ? (int)$_POST['resultsPerPage'] : (isset($_GET['resultsPerPage']) ? (int)$_GET['resultsPerPage'] : 10); // Default to 10

// AMBIL DATA DISTRICT NAME
$districts = array(); // yg nampung hasil district
$districtResult = $conn->query("SELECT DISTINCT district_name FROM accident");
while ($row = $districtResult->fetch_assoc()) {
    if (strtolower(trim($row['district_name'])) !== 'district name') {
        $districts[] = $row['district_name'];
    }
}

// AMBIL DATA NEIGHBORHOOD NAME BERDASAR DISTRICT NAME
$neighborhood = array(); // yg nampung hasil neighborhood
if ($selectedDistrict) {
    $neighborhoodResult = $conn->query("SELECT DISTINCT neighborhood_name FROM accident WHERE district_name = '" . $conn->real_escape_string($selectedDistrict) . "'");
    while ($row = $neighborhoodResult->fetch_assoc()) {
        $neighborhood[] = $row['neighborhood_name'];
    }
}

// PAGINATION
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $resultsPerPage;

// FILTER
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

// COUNT TOTAL RESULTS
$totalResultsQuery = "SELECT COUNT(*) as total FROM accident";
if (count($whereClauses) > 0) {
    $totalResultsQuery .= " WHERE " . implode(' AND ', $whereClauses);
}
$totalResultsResult = $conn->query($totalResultsQuery);
$totalResultsRow = $totalResultsResult->fetch_assoc();
$totalResults = $totalResultsRow['total'];

// FETCH RESULTS FOR CURRENT PAGE
$query .= " LIMIT $offset, $resultsPerPage";
$result = $conn->query($query);

$filteredResults = array();
while ($row = $result->fetch_assoc()) {
    $filteredResults[] = $row;
}

$totalPages = ceil($totalResults / $resultsPerPage);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accident Report 2017</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- HASIL -->
    <div class="container">
        <h3 class="text-center">ACCIDENT BY LOCATION</h3>

        <form method="post" action="" id="filterForm">
            <div class="row my-3">
                <div class="col">
                    <label for="">FIND BY SEARCH</label>
                    <input type="text" class="form-control" name="search" placeholder="Search" value="<?php echo htmlspecialchars($searchTerm); ?>" id="searchInput">
                </div>
                <div class="col">
                    <label for="">SELECT DISTRICT</label>
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
                    <label for="">ENTRIES PER PAGE</label>
                    <select class="form-select" name="resultsPerPage" aria-label="Results Per Page" id="resultsPerPageSelect">
                        <option value="10" <?php if ($resultsPerPage == 10) echo 'selected="selected"'; ?>>10</option>
                        <option value="25" <?php if ($resultsPerPage == 25) echo 'selected="selected"'; ?>>25</option>
                        <option value="50" <?php if ($resultsPerPage == 50) echo 'selected="selected"'; ?>>50</option>
                        <option value="100" <?php if ($resultsPerPage == 100) echo 'selected="selected"'; ?>>100</option>
                    </select>
                </div>
                <div class="col">
                    <br>
                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                    <a href="index.php" class="btn btn-form" role="button" style="background-color: pink;">Kembali</a>
                </div>
            </div>
        </form>
        <?php if (!empty($filteredResults)) : ?>
            <div class="row">
                <div class="col">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>District Name</th>
                                <th>Neighborhood Name</th>
                                <th>Street</th>
                                <th>Weekday</th>
                                <th>Month</th>
                                <th>Day</th>
                                <th>Hour</th>
                                <th>Part of the Day</th>
                                <th>Mild Injuries</th>
                                <th>Serious Injuries</th>
                                <th>Victims</th>
                                <th>Vehicle Involved</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredResults as $result) : ?>
                                <tr>
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
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p>Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $resultsPerPage, $totalResults); ?> of <?php echo $totalResults; ?> entries</p>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end">
                            <?php 
                            $params = "&search=".urlencode($searchTerm)."&district=".urlencode($selectedDistrict)."&neighborhood=".urlencode($selectedNeighborhood)."&resultsPerPage=$resultsPerPage";
                            if ($page > 1) : ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1 . $params; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php 
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $startPage + 4);
                                if ($endPage - $startPage < 4) {
                                    $startPage = max(1, $endPage - 4);
                                }
                                for ($i = $startPage; $i <= $endPage; $i++) : 
                            ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i . $params; ?>"><?php echo $i; ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages) : ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1 . $params; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // RISET FILTER
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('districtSelect').value = '';
            document.getElementById('neighborhoodSelect').value = '';
            document.getElementById('resultsPerPageSelect').value = '10';
            document.getElementById('filterForm').submit();
        }

        // AGAR FILTER BISA OTOMATIS
        document.getElementById('searchInput').addEventListener('input', function () {
            document.getElementById('filterForm').submit();
        });
        document.getElementById('districtSelect').addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
        document.getElementById('neighborhoodSelect').addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
        document.getElementById('resultsPerPageSelect').addEventListener('change', function () {
            document.getElementById('filterForm').submit();
        });
    </script>
</body>

</html>
