<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Dashboard</h2>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-users text-primary" style="font-size: 2rem;"></i>
            <div class="stat-value"><?= number_format($stats['total_beneficiaries']) ?></div>
            <div class="stat-label">Total Beneficiaries</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
            <div class="stat-value"><?= number_format($stats['active_beneficiaries']) ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-heart text-danger" style="font-size: 2rem;"></i>
            <div class="stat-value"><?= number_format($stats['total_donors']) ?></div>
            <div class="stat-label">Total Donors</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <i class="fas fa-rupiah-sign text-warning" style="font-size: 2rem;"></i>
            <div class="stat-value">₹<?= number_format($stats['total_contributions'], 0) ?></div>
            <div class="stat-label">Total Contributions</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tasks"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/beneficiaries/create" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> Register New Beneficiary
                    </a>
                    <a href="/donors/create" class="btn btn-outline-primary">
                        <i class="fas fa-heart-circle-plus"></i> Add New Donor
                    </a>
                    <a href="/programs/create" class="btn btn-outline-primary">
                        <i class="fas fa-plus-circle"></i> Create Program
                    </a>
                    <a href="/reports" class="btn btn-outline-primary">
                        <i class="fas fa-file-chart-line"></i> View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> System Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Logged in as:</strong> <?= session('name') ?></p>
                <p><strong>Email:</strong> <?= session('email') ?></p>
                <p><strong>Role:</strong> <span class="badge bg-primary"><?= session('role') ?></span></p>
                <p><strong>Last login:</strong> Just now</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>