<div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Daftar RPP</h2>

    <div class="glass-effect rounded-lg p-4">
        <?php if (empty($data['list_rpp'])): ?>
            <div class="p-6 text-center">Belum ada RPP untuk semester ini.</div>
        <?php else: ?>
            <table class="w-full table-auto text-sm">
                <thead>
                    <tr class="text-left">
                        <th class="p-3">Mapel</th>
                        <th class="p-3">Kelas</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Terakhir Diubah</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['list_rpp'] as $r): ?>
                        <tr>
                            <td class="p-3"><?= htmlspecialchars($r['nama_mapel']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($r['nama_kelas']); ?></td>
                            <td class="p-3"><?= htmlspecialchars(ucfirst($r['status'])); ?></td>
                            <td class="p-3"><?= htmlspecialchars($r['updated_at']); ?></td>
                            <td class="p-3">
                                <a href="<?= BASEURL; ?>/guru/detailRPP/<?= htmlspecialchars($r['id_rpp']); ?>" class="btn-primary btn-sm">Detail</a>
                                <?php if ($r['id_guru'] == ($_SESSION['id_ref'] ?? null)): ?>
                                    <a href="<?= BASEURL; ?>/guru/editRPP/<?= htmlspecialchars($r['id_rpp']); ?>" class="btn-secondary btn-sm">Edit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>