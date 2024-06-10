<?php
include './includes/head.php';

?>

<body>
  <!-- Sidebar -->
  <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 280px; height: 100vh;">
    <div class="fs-4 text-center">Barcelona Datasets</div>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <a href="index.php" class="nav-link">
          Accident by Location
        </a>
      </li>
      <li class="nav-item">
        <a href="immigrant.php" class="nav-link">
          Immigrant by Nationality
        </a>
      </li>
      <li class="nav-item">
        <a href="birth.php" class="nav-link active">
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
</body>