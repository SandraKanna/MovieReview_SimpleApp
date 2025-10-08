<?php

/* ================================================================
   edit and existing review (view)
   ================================================================ */

section('Edit review', 2);
$pdo = pdo_pg(); ensure_movies_table($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM movie_reviews WHERE id=:id");
$stmt->execute([':id'=>$id]);
$data = $stmt->fetch();

if (!$data) {
    flash('error','Not found');
    include __DIR__.'/movie-list.php';
    return;
}

$actionUrl   = '?p=movie-save&id='.$id;  // with id to update an existing one
$submitLabel = 'Update';

include __DIR__.'/_movie_form.php';