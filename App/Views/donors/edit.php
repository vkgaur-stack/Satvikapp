<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Edit Donor</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/donors/<?= $donor['id'] ?>/update">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Donor Name *</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= old('name') ?? $donor['name'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="individual" <?= (old('type') ?? $donor['type']) === 'individual' ? 'selected' : '' ?>>Individual</option>
                            <option value="organization" <?= (old('type') ?? $donor['type']) === 'organization' ? 'selected' : '' ?>>Organization</option>
                            <option value="corporate" <?= (old('type') ?? $donor['type']) === 'corporate' ? 'selected' : '' ?>>Corporate</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= old('email') ?? $donor['email'] ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?= old('phone') ?? $donor['phone'] ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= old('address') ?? $donor['address'] ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" 
                                   value="<?= old('city') ?? $donor['city'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" 
                                   value="<?= old('state') ?? $donor['state'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Zip Code</label>
                            <input type="text" name="zip_code" class="form-control" 
                                   value="<?= old('zip_code') ?? $donor['zip_code'] ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Contribution (₹)</label>
                        <input type="number" name="total_contribution" class="form-control" 
                               step="0.01" value="<?= old('total_contribution') ?? $donor['total_contribution'] ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= (old('status') ?? $donor['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (old('status') ?? $donor['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            <option value="pending" <?= (old('status') ?? $donor['status']) === 'pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Donor</button>
                        <a href="/donors" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>