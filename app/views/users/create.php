<!-- Create/Edit User -->
<?php $isEdit = isset($user); ?>
<div class="card" style="max-width:500px">
    <div class="card-header"><h3><?= $isEdit ? '✏️ Edit User' : '👤 Tambah User Baru' ?></h3></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=users&action=<?= $isEdit ? 'update&id=' . $user['id'] : 'store' ?>">
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($isEdit ? $user['nama'] : '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Username <span class="required">*</span></label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($isEdit ? $user['username'] : '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password <?= $isEdit ? '' : '<span class="required">*</span>' ?></label>
                <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
                <?php if ($isEdit): ?><div class="form-hint">Kosongkan jika tidak ingin mengubah password</div><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="form-label">Role <span class="required">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="staff" <?= ($isEdit && $user['role'] === 'staff') ? 'selected' : '' ?>>Staff</option>
                    <option value="admin" <?= ($isEdit && $user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="d-flex gap-2" style="margin-top:24px">
                <button type="submit" class="btn btn-primary">💾 <?= $isEdit ? 'Update' : 'Simpan' ?></button>
                <a href="<?= BASE_URL ?>/index.php?page=users" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
