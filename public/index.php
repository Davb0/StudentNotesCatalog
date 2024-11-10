<?php
// Include database connection
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $student_name = htmlspecialchars($_POST['student_name']);
    $note_content = htmlspecialchars($_POST['note_content']);
    $subject = htmlspecialchars($_POST['subject']);
    $teacher_comments = htmlspecialchars($_POST['teacher_comments']);
    $absent = isset($_POST['absent']) ? 1 : 0;

    // File upload handling
    $file = null;
    if (isset($_FILES['file'])) {
        $file = 'uploads/' . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $file);
    }

    // Insert note into the database
    $stmt = $conn->prepare("INSERT INTO notes (student_name, note_content, subject, teacher_comments, absent, file) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssds', $student_name, $note_content, $subject, $teacher_comments, $absent, $file);
    $stmt->execute();
}

// Fetch all notes
$result = $conn->query("SELECT * FROM notes ORDER BY date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Notes Catalog</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <div class="container">
        <h1>Student Notes Catalog</h1>

        <!-- Form to add new note -->
        <form action="index.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="student_name" placeholder="Student Name" required>
            <textarea name="note_content" placeholder="Write the note here..." required></textarea>
            <input type="text" name="subject" placeholder="Subject" required>
            <textarea name="teacher_comments" placeholder="Teacher's comments"></textarea>
            <label for="absent">Was the student absent this week?</label>
            <input type="checkbox" name="absent">
            <input type="file" name="file">
            <button type="submit">Add Note</button>
        </form>

        <!-- Display all notes -->
        <h2>All Notes:</h2>
        <?php while ($note = $result->fetch_assoc()) { ?>
            <div class="note">
                <strong><?php echo htmlspecialchars($note['student_name']); ?></strong>
                <p><?php echo htmlspecialchars($note['note_content']); ?></p>
                <p><strong>Subject:</strong> <?php echo htmlspecialchars($note['subject']); ?></p>
                <p><strong>Teacher's Comments:</strong> <?php echo htmlspecialchars($note['teacher_comments']); ?></p>
                <p><strong>Date Added:</strong> <?php echo $note['date_added']; ?></p>
                <?php if ($note['file']) { ?>
                    <p><a href="<?php echo $note['file']; ?>" target="_blank">Download File</a></p>
                <?php } ?>
                <form action="delete_note.php" method="POST">
                    <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
                    <button type="submit">Delete Note</button>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>

