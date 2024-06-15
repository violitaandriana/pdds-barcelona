<?php
include "./includes/head.php";
require "functions.php";

$year = '';

$rows = findTopDistricts($year);

?>

<body>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th scope="col">District Name</th>
        <th scope="col">Neighborhood Name</th>
        <th scope="col">Nationality</th>
        <th scope="col">Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $row) { ?>
      <tr>
        <td><?= $row['district_name'] ?></td>
        <td><?= $row['neighborhood_name'] ?></td>
        <td><?= $row['nationality'] ?></td>
        <td><?= $row['total'] ?></td>
      </tr>
      <?php  } ?>
    </tbody>
  </table>
  <script>

  </script>
</body>

</html>