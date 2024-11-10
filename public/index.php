<?php
session_start();

// Check for login/logout actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Replace these with your credentials
    $validUsername = 'admin';
    $validPassword = 'password123';

    if ($username === $validUsername && $password === $validPassword) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
    } else {
        $loginError = "Invalid username or password.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

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
        'login_error' => 'Invalid username or password.',
        'file_upload' => 'Attach File',
        'info' => 'Information',
        'info_title' => 'More Information',
        'info_text' => 'Catalog realized for testing knowledge. It is not real. It isnt neccesary to add an file, abscence or teacher remark. If there isnt abscence or remark write for each none.',
    ],
    'ro' => [
        'title' => 'Catalogul Notelor Elevilor',
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
        'all_notes' => 'Toate Notele',
        'delete' => 'Șterge',
        'note_added' => 'Notă adăugată cu succes!',
        'note_deleted' => 'Notă ștearsă cu succes!',
        'no_notes' => 'Nu sunt note disponibile.',
        'login_error' => 'Nume utilizator sau parolă incorectă.',
        'file_upload' => 'Atașează fișier',
        'info' => 'Informații',
        'info_title' => 'Mai multe informații',
        'info_text' => 'Catalog realizat pentru testarea cunoștințelor. Nu este real. Nu este necesar să adăugați un fișier, absență sau observație a profesorului. Dacă nu există absență sau observație, scrieti nicuna.',
    ]
];
$t = $translations[$lang];

// Handle messages
$message = '';

// File upload directory
$uploadDir = 'uploads/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// If logged in, handle note addition and deletion
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
        $studentName = htmlspecialchars($_POST['student_name']);
        $subject = htmlspecialchars($_POST['subject']);
        $noteContent = htmlspecialchars($_POST['note_content']);
        $teacherRemarks = htmlspecialchars($_POST['teacher_remarks']);
        $absenceDetails = htmlspecialchars($_POST['absence_details']);
        $date = htmlspecialchars($_POST['date']);
        $noteID = uniqid();

        // Handle file upload
        $filePath = '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['file']['name']);
            $filePath = $uploadDir . $noteID . "_" . $fileName;
            move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
        }

        // Add entry to notes file
        $note = "$noteID|$studentName|$subject|$noteContent|$teacherRemarks|$absenceDetails|$date|$filePath|\n";
        if (file_put_contents('notes.txt', $note, FILE_APPEND | LOCK_EX)) {
            $message = $t['note_added'];
        }
    }

    if (isset($_GET['delete_id'])) {
        $deleteID = $_GET['delete_id'];
        $notes = file('notes.txt', FILE_IGNORE_NEW_LINES);
        $newNotes = [];

        foreach ($notes as $note) {
            if (str_starts_with($note, $deleteID . '|')) {
                $parts = explode('|', $note);
                if (file_exists($parts[7])) {
                    unlink($parts[7]);
                }
            } else {
                $newNotes[] = $note;
            }
        }

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
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }
        .container {
            width: 80%;
            margin: auto;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .note {
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .note p {
            margin: 5px 0;
        }
        .delete-button {
            color: red;
            text-decoration: none;
        }
        .delete-button:hover {
            color: darkred;
        }
        .message {
            color: green;
        }
        .login-form, .add-note-form {
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 20px;
        }
        .info-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-right: 20px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 60%;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover, .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><?php echo $t['title']; ?></h1>

        <!-- Language Selector -->
        <a href="index.php?lang=en">English</a> | <a href="index.php?lang=ro">Română</a>

        <!-- Info Button -->
        <button class="info-button" id="infoBtn"><?php echo $t['info']; ?></button>
    </div>

    <!-- Info Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><?php echo $t['info_title']; ?></h2>
            <p><?php echo $t['info_text']; ?></p>
        </div>
    </div>

    <?php if (isset($message) && $message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']): ?>

        <!-- Login Form -->
        <div class="login-form">
            <h2><?php echo $t['login']; ?></h2>
            <form method="POST">
                <input type="text" name="username" placeholder="<?php echo $t['username']; ?>" required><br><br>
                <input type="password" name="password" placeholder="<?php echo $t['password']; ?>" required><br><br>
                <button type="submit" name="login"><?php echo $t['submit']; ?></button>
            </form>
            <?php if (isset($loginError)): ?>
                <div class="error"><?php echo $loginError; ?></div>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <!-- Logout Link -->
        <a href="index.php?logout=true"><?php echo $t['logout']; ?></a>

        <!-- Add Note Form -->
        <div class="add-note-form">
            <h2><?php echo $t['add_note']; ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="student_name" placeholder="<?php echo $t['student_name']; ?>" required><br><br>
                <input type="text" name="subject" placeholder="<?php echo $t['subject']; ?>" required><br><br>
                <textarea name="note_content" placeholder="<?php echo $t['note']; ?>" required></textarea><br><br>
                <textarea name="teacher_remarks" placeholder="<?php echo $t['teacher_remarks']; ?>"></textarea><br><br>
                <textarea name="absence_details" placeholder="<?php echo $t['absence_details']; ?>"></textarea><br><br>
                <input type="text" name="date" class="flatpickr" placeholder="<?php echo $t['date']; ?>" required><br><br>
                <input type="file" name="file"><br><br>
                <button type="submit" name="add_note"><?php echo $t['submit']; ?></button>
            </form>
        </div>

        <!-- Display Notes -->
        <h2><?php echo $t['all_notes']; ?></h2>
        <?php if (count($notes) > 0): ?>
            <?php foreach ($notes as $note): ?>
                <?php
                list($noteID, $studentName, $subject, $noteContent, $teacherRemarks, $absenceDetails, $date, $filePath) = explode('|', $note);
                ?>
                <div class="note">
                    <p><strong><?php echo $t['student_name']; ?>:</strong> <?php echo $studentName; ?></p>
                    <p><strong><?php echo $t['subject']; ?>:</strong> <?php echo $subject; ?></p>
                    <p><strong><?php echo $t['note']; ?>:</strong> <?php echo $noteContent; ?></p>
                    <p><strong><?php echo $t['teacher_remarks']; ?>:</strong> <?php echo $teacherRemarks; ?></p>
                    <p><strong><?php echo $t['absence_details']; ?>:</strong> <?php echo $absenceDetails; ?></p>
                    <p><strong><?php echo $t['date']; ?>:</strong> <?php echo $date; ?></p>
                    <?php if ($filePath): ?>
                        <p><strong><?php echo $t['file_upload']; ?>:</strong> <a href="<?php echo $filePath; ?>" target="_blank">Download</a></p>
                    <?php endif; ?>
                    <a href="index.php?delete_id=<?php echo $noteID; ?>" class="delete-button"><?php echo $t['delete']; ?></a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?php echo $t['no_notes']; ?></p>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize date picker
    flatpickr(".flatpickr", {
        dateFormat: "Y-m-d"
    });

    // Modal functionality
    var modal = document.getElementById("myModal");
    var btn = document.getElementById("infoBtn");
    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>
