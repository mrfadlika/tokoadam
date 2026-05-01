<!-- User Management -->
<div class="toolbar">
    <h3 style="font-size:14px;color:var(--text-secondary)">👥 Daftar User</h3>
    <a href="<?= BASE_URL ?>/index.php?page=users&action=create" class="btn btn-primary">+ Tambah User</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Status</th><th>Dibuat</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($u['nama']) ?></td>
                    <td><span class="font-mono"><?= htmlspecialchars($u['username']) ?></span></td>
                    <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-info' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= $u['is_active'] ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Nonaktif</span>' ?></td>
                    <td class="text-muted"><?= formatDate($u['created_at']) ?></td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="<?= BASE_URL ?>/index.php?page=users&action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
                            <?php if ($u['id'] != currentUserId()): ?>
                                <a href="<?= BASE_URL ?>/index.php?page=users&action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline" data-confirm="Yakin nonaktifkan user ini?">🗑️</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
