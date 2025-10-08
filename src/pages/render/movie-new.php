<?php
/* ================================================================
   create new review view
   ================================================================ */

section('New review', 2);
$pdo = pdo_pg(); ensure_movies_table($pdo);

$data = ['title'=>'','genre'=>'','rating'=>3,'review'=>'','watch_date'=>date('Y-m-d'),'image_path'=>null];

$actionUrl   = '?p=movie-save';   // wihtout id to create a new one
$submitLabel = 'Create';

include __DIR__.'/_movie_form.php';