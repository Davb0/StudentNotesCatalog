<?php
session_start();

// Translation settings
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$translations = [
    'en' => [
        'title' => 'Student Notes Catalog',
        'login' => 'Login',
        'logout' => 'Logout',
        'username' => 'Username',
        'password' => 'Password',
        'submit' => 'Submit',
        'add_note' => 'Add Note',
        'student_name' => 'Student Name',
        'subject' => 'Subject',
        'note' => 'Note',
        'teacher_remarks' => "Teacher's Remarks",
        'absence_details' => 'Absence Details (this week)',
        'date' => 'Date',
        'all_notes' => 'All Notes',
        'delete' => 'Delete',
        'note_added' => 'Note added successfully!',
        'note_deleted' => 'Note deleted successfully!',
        'no_notes' => 'No notes available.',
        'login_error' => 'Invalid username or password.'
    ],
    'ro' => [
        'title' => 'Catalogul Notițelor Elevilor',
        'login' => 'Autentificare',
        'logout' => 'Deconectare',
        'username' => 'Nume utilizator',
        'password' => 'Parolă',
        'submit' => 'Trimite',
        'add_note' => 'Adaugă Notă',
        'student_name' => 'Numele Elevului',
        'subject' => 'Materie',
        'note' => 'Notă',
        'teacher_remarks' => 'Mențiuni ale Profesorului',
        'absence_details' => 'Detalii Absență (săptămâna aceasta)',
        'date' => 'Data',
        'all_notes' => 'Toate Notițele',
        'delete' => 'Șterge',
        'note_added' => 'Notă adăugată cu succes!',
        'note_deleted' => 'Notă ștearsă cu succes!',
        'no_notes' => 'Nu sunt notițe disponibile.',
        'login_error' => 'Nume utilizator sau parolă incorectă.'
    ]
];
$t = $translations[$lang];

// Define credentials
$validUsername = 'admin';
$validPassword = 'password123';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $validUsername && $password === $validPassword) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
    } else {
        $loginError = $t['login_error'];
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php?lang=$lang");
    exit();
}

// Handle adding notes and deletion if logged in
$message = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
        $studentName = htmlspecialchars($_POST['student_name']);
        $subject = htmlspecialchars($_POST['subject']);
        $noteContent = htmlspecialchars($_POST['note_content']);
        $teacherRemarks = htmlspecialchars($_POST['teacher_remarks']);
        $absenceDetails = htmlspecialchars($_POST['absence_details']);
        $date = htmlspecialchars($_POST['date']);
        $noteID = uniqid();

        $note = "$noteID|$studentName|$subject|$noteContent|$teacherRemarks|$absenceDetails|$date|\n";
        if (file_put_contents('notes.txt', $note, FILE_APPEND | LOCK_EX)) {
            $message = $t['note_added'];
        }
    }

    if (isset($_GET['delete_id'])) {
        $deleteID = $_GET['delete_id'];
        $notes = file('notes.txt', FILE_IGNORE_NEW_LINES);
        $newNotes = array_filter($notes, function($note) use ($deleteID) {
            return !str_starts_with($note, $deleteID . '|');
        });
        file_put_contents('notes.txt', implode("\n", $newNotes) . "\n");
        $message = $t['note_deleted'];
    }
}

// Display all notes
$notes = file_exists('notes.txt') ? file('notes.txt', FILE_IGNORE_NEW_LINES) : [];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $t['title']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .container { width: 80%; margin: auto; }
        .note { border: 1px solid #ccc; padding: 10px; margin: 10px 0; }
        .delete-button { color: red; text-decoration: none; cursor: pointer; }
        .delete-button:hover { color: darkred; }
        .message { color: green; }
        .login-form { max-width: 300px; margin: auto; padding: 20px; border: 1px solid #ddd; }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo $t['title']; ?></h1>
    
    <!-- Language Selector -->
    <a href="index.php?lang=en">English</a> | <a href="index.php?lang=ro">Română</a>

    <!-- Login Form or Logout Button -->
    <?php if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']): ?>
        <div class="login-form">
            <h2><?php echo $t['login']; ?></h2>
            <form method="POST">
                <input type="text" name="username" placeholder="<?php echo $t['username']; ?>" required><br>
                <input type="password" name="password" placeholder="<?php echo $t['password']; ?>" required><br>
                <button type="submit" name="login"><?php echo $t['submit']; ?></button>
            </form>
            <?php if (isset($loginError)): ?>
                <p style="color:red;"><?php echo $loginError; ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <a href="index.php?logout=true"><?php echo $t['logout']; ?></a>
        
        <!-- Display Messages -->
        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- Form for Adding Notes -->
        <form action="index.php?lang=<?php echo $lang; ?>" method="POST">
            <input type="text" name="student_name" placeholder="<?php echo $t['student_name']; ?>" required>
            <input type="text" name="subject" placeholder="<?php echo $t['subject']; ?>" required>
            <textarea name="note_content" placeholder="<?php echo $t['note']; ?>" required></textarea>
            <textarea name="teacher_remarks" placeholder="<?php echo $t['teacher_remarks']; ?>"></textarea>
            <textarea name="absence_details" placeholder="<?php echo $t['absence_details']; ?>"></textarea>
            <label><?php echo $t['date']; ?>:</label>
            <input type="text" id="date" name="date" required>
            <button type="submit" name="add_note"><?php echo $t['add_note']; ?></button>
        </form>

        <!-- Display All Notes -->
        <h2><?php echo $t['all_notes']; ?></h2>
        <?php if (count($notes) > 0): ?>
            <?php foreach ($notes as $note): ?>
                <?php
                list($id, $studentName, $subject, $noteContent, $teacherRemarks, $absenceDetails, $date) = explode('|', $note);
                ?>
                <div class="note">
                    <p><strong><?php echo $t['student_name']; ?>:</strong> <?php echo $studentName; ?></p>
                    <p><strong><?php echo $t['subject']; ?>:</strong> <?php echo $subject; ?></p>
                    <p><strong><?php echo $t['note']; ?>:</strong> <?php echo $noteContent; ?></p>
                    <p><strong><?php echo $t['teacher_remarks']; ?>:</strong> <?php echo $teacherRemarks; ?></p>
                    <p><strong><?php echo $t['absence_details']; ?>:</strong> <?php echo $absenceDetails; ?></p>
                    <p><strong><?php echo $t['date']; ?>:</strong> <?php echo $date; ?></p>
                    <a href="index.php?delete_id=<?php echo $id; ?>&lang=<?php echo $lang; ?>" class="delete-button"><?php echo $t['delete']; ?></a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?php echo $t['no_notes']; ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    flatpickr("#date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        defaultDate: new Date()
    });
</script>
</body>
</html>
