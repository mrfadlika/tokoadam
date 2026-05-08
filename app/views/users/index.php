<!-- User Management -->
<div class="toolbar">
    <h3 style="font-size:14px;color:var(--text-secondary)">User Management</h3>
    <a href="<?= BASE_URL ?>/index.php?page=users&action=create" class="btn btn-primary">+ Tambah User</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table-compact-mobile">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th class="mobile-hide-col">Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="mobile-hide-col">Dibuat</th>
                    <th class="text-center mobile-hide-col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="fw-bold">
                        <?= htmlspecialchars($u['nama']) ?>
                        <div class="mobile-only-inline"><span class="font-mono"><?= htmlspecialchars($u['username']) ?></span></div>
                    </td>
                    <td class="mobile-hide-col"><span class="font-mono"><?= htmlspecialchars($u['username']) ?></span></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-info' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= $u['is_active'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>' ?></td>
                    <td class="text-muted mobile-hide-col"><?= formatDate($u['created_at']) ?></td>
                    <td class="text-center mobile-hide-col">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>/index.php?page=users&action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                            <?php if ($u['id'] != currentUserId()): ?>
                                <a href="<?= BASE_URL ?>/index.php?page=users&action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline" data-confirm="Yakin nonaktifkan user ini?">Hapus</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
