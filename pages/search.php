<?php
include("../controllers/page_controller.php");
require_once __DIR__ . '/../repositories/db_connect.php';

$brand      = $_GET['brand']      ?? '';
$engine     = $_GET['engine']     ?? '';
$year_start = $_GET['year_start'] ?? '';
$year_end   = $_GET['year_end']   ?? '';
$country    = $_GET['country']    ?? '';
$keyword    = $_GET['keyword']    ?? '';

$year_start = ($year_start === '') ? null : (int)$year_start;
$year_end   = ($year_end   === '') ? null : (int)$year_end;

$rows  = [];
$error = null;

if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare("CALL search_cars(:brand, :engine, :year_start, :year_end, :country, :keyword)");
        $stmt->execute([
            ':brand'      => $brand ?: null,
            ':engine'     => $engine ?: null,
            ':year_start' => $year_start,
            ':year_end'   => $year_end,
            ':country'    => $country ?: null,
            ':keyword'    => $keyword ?: null
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        foreach ($rows as &$car) {
            $carId = $car['c_id'] ?? 0;
            if ($carId) {
                $stmtAvg = $pdo->prepare("CALL GetCarAverageReview(:car_id)");
                $stmtAvg->bindParam(':car_id', $carId, PDO::PARAM_INT);
                $stmtAvg->execute();
                $avgData = $stmtAvg->fetch(PDO::FETCH_ASSOC);
                $stmtAvg->closeCursor();

                $car['avg_rating']    = $avgData['avg_rating'] ?? 0;
                $car['total_reviews'] = $avgData['total_reviews'] ?? 0;
            }
        }
        unset($car);
    } catch (PDOException $e) {
        error_log("Search page error: " . $e->getMessage());
        $error = "Something went wrong. Please try again later.";
    }
} else {
    $error = "No database connection found.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Search Cars — Carverly</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../styles/styles/search.css" />
</head>
<body>
<header>
    <div class="navbar">
        <div class="brand-container">
            <img src="https://i.postimg.cc/63bx44WN/car-logo.jpg" alt="Logo" />
            <div class="logo-text">carverly</div>
        </div>
        <div class="nav-icons"></div>
    </div>
</header>

<div class="breadcrumb">
    <a href="homepage.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <span>Search</span>
</div>

<h1 class="section-title">Find your next car</h1>

<main class="wrap">
    <form method="get" class="search-form" autocomplete="off">
      <div class="grid">
        <div class="field">
          <label for="brand">Brand</label>
          <input id="brand" type="text" name="brand" value="<?= htmlspecialchars($brand) ?>">
        </div>
        <div class="field">
          <label for="engine">Engine (contains)</label>
          <input id="engine" type="text" name="engine" value="<?= htmlspecialchars($engine) ?>">
        </div>
        <div class="field">
          <label for="year_start">Release Year From</label>
          <input id="year_start" type="number" name="year_start" value="<?= htmlspecialchars($_GET['year_start'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="year_end">Release Year To</label>
          <input id="year_end" type="number" name="year_end" value="<?= htmlspecialchars($_GET['year_end'] ?? '') ?>">
        </div>
        <div class="field">
          <label for="country">Country</label>
          <input id="country" type="text" name="country" value="<?= htmlspecialchars($country) ?>">
        </div>
        <div class="field">
          <label for="keyword">Keyword</label>
          <input id="keyword" type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>">
        </div>
        <div class="actions">
          <button class="btn search-btn" type="submit">
            <i class="fa-solid fa-magnifying-glass"></i> Search
          </button>
          <a class="btn clear-btn" href="search.php">Clear</a>
        </div>
      </div>
    </form>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="results">
    <table class="results-table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Model</th>
      <th>Brand</th>
      <th>Engine</th>
      <th>Release Date</th>
      <th>Country</th>
      <th>Reviews</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!empty($rows)): ?>
      <?php foreach ($rows as $r): ?>
      <tr onclick="window.location='car.php?c_id=<?= urlencode($r['c_id'] ?? '') ?>'">
        <td>
          <div class="car-name"><?= htmlspecialchars($r['c_name'] ?? '') ?></div>
          <div class="muted small"><?= htmlspecialchars(mb_strimwidth($r['c_description'] ?? '', 0, 120, '…')) ?></div>
        </td>
        <td><?= htmlspecialchars($r['c_model'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['c_brand'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['c_engine'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['c_release_date'] ?? '') ?></td>
        <td><?= htmlspecialchars($r['c_country'] ?? '') ?></td>
        <td>⭐ <?= htmlspecialchars($r['avg_rating'] ?? 0) ?> / 5 (<?= htmlspecialchars($r['total_reviews'] ?? 0) ?>)</td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="7" class="muted">No results found. <a href="search.php">Clear filters</a> and try again.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
    </section>
</main>

<footer class="custom-footer small-footer">
    <div class="footer-content">
        <div class="footer-column">
            <h3>Help Centre</h3>
            <p>Mon–Fri: 9.00 - 18.00</p>
        </div>
        <div class="footer-column">
            <a href="#">About us</a>
            <a href="#">Contact us</a>
        </div>
        <div class="footer-column trustpilot">
            <p>Rated <strong>4.4</strong>/5</p>
        </div>
    </div>
    <hr>
    <div class="footer-bottom">
        <p>© 2025 Carverly Ltd. All rights reserved</p>
    </div>
</footer>

<script>
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "../controllers/logout.php";
        }
    });
}
const profileBtn = document.getElementById("ProfileBtn");
if (profileBtn) {
    profileBtn.addEventListener("click", function () {
        window.location.href = "../pages/userprofile.php";
    });
}
</script>
</body>
</html>
