<?php
// File containing notes
$file = 'notes.txt';

// Check if index parameter is set
if (isset($_GET['index'])) {
    $index = (int)$_GET['index'];

    // Load notes from the file
    $notes = file($file, FILE_IGNORE_NEW_LINES);

    // Check if index is valid
    if (isset($notes[$index])) {
        // Remove the note at the specified index
        unset($notes[$index]);

        // Save updated notes back to the file
        file_put_contents($file, implode("\n", $notes) . "\n");

        // Redirect to index.php with success message
        header("Location: index.php?deleted=true");
        exit;
    }
}

// Redirect to index.php if index is not valid
header("Location: index.php");
exit;
?>
