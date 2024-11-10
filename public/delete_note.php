<?php
if (!isset($_GET['note_id'])) {
    die("Note ID is required.");
}

$noteID = $_GET['note_id'];
$notes = file_exists('notes.txt') ? file('notes.txt', FILE_IGNORE_NEW_LINES) : [];
$newNotes = [];

foreach ($notes as $note) {
    if (strpos($note, $noteID . '|') !== 0) {  // Keep notes that do not match the ID
        $newNotes[] = $note;
    }
}

// Save the updated notes list
file_put_contents('notes.txt', implode("\n", $newNotes) . "\n");
header("Location: index.php");
exit();
