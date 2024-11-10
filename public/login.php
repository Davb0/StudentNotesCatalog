<?php
session_start();
include_once "translations_$lang.php";  // Include translation file based on selected language

// Check for login/logout actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $validUsername = 'admin';
    $validPassword = 'password123';

    if ($username === $validUsername && $password === $validPassword) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
    } else {
        $loginError = $t['login_error'];
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';  // Default to English if no language is set
$message = '';

$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
        $studentName = htmlspecialchars($_POST['student_name']);
        $subject = htmlspecialchars($_POST['subject']);
        $noteContent = htmlspecialchars($_POST['note_content']);
        $teacherRemarks = htmlspecialchars($_POST['teacher_remarks']);
        $absenceDetails = htmlspecialchars($_POST['absence_details']);
        $date = htmlspecialchars($_POST['date']);
        $noteID = uniqid();

        $filePath = '';
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['file']['name']);
            $filePath = $uploadDir . $noteID . "_" . $fileName;
            move_uploaded_file($_FILES['file']['tmp_name'], $filePath);
        }

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

$notes = file_exists('notes.txt') ? file('notes.txt', FILE_IGNORE_NEW_LINES) : [];
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $t['title']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }
        .container { width: 90%; max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); }
        .note { border: 1px solid #e0e0e0; padding: 15px; margin: 10px 0; border-radius: 6px; background: #fefefe; }
        .delete-button { color: red; font-weight: bold; cursor: pointer; }
        .message { color: green; margin-bottom: 15px; }
        .login-form { max-width: 300px; margin: auto; padding: 20px; background: #f7f7f7; border: 1px solid #ddd; border-radius: 6px; }
    </style>
</head>
<body>
<div class="container">
    <h1><?php echo $t['title']; ?></h1>

    <a href="index.php?lang=en">English</a> | <a href="index.php?lang=ro">Română</a>

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

        <?php if ($message): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="index.php?lang=<?php echo $lang; ?>" method="POST" enctype="multipart/form-data">
            <input type="text" name="student_name" placeholder="<?php echo $t['student_name']; ?>" required>
            <input type="text" name="subject" placeholder="<?php echo $t['subject']; ?>" required>
            <textarea name="note_content" placeholder="<?php echo $t['note']; ?>" required></textarea>
            <textarea name="teacher_remarks" placeholder="<?php echo $t['teacher_remarks']; ?>"></textarea>
            <textarea name="absence_details" placeholder="<?php echo $t['absence_details']; ?>"></textarea>
            <label><?php echo $t['date']; ?>:</label>
            <input type="text" id="date" name="date" required>
            <label><?php echo $t['file_upload']; ?>:</label>
            <input type="file" name="file"><br>
            <button type="submit" name="add_note"><?php echo $t['add_note']; ?></button>
        </form>

        <h2><?php echo $t['all_notes']; ?></h2>
        <?php if (count($notes) > 0): ?>
            <?php foreach ($notes as $note): ?>
                <?php
                list($id, $studentName, $subject, $noteContent, $teacherRemarks, $absenceDetails, $date, $filePath) = explode('|', $note);
                ?>
                <div class="note">
                    <p><strong><?php echo $t['student_name']; ?>:</strong> <?php echo $studentName; ?></p>
                    <p><strong><?php echo $t['subject']; ?>:</strong> <?php echo $subject; ?></p>
                    <p><strong><?php echo $t['note']; ?>:</strong> <?php echo $noteContent; ?></p>
                    <p><strong><?php echo $t['teacher_remarks']; ?>:</strong> <?php echo $teacherRemarks; ?></p>
                    <p><strong><?php echo $t['absence_details']; ?>:</strong> <?php echo $absenceDetails; ?></p>
                    <p><strong><?php echo $t['date']; ?>:</strong> <?php echo $date; ?></p>
                    <?php if ($filePath): ?>
                        <p><strong>File:</strong> <a href="<?php echo $filePath; ?>" target="_blank">View File</a></p>
                    <?php endif; ?>
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
    flatpickr("#date", { enableTime: false, dateFormat: "Y-m-d" });
</script>
</body>
</html>
