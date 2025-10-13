<?php
/* ================================================================
   helper functions
   ================================================================ */

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function section(string $title, int $level = 2) { // switch for h1/h2/h3
	switch ($level) {
		case 1: echo "<h1>$title</h1>"; break;
		case 3: echo "<h3>$title</h3>"; break;
		default: echo "<h2>$title</h2>";
	}
}

function antiXss($s) {
	// htmlspecialchars takes special chars as "plain"/safe text
	return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** store a temp message (success or error) in the current session */
function flash(string $type, string $msg): void {
	$_SESSION['flashes'][] = ['type'=>$type,'msg'=>$msg];
}

/** get the message from the current session and then deletes it */
function get_flashes(): array {
	$f = $_SESSION['flashes'] ?? [];
	unset($_SESSION['flashes']);
	return $f;
}

/** Utilities */
function stars(int $n): string {
	$n = max(1, min(5, $n));
	return str_repeat('★', $n) . str_repeat('☆', 5-$n);
}

/** @return list<string> */
function get_genres(): array {
  return ['Action','Drama','Comedy','Sci-Fi','Romance','Horror','Documentary','Animation','Other'];
}

/** Optional img upload */
function upload_image(string $field = 'poster'): array {
	$errs = [];
	//check if the user submit an image for upload
	if (empty($_FILES[$field]['name'])) {
		return [null, $errs];
	}
	//chck if there has been any upload error
	$f = $_FILES[$field];
	if ($f['error'] !== UPLOAD_ERR_OK) { // UPLOAD_ERR_OK = 0 = everything went well
		if ($f['error'] !== UPLOAD_ERR_NO_FILE) { // if error is other than "no file uploaded", then consider it as a real error
			$errs[] = 'Upload error';
		}
		return [null, $errs];
	}
	//avoid big images
	if ($f['size'] > 2*1024*1024) {
		$errs[] = 'Image too large (max 2MB)';
		return [null, $errs];
	}
	// accept only valid types of img files
	$fi = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($fi, $f['tmp_name']); finfo_close($fi);
	$map = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
	if (!isset($map[$mime])) {
		$errs[] = 'Only JPG/PNG/WebP allowed';
		return [null, $errs];
	}
	// create the directory if it doesnt exist yet
	$uploadDir  = __DIR__ . '/.uploads';
	$publicBase = '/.uploads';
	if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true)) {
		$errs[]='Upload dir cannot be created';
		return [null,$errs];
	}
	if (!is_writable($uploadDir)) {
		$errs[]='Upload dir not writable';
		return [null,$errs];
	}
	// generate a uniqu name to save the image
	$name = 'img_' . bin2hex(random_bytes(6)) . '.' . $map[$mime];
	$dest = $uploadDir . '/' . $name;
	if (!@move_uploaded_file($f['tmp_name'], $dest)) {
		$errs[]='Cannot move uploaded file';
		return [null,$errs];
	}

 	return [$publicBase.'/'.$name, $errs];
}

/** Validation rules and basic parsing for create/update */
function validate_review(array $in): array {
	$genres = get_genres();
	$title  = filter_var(trim($in['title'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $genre  = filter_var($in['genre'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $review = filter_var(trim($in['review'] ?? ''), FILTER_UNSAFE_RAW);
    $rating = filter_var($in['rating'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 5]
    ]);
    $watch  = filter_var($in['watch_date'] ?? '', FILTER_SANITIZE_STRING);

	$errors = [];
	if ($title === '' || $title === null) {
		$errors[] = 'Title is required';
	}
	if (!in_array($genre,$genres,true)) {
		$errors[] = 'Invalid genre';
	}
	if ($rating === false) {
		$errors[] = 'Rating must be an integer between 1 and 5';
	}
	// validation of date format with DateTime class (better than just a preg_match)
	$dt = DateTime::createFromFormat('Y-m-d', $watch);
    if (!$dt || $dt->format('Y-m-d') !== $watch) {
        $errors[] = 'Invalid date format (YYYY-MM-DD expected)';
    }

	$clean = [
        'title'      => $title,
        'genre'      => $genre,
        'rating'     => $rating === false ? 3 : $rating,
        'review'     => $review,
        'watch_date' => $watch,
    ];

	return [$clean, $errors];
}

/** creates the connection to the DB (Postgres) and returns a PHP Data Object (PDO)*/
function pdo_pg(): PDO {
	$host = getenv('DB_HOST') ?: 'db';
	$port = (int)(getenv('DB_PORT') ?: 5432);
	$db   = getenv('DB_NAME') ?: 'appdb';
	$user = getenv('DB_USER') ?: 'app';
	$pass = getenv('DB_PASS') ?: 'secret';
	$dsn  = "pgsql:host=$host;port=$port;dbname=$db";
	return new PDO($dsn, $user, $pass, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);
}

/** Create table in DB if doesnt exist yet. It needs the PDO*/
function ensure_movies_table(PDO $pdo): void {
	$pdo->exec("
	CREATE TABLE IF NOT EXISTS movie_reviews (
		id           	SERIAL PRIMARY KEY,
		title        	TEXT NOT NULL,
		genre        	TEXT NOT NULL,
		rating       	INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
		review       	TEXT,
		watch_date   	DATE NOT NULL,
		image_path 	TEXT,
		created_at   	TIMESTAMPTZ NOT NULL DEFAULT NOW(),
		updated_at   	TIMESTAMPTZ NOT NULL DEFAULT NOW()
	)
	");
}

/** Insert or update a review and return id */
function save_review(PDO $pdo, array $data, ?int $id = null): int {
  if ($id) {
    $stmt = $pdo->prepare("
      UPDATE movie_reviews
         SET title=:t, genre=:g, rating=:r, review=:rv, watch_date=:d, image_path=:p, updated_at=NOW()
       WHERE id=:id
    ");
    $stmt->execute([
      ':t'=>$data['title'], ':g'=>$data['genre'], ':r'=>$data['rating'], ':rv'=>$data['review'],
      ':d'=>$data['watch_date'], ':p'=>($data['image_path'] ?? null), ':id'=>$id
    ]);
    return $id;
  } else {
    $stmt = $pdo->prepare("
      INSERT INTO movie_reviews (title, genre, rating, review, watch_date, image_path)
      VALUES (:t, :g, :r, :rv, :d, :p)
      RETURNING id
    ");
    $stmt->execute([
      ':t'=>$data['title'], ':g'=>$data['genre'], ':r'=>$data['rating'], ':rv'=>$data['review'],
      ':d'=>$data['watch_date'], ':p'=>($data['image_path'] ?? null)
    ]);
    return (int)$stmt->fetchColumn();
  }
}