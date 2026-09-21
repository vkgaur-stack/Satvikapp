<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Beneficiaries</h2>
            <a href="/beneficiaries/create" class="btn btn-primary">+ Register New Beneficiary</a>
        </div>

        <?php if (empty($beneficiaries)): ?>
            <div class="alert alert-info">
                No beneficiaries found. <a href="/beneficiaries/create">Register the first beneficiary</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Email</th>
                            <th>DOB</th>
                            <th>ID Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($beneficiaries as $b): ?>
                            <tr>
                                <td><strong><?= esc($b['first_name'] . ' ' . $b['last_name']) ?></strong></td>
                                <td><?= esc($b['phone']) ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= ucfirst($b['gender']) ?>
                                    </span>
                                </td>
                                <td><?= esc($b['email'] ?? '-') ?></td>
                                <td><?= $b['date_of_birth'] ? date('d-M-Y', strtotime($b['date_of_birth'])) : '-' ?></td>
                                <td><?= esc($b['unique_id_type'] ?? '-') ?></td>
                                <td>
                                    <?php if ($b['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif ($b['status'] === 'inactive'): ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Registered</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/beneficiaries/<?= $b['id'] ?>" class="btn btn-sm btn-info">View</a>
                                    <a href="/beneficiaries/<?= $b['id'] ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                                    <form method="POST" action="/beneficiaries/<?= $b['id'] ?>/delete" style="display:inline;">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this beneficiary?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>