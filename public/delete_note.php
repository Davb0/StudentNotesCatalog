<?php
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $note_id = (int) $_POST['note_id'];
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ?");
    $stmt->bind_param('i', $note_id);
    $stmt->execute();
    
    // Redirect back to index
    header('Location: index.php');
}
?>
