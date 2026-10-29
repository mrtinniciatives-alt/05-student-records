<?php
$student = $student ?? [];
$errors = $errors ?? [];
$courses = $courses ?? [];
$action = $action ?? '';
$submitLabel = $submitLabel ?? 'Save student';
$getValue = static function (string $field, string $default = '') use ($student): string {
    return $_SERVER['REQUEST_METHOD'] === 'POST' ? trim((string) ($_POST[$field] ?? '')) : (string) ($student[$field] ?? $default);
};
?>
<form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="form-grid">
        <div class="field">
            <label for="first_name">First name *</label>
            <input id="first_name" name="first_name" required maxlength="60" value="<?= e($getValue('first_name')) ?>">
            <?php if (isset($errors['first_name'])): ?><small class="field-error"><?= e($errors['first_name']) ?></small><?php endif; ?>
        </div>
        <div class="field">
            <label for="last_name">Last name *</label>
            <input id="last_name" name="last_name" required maxlength="60" value="<?= e($getValue('last_name')) ?>">
            <?php if (isset($errors['last_name'])): ?><small class="field-error"><?= e($errors['last_name']) ?></small><?php endif; ?>
        </div>
        <div class="field">
            <label for="email">Email *</label>
            <input id="email" name="email" type="email" required value="<?= e($getValue('email')) ?>">
            <?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>
        </div>
        <div class="field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" inputmode="numeric" pattern="0[789][01][0-9]{8}" value="<?= e($getValue('phone')) ?>">
            <?php if (isset($errors['phone'])): ?><small class="field-error"><?= e($errors['phone']) ?></small><?php endif; ?>
        </div>
        <div class="field">
            <label for="course">Course *</label>
            <select id="course" name="course" required>
                <option value="">Select a course</option>
                <?php foreach ($courses as $course): ?>
                    <?php $selectedCourse = $getValue('course'); ?>
                    <option value="<?= e($course) ?>" <?= $selectedCourse === $course ? 'selected' : '' ?>><?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['course'])): ?><small class="field-error"><?= e($errors['course']) ?></small><?php endif; ?>
        </div>
        <div class="field">
            <label for="enrolled_on">Enrolled date *</label>
            <input id="enrolled_on" name="enrolled_on" type="date" required max="<?= date('Y-m-d') ?>" value="<?= e($getValue('enrolled_on', date('Y-m-d'))) ?>">
            <?php if (isset($errors['enrolled_on'])): ?><small class="field-error"><?= e($errors['enrolled_on']) ?></small><?php endif; ?>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn" type="submit"><?= e($submitLabel) ?></button>
        <a class="btn btn-secondary" href="/swd/05-student-records/index.php">Cancel</a>
    </div>
</form>
