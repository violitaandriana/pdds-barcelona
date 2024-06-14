<?php
include './includes/head.php';

require 'autoload.php'; // Memuat MongoDB PHP Library

use MongoDB\Client;
$client = new Client("mongodb://localhost:27017");
$collection = $client->pdds_barcelona->Birth_Rate;
?>

<style>
  body {
      display: flex;
      margin: 0;
      padding: 0;
      height: 100vh;
      overflow: hidden;
  }

  .sidebar {
      width: 280px;
      height: 100vh;
      background: #f8f9fa;
      padding: 20px;
      position: fixed;
  }

  .main-content {
      margin-left: 280px; /* Sama dengan lebar sidebar */
      padding: 20px;
      flex-grow: 1;
      overflow-y: auto;
      height: 100vh;
  }

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

  .filter-item {
      display: flex;
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
  }

  .filter-item button {
      margin-left: 20px;
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

  .table thead th {
      background-color: #007bff;
      color: #fff;
  }

  .table {
      width: 100%;
      margin-top: 20px;
  }
</style>

<body>
  <div class="sidebar d-flex flex-column flex-shrink-0 p-3 bg-light">
    <div class="fs-4 text-center">Barcelona Datasets</div>
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

  <div class="main-content">
      <h1>Birth Rate Pattern</h1>

      <!-- Filter box -->
      <div class="filter-box">
          <form method="GET" action="birth.php" class="filter-item">
              <label for="year">Year:</label>
              <select name="year" id="year">
                  <option value="">All Years</option>
                  <?php
                  for ($i = 2013; $i <= 2017; $i++) {
                      $selected = (isset($_GET['year']) && $_GET['year'] == $i) ? 'selected' : '';
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

      <table class="table table-bordered">
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
              // Mengambil nilai filter dari URL
              $yearFilter = isset($_GET['year']) ? (int)$_GET['year'] : null;
              $genderFilter = isset($_GET['gender']) ? $_GET['gender'] : null;

              // Membuat filter query untuk MongoDB
              $filter = [];
              if ($yearFilter) {
                  $filter['Year'] = $yearFilter;
              }
              if ($genderFilter) {
                  $filter['Gender'] = $genderFilter;
              }

              // Query ke MongoDB dengan filter
              try {
                  $cursor = $collection->find($filter);
                  foreach ($cursor as $document) {
                      echo "<tr>
                              <td>{$document['Year']}</td>
                              <td>{$document['District Code']}</td>
                              <td>{$document['District Name']}</td>
                              <td>{$document['Neighborhood Code']}</td>
                              <td>{$document['Neighborhood Name']}</td>
                              <td>{$document['Gender']}</td>
                              <td>{$document['Number']}</td>
                            </tr>";
                  }
              } catch (Exception $e) {
                  echo "<tr><td colspan='7'>Error retrieving data: " . $e->getMessage() . "</td></tr>";
              }
              ?>
          </tbody>
      </table>
  </div>
</body>
