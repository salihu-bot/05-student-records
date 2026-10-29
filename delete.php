<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method Not Allowed');
}

$studentId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($studentId === false || $studentId === null || $studentId < 1) {
    http_response_code(400);
    exit('Invalid student ID.');
}

if (!hasValidCsrfToken($_POST['_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid or expired form token.');
}

try {
    $statement = db()->prepare('DELETE FROM students WHERE id = :id');
    $statement->execute(['id' => $studentId]);

    if ($statement->rowCount() === 0) {
        http_response_code(404);
        $pageTitle = 'Student not found';
        require __DIR__ . '/partials/header.php';
        ?>
        <section class="error-panel" role="alert">
            <h1>Student not found</h1>
            <p>The requested student record could not be found. <a href="index.php">Return to the student list</a>.</p>
        </section>
        <?php
        require __DIR__ . '/partials/footer.php';
        exit;
    }

    flash('success', 'Student deleted');
    redirect('index.php');
} catch (PDOException $exception) {
    http_response_code(503);
    $pageTitle = 'Student records unavailable';
    require __DIR__ . '/partials/header.php';
    ?>
    <section class="error-panel" role="alert">
        <h1>Student records are unavailable</h1>
        <p>We couldn't update the student records. Please check that MySQL is running, then try again.</p>
    </section>
    <?php
    require __DIR__ . '/partials/footer.php';
}
