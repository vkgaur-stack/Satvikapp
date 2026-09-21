<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><?= esc($beneficiary['first_name'] . ' ' . $beneficiary['last_name']) ?></h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Gender:</strong> <span class="badge bg-secondary"><?= ucfirst($beneficiary['gender']) ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <?php if ($beneficiary['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif ($beneficiary['status'] === 'inactive'): ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Registered</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3 text-secondary">Contact Information</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Phone:</strong><br><?= esc($beneficiary['phone']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Email:</strong><br><?= esc($beneficiary['email'] ?? 'Not provided') ?></p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3 text-secondary">Personal Details</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Date of Birth:</strong><br>
                            <?= $beneficiary['date_of_birth'] ? date('d-M-Y', strtotime($beneficiary['date_of_birth'])) : 'Not provided' ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Age:</strong><br>
                            <?php 
                            if ($beneficiary['date_of_birth']) {
                                $age = date_diff(date_create($beneficiary['date_of_birth']), date_create('today'))->y;
                                echo $age . ' years';
                            } else {
                                echo 'Not calculated';
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <div class="mb-3">
                    <p><strong>Address:</strong><br><?= esc($beneficiary['address'] ?? 'Not provided') ?></p>
                </div>

                <hr>

                <h6 class="mb-3 text-secondary">Identification</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>ID Type:</strong> <?= esc($beneficiary['unique_id_type'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>ID Number:</strong> <?= esc($beneficiary['unique_id'] ?? '-') ?></p>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3 text-secondary">Location (GPS)</h6>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Latitude:</strong> <?= $beneficiary['latitude'] ?? '-' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Longitude:</strong> <?= $beneficiary['longitude'] ?? '-' ?></p>
                    </div>
                </div>

                <div class="alert alert-secondary">
                    <small>
                        <strong>Registered:</strong> <?= date('d-M-Y H:i', strtotime($beneficiary['created_at'])) ?><br>
                        <strong>Updated:</strong> <?= date('d-M-Y H:i', strtotime($beneficiary['updated_at'])) ?>
                    </small>
                </div>

                <div class="d-flex gap-2">
                    <a href="/beneficiaries/<?= $beneficiary['id'] ?>/edit" class="btn btn-warning">Edit</a>
                    <a href="/beneficiaries" class="btn btn-secondary">Back to List</a>
                    <form method="POST" action="/beneficiaries/<?= $beneficiary['id'] ?>/delete" style="display:inline;">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this beneficiary?')">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>