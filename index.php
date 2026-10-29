<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Students';
$databaseError = false;
$students = [];

try {
    $statement = db()->query(
        'SELECT id, first_name, last_name, email, course, enrolled_on
         FROM students
         ORDER BY last_name ASC, first_name ASC'
    );
    $students = $statement->fetchAll();
} catch (PDOException $exception) {
    http_response_code(503);
    $databaseError = true;
}

require __DIR__ . '/partials/header.php';
?>

<?php if ($databaseError): ?>
    <section class="error-panel" role="alert">
        <h1>Student records are unavailable</h1>
        <p>We couldn't connect to the student records database. Please check that MySQL is running, then try again.</p>
    </section>
<?php else: ?>
    <section class="page-heading">
        <div>
            <h1>Students</h1>
            <p class="page-description">View and manage enrolled students.</p>
        </div>
        <a class="button" href="create.php">Add student</a>
    </section>

    <section class="panel" aria-label="Student list">
        <?php if ($students === []): ?>
            <div class="empty-state">
                <strong>No students yet</strong>
                Add your first student to get started.
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Course</th>
                            <th scope="col">Enrolled</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="student-name">
                                    <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
                                </td>
                                <td><a href="mailto:<?= e($student['email']) ?>"><?= e($student['email']) ?></a></td>
                                <td><span class="course-label"><?= e($student['course']) ?></span></td>
                                <td><?= e(date('d M Y', strtotime($student['enrolled_on']))) ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="show.php?id=<?= e($student['id']) ?>">View</a>
                                        <a href="edit.php?id=<?= e($student['id']) ?>">Edit</a>
                                        <form class="inline-form" method="post" action="delete.php"
                                            data-confirm="<?= e('Delete ' . $student['first_name'] . ' ' . $student['last_name'] . '?') ?>"
                                            onsubmit="return confirm(this.dataset.confirm)">
                                            <input type="hidden" name="_token" value="<?= e(csrfToken()) ?>">
                                            <input type="hidden" name="id" value="<?= e($student['id']) ?>">
                                            <button class="action-delete" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
