<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/student-validation.php';

$pageTitle = 'Students';
$databaseError = false;
$students = [];
$totalStudents = 0;
$perPage = 10;
$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$selectedCourse = isset($_GET['course']) && is_string($_GET['course']) ? $_GET['course'] : '';
$filterError = $selectedCourse !== '' && !in_array($selectedCourse, STUDENT_COURSES, true);
if ($filterError) {
    $selectedCourse = '';
}

$requestedPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$currentPage = is_int($requestedPage) && $requestedPage > 0 ? $requestedPage : 1;
$totalPages = 1;
$firstStudent = 0;
$lastStudent = 0;

try {
    $pdo = db();
    $conditions = [];
    $parameters = [];

    if ($search !== '') {
        $conditions[] = '(first_name LIKE :first_name OR last_name LIKE :last_name OR email LIKE :email)';
        $searchPattern = '%' . $search . '%';
        $parameters['first_name'] = $searchPattern;
        $parameters['last_name'] = $searchPattern;
        $parameters['email'] = $searchPattern;
    }

    if ($selectedCourse !== '') {
        $conditions[] = 'course = :course';
        $parameters['course'] = $selectedCourse;
    }

    $whereClause = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
    $countStatement = $pdo->prepare('SELECT COUNT(*) FROM students' . $whereClause);
    $countStatement->execute($parameters);
    $totalStudents = (int) $countStatement->fetchColumn();
    $totalPages = max(1, (int) ceil($totalStudents / $perPage));
    $currentPage = min($currentPage, $totalPages);
    $offset = ($currentPage - 1) * $perPage;

    $statement = $pdo->prepare(
        'SELECT id, first_name, last_name, email, course, enrolled_on
         FROM students'
         . $whereClause .
        ' ORDER BY last_name ASC, first_name ASC, id ASC
         LIMIT :limit OFFSET :offset'
    );

    foreach ($parameters as $name => $value) {
        $statement->bindValue(':' . $name, $value, PDO::PARAM_STR);
    }
    $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $students = $statement->fetchAll();

    if ($totalStudents > 0) {
        $firstStudent = $offset + 1;
        $lastStudent = min($offset + $perPage, $totalStudents);
    }
} catch (PDOException $exception) {
    http_response_code(503);
    $databaseError = true;
}

$paginationUrl = static function (int $page) use ($search, $selectedCourse): string {
    $parameters = ['page' => $page];

    if ($search !== '') {
        $parameters['q'] = $search;
    }
    if ($selectedCourse !== '') {
        $parameters['course'] = $selectedCourse;
    }

    return 'index.php?' . http_build_query($parameters);
};

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

    <?php if ($filterError): ?>
        <div class="notice notice-error" role="alert">That course filter is not valid. Showing all courses instead.</div>
    <?php endif; ?>

    <form class="filter-form" method="get" action="index.php">
        <div class="filter-field">
            <label for="q">Search students</label>
            <input id="q" name="q" type="search" value="<?= e($search) ?>"
                placeholder="Name or email">
        </div>
        <div class="filter-field">
            <label for="course">Course</label>
            <select id="course" name="course">
                <option value="">All courses</option>
                <?php foreach (STUDENT_COURSES as $course): ?>
                    <option value="<?= e($course) ?>" <?= $selectedCourse === $course ? 'selected' : '' ?>>
                        <?= e($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="button" type="submit">Search</button>
        <?php if ($search !== '' || $selectedCourse !== ''): ?>
            <a class="button button-secondary" href="index.php">Clear filters</a>
        <?php endif; ?>
    </form>

    <div class="results-summary" aria-live="polite">
        <?php if ($totalStudents > 0): ?>
            Showing <?= e($firstStudent) ?>–<?= e($lastStudent) ?> of <?= e($totalStudents) ?> students
        <?php else: ?>
            Showing 0 students
        <?php endif; ?>
    </div>

    <section class="panel" aria-label="Student list">
        <?php if ($students === []): ?>
            <div class="empty-state">
                <strong><?= $search !== '' || $selectedCourse !== '' ? 'No matching students' : 'No students yet' ?></strong>
                <?= $search !== '' || $selectedCourse !== ''
                    ? 'Try changing or clearing your filters.'
                    : 'Add your first student to get started.' ?>
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

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Student list pages">
                    <?php if ($currentPage > 1): ?>
                        <a class="page-link" href="<?= e($paginationUrl($currentPage - 1)) ?>">Previous</a>
                    <?php else: ?>
                        <span class="page-link page-link-disabled" aria-disabled="true">Previous</span>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                        <?php if ($page === $currentPage): ?>
                            <span class="page-link page-link-current" aria-current="page"><?= e($page) ?></span>
                        <?php else: ?>
                            <a class="page-link" href="<?= e($paginationUrl($page)) ?>"><?= e($page) ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a class="page-link" href="<?= e($paginationUrl($currentPage + 1)) ?>">Next</a>
                    <?php else: ?>
                        <span class="page-link page-link-disabled" aria-disabled="true">Next</span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
