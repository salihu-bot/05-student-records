<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Student details';
$studentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$student = false;
$databaseError = false;

if ($studentId === false || $studentId === null || $studentId < 1) {
    http_response_code(404);
} else {
    try {
        $statement = db()->prepare(
            'SELECT id, first_name, last_name, email, phone, course, enrolled_on, created_at, updated_at
             FROM students
             WHERE id = :id'
        );
        $statement->execute(['id' => $studentId]);
        $student = $statement->fetch();

        if ($student === false) {
            http_response_code(404);
        }
    } catch (PDOException $exception) {
        http_response_code(503);
        $databaseError = true;
    }
}

require __DIR__ . '/partials/header.php';
?>

<?php if ($databaseError): ?>
    <section class="error-panel" role="alert">
        <h1>Student records are unavailable</h1>
        <p>We couldn't connect to the student records database. Please check that MySQL is running, then try again.</p>
    </section>
<?php elseif ($student === false): ?>
    <section class="error-panel" role="alert">
        <h1>Student not found</h1>
        <p>The requested student record could not be found. <a href="index.php">Return to the student list</a>.</p>
    </section>
<?php else: ?>
    <section class="page-heading">
        <div>
            <h1><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h1>
            <p class="page-description">Student record details.</p>
        </div>
        <a class="button" href="edit.php?id=<?= e($student['id']) ?>">Edit student</a>
    </section>

    <dl class="detail-list">
        <div class="detail-item">
            <dt>Email</dt>
            <dd><a href="mailto:<?= e($student['email']) ?>"><?= e($student['email']) ?></a></dd>
        </div>
        <div class="detail-item">
            <dt>Phone</dt>
            <dd><?= e($student['phone'] ?: 'Not provided') ?></dd>
        </div>
        <div class="detail-item">
            <dt>Course</dt>
            <dd><span class="course-label"><?= e($student['course']) ?></span></dd>
        </div>
        <div class="detail-item">
            <dt>Enrolled</dt>
            <dd><?= e(date('d M Y', strtotime($student['enrolled_on']))) ?></dd>
        </div>
        <div class="detail-item">
            <dt>Record created</dt>
            <dd><?= e($student['created_at']) ?></dd>
        </div>
        <div class="detail-item">
            <dt>Last updated</dt>
            <dd><?= e($student['updated_at']) ?></dd>
        </div>
    </dl>

    <p class="detail-back"><a href="index.php">← Back to students</a></p>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
