<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Donors</h2>
            <a href="/donors/create" class="btn btn-primary">+ Add New Donor</a>
        </div>

        <?php if (empty($donors)): ?>
            <div class="alert alert-info">
                No donors found. <a href="/donors/create">Add the first donor</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Total Contribution</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donors as $donor): ?>
                            <tr>
                                <td><strong><?= esc($donor['name']) ?></strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= ucfirst($donor['type']) ?>
                                    </span>
                                </td>
                                <td><?= esc($donor['email'] ?? '-') ?></td>
                                <td><?= esc($donor['phone'] ?? '-') ?></td>
                                <td><?= esc($donor['city'] ?? '-') ?></td>
                                <td><strong>₹<?= number_format($donor['total_contribution'], 2) ?></strong></td>
                                <td>
                                    <?php if ($donor['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif ($donor['status'] === 'inactive'): ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/donors/<?= $donor['id'] ?>" class="btn btn-sm btn-info">View</a>
                                    <a href="/donors/<?= $donor['id'] ?>/edit" class="btn btn-sm btn-warning">Edit</a>
                                    <form method="POST" action="/donors/<?= $donor['id'] ?>/delete" style="display:inline;">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this donor?')">Delete</button>
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