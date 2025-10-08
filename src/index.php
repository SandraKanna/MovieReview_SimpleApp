<?php

/* 	===========================================================================
    Index: simple router
    =========================================================================== */

require __DIR__ . '/helpers.php';

$pages = [
  // views (include layout)
  'home'      => ['file' => __DIR__.'/pages/render/home.php'],
  'movies'      => ['file' => __DIR__.'/pages/render/movies.php'],
  'movie-new'   => ['file' => __DIR__.'/pages/render/movie-new.php'],
  'movie-edit'  => ['file' => __DIR__.'/pages/render/movie-edit.php'],

  // actions (without layout)
  'movie-create'=> ['file' => __DIR__.'/pages/actions/create.php', 'raw' => true],
  'movie-update'=> ['file' => __DIR__.'/pages/actions/update.php', 'raw' => true],
  'movie-delete'=> ['file' => __DIR__.'/pages/actions/delete.php', 'raw' => true],
];

$current = $_GET['p'] ?? 'home';
if (!isset($pages[$current])) {
  http_response_code(404); $current = 'home';
}

$meta = $pages[$current];

// if action → process the request without layout
if (!empty($meta['raw'])) {
  include $meta['file'];
  return;
}

// if view → render page with layout
include __DIR__ . '/layout/header.php';
include $meta['file'];
include __DIR__ . '/layout/footer.php';