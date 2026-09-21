<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><?= esc($donor['name']) ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Donor Type:</strong> <span class="badge bg-secondary"><?= ucfirst($donor['type']) ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <?php if ($donor['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif ($donor['status'] === 'inactive'): ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Email:</strong><br><?= esc($donor['email'] ?? 'Not provided') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Phone:</strong><br><?= esc($donor['phone'] ?? 'Not provided') ?></p>
                    </div>
                </div>

                <div class="mb-3">
                    <p><strong>Address:</strong><br><?= esc($donor['address'] ?? 'Not provided') ?></p>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <p><strong>City:</strong> <?= esc($donor['city'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>State:</strong> <?= esc($donor['state'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Zip Code:</strong> <?= esc($donor['zip_code'] ?? '-') ?></p>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Total Contribution:</strong> ₹<?= number_format($donor['total_contribution'], 2) ?>
                </div>

                <div class="alert alert-secondary">
                    <small>
                        <strong>Created:</strong> <?= date('d-M-Y H:i', strtotime($donor['created_at'])) ?><br>
                        <strong>Updated:</strong> <?= date('d-M-Y H:i', strtotime($donor['updated_at'])) ?>
                    </small>
                </div>

                <div class="d-flex gap-2">
                    <a href="/donors/<?= $donor['id'] ?>/edit" class="btn btn-warning">Edit</a>
                    <a href="/donors" class="btn btn-secondary">Back to List</a>
                    <form method="POST" action="/donors/<?= $donor['id'] ?>/delete" style="display:inline;">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this donor?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>