<?php

$formAction = $formAction ?? '';
$submitLabel = $submitLabel ?? 'Save student';
?>
<?php if (isset($errors['_form'])): ?>
    <div class="notice notice-error" role="alert"><?= e($errors['_form']) ?></div>
<?php endif; ?>

<form class="form-panel" method="post" action="<?= e($formAction) ?>" novalidate>
    <input type="hidden" name="_token" value="<?= e(csrfToken()) ?>">

    <div class="form-grid">
        <div class="form-field">
            <label for="first_name">First name</label>
            <input id="first_name" name="first_name" type="text" maxlength="60" required
                autocomplete="given-name" value="<?= e($student['first_name']) ?>"
                aria-invalid="<?= isset($errors['first_name']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['first_name'])): ?>
                <p class="field-error"><?= e($errors['first_name']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="last_name">Last name</label>
            <input id="last_name" name="last_name" type="text" maxlength="60" required
                autocomplete="family-name" value="<?= e($student['last_name']) ?>"
                aria-invalid="<?= isset($errors['last_name']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['last_name'])): ?>
                <p class="field-error"><?= e($errors['last_name']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required autocomplete="email"
                value="<?= e($student['email']) ?>"
                aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['email'])): ?>
                <p class="field-error"><?= e($errors['email']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="phone">Phone <span class="field-hint">(optional)</span></label>
            <input id="phone" name="phone" type="tel" autocomplete="tel"
                value="<?= e($student['phone']) ?>"
                aria-invalid="<?= isset($errors['phone']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['phone'])): ?>
                <p class="field-error"><?= e($errors['phone']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="course">Course</label>
            <select id="course" name="course" required
                aria-invalid="<?= isset($errors['course']) ? 'true' : 'false' ?>">
                <option value="">Select a course</option>
                <?php foreach (STUDENT_COURSES as $course): ?>
                    <option value="<?= e($course) ?>" <?= $student['course'] === $course ? 'selected' : '' ?>>
                        <?= e($course) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['course'])): ?>
                <p class="field-error"><?= e($errors['course']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-field">
            <label for="enrolled_on">Enrollment date</label>
            <input id="enrolled_on" name="enrolled_on" type="date" required
                value="<?= e($student['enrolled_on']) ?>"
                aria-invalid="<?= isset($errors['enrolled_on']) ? 'true' : 'false' ?>">
            <?php if (isset($errors['enrolled_on'])): ?>
                <p class="field-error"><?= e($errors['enrolled_on']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-actions">
        <button class="button" type="submit"><?= e($submitLabel) ?></button>
        <a class="button button-secondary" href="index.php">Cancel</a>
    </div>
</form>
