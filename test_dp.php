<?php
// test_db.php  ─  DELETE this file before submitting!

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ── 1. Test Connection ────────────────────────────────────────
$conn = new mysqli('localhost', 'root', '', 'movie_tracker');

if ($conn->connect_error) {
    die("<h2 style='color:red'>❌ Connection Failed: " . $conn->connect_error . "</h2>");
}
echo "<h2 style='color:green'>✅ Database Connected!</h2>";

// ── 2. Test INSERT ────────────────────────────────────────────
$stmt = $conn->prepare(
    "INSERT INTO watchlist 
        (user_id, tmdb_id, title, poster_path, genre, release_year, status, notes)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

$user_id     = 1;
$tmdb_id     = 550;
$title       = "Fight Club";
$poster      = "https://image.tmdb.org/t/p/w500/sample.jpg";
$genre       = "Drama";
$year        = 1999;
$status      = "want_to_watch";
$notes       = "Test note";

$stmt->bind_param('iissssss', $user_id, $tmdb_id, $title, $poster, $genre, $year, $status, $notes);

if ($stmt->execute()) {
    echo "<h2 style='color:green'>✅ INSERT Works! Movie added.</h2>";
    $inserted_id = $conn->insert_id;
} else {
    echo "<h2 style='color:red'>❌ INSERT Failed: " . $stmt->error . "</h2>";
    $inserted_id = null;
}
$stmt->close();

// ── 3. Test READ ──────────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM watchlist WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!empty($rows)) {
    echo "<h2 style='color:green'>✅ READ Works! Movies in watchlist:</h2>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse'>";
    echo "<tr style='background:#eee'>
            <th>ID</th><th>Title</th><th>Genre</th>
            <th>Year</th><th>Status</th><th>Notes</th>
          </tr>";
    foreach ($rows as $row) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>" . htmlspecialchars($row['title'])  . "</td>
                <td>" . htmlspecialchars($row['genre'])  . "</td>
                <td>{$row['release_year']}</td>
                <td>{$row['status']}</td>
                <td>" . htmlspecialchars($row['notes'])  . "</td>
              </tr>";
    }
    echo "</table><br>";
} else {
    echo "<h2 style='color:red'>❌ READ returned nothing.</h2>";
}

// ── 4. Test UPDATE ────────────────────────────────────────────
if ($inserted_id) {
    $new_status = "watched";
    $new_rating = 9;
    $new_notes  = "Updated note - great movie!";

    $stmt = $conn->prepare(
        "UPDATE watchlist SET status = ?, user_rating = ?, notes = ?
         WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param('sisii', $new_status, $new_rating, $new_notes, $inserted_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<h2 style='color:green'>✅ UPDATE Works! Status changed to 'watched'.</h2>";
    } else {
        echo "<h2 style='color:red'>❌ UPDATE Failed.</h2>";
    }
    $stmt->close();
}

// ── 5. Test DELETE ────────────────────────────────────────────
if ($inserted_id) {
    $stmt = $conn->prepare("DELETE FROM watchlist WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $inserted_id, $user_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo "<h2 style='color:green'>✅ DELETE Works! Movie removed.</h2>";
    } else {
        echo "<h2 style='color:red'>❌ DELETE Failed.</h2>";
    }
    $stmt->close();
}

$conn->close();
echo "<hr><h3>All tests done! If all 4 are green, your DB_Ops.php is ready ✅</h3>";
echo "<p style='color:red'><strong>⚠️ Remember to delete this file before submitting!</strong></p>";
?>
