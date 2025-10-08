<?php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header("Referrer-Policy: no-referrer-when-downgrade");
/* CSP simple: solo servimos nuestros propios recursos.
   - script-src 'self' (sin inline)
   - style-src 'self' (solo tu CSS)
   - img-src 'self' data: (para <img src="data:..."> si hiciera falta)
*/
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;");

$cur = $_GET['p'] ?? 'home';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Movie Reviews</title>
    <!-- v=2 forces css reload -->
    <link rel="stylesheet" href="/assets/style.css?v=2">
    <!-- call to a JS script for the delte warning prompt -->
    <script src="/assets/app.js" defer></script>
  </head>
  <body>
    <div class="container">
      <header>
        <h1>Movie Reviews</h1>
        <nav class="navbar">
          <a class="<?= $cur==='home'?'active':'' ?>" href="?p=home">Home</a>
          <a class="<?= $cur==='movies'?'active':'' ?>" href="?p=movies">Movies</a>
          <a class="<?= $cur==='movie-new'?'active':'' ?>" href="?p=movie-new">+ New review</a>
        </nav>
        <hr>
      </header>

      <?php foreach (get_flashes() as $f): ?>
        <div class="panel <?= $f['type']==='success'?'success':'error' ?>">
          <?= htmlspecialchars($f['msg']) ?>
        </div>
      <?php endforeach; ?>
   