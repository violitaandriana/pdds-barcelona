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

      <!-- Dropdown untuk memilih tahun -->
      <form method="GET" action="birth.php">
          <div class="form-group">
              <label for="year">Select Year:</label>
              <select name="year" id="year" class="form-control">
                  <option value="">All Years</option>
                  <?php
                  for ($i = 2013; $i <= 2017; $i++) {
                      $selected = (isset($_GET['year']) && $_GET['year'] == $i) ? 'selected' : '';
                      echo "<option value=\"$i\" $selected>$i</option>";
                  }
                  ?>
              </select>
              <button type="submit" class="btn btn-primary mt-2">Filter</button>
          </div>
      </form>

      <table class="table table-bordered mt-3">
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
              $yearFilter = isset($_GET['year']) ? (int)$_GET['year'] : null;
              $filter = $yearFilter ? ['Year' => $yearFilter] : [];
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
