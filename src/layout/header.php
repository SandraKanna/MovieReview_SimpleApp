<?php
header('X-Content-Type-Options: nosniff'); /* use exactly the MIME type specified in the request */
header('X-Frame-Options: SAMEORIGIN'); /* avoid clickjacking, only use <iframe> in the same domain*/
header("Referrer-Policy: no-referrer-when-downgrade"); /* do not inlcude private data/tokens/routes if not https */
/* CSP simple: we serve only our resouces*/
header("Content-Security-Policy: default-src 'selfq'; script-src 'self'; style-src 'self'; img-src 'self' data:;");

$cur = $_GET['p'] ?? 'home';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Movie Reviews</title>
    <!-- v=2 forces css reload -->
    <link rel="stylesheet" href="/assets/style.css?v=2">
    <!-- listener in a JS script for the delete warning prompt. Use defer to avoid blocking the page load -->
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
   