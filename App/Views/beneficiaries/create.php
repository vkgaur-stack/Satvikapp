<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Register New Beneficiary</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/beneficiaries/store">
                    <?= csrf_field() ?>

                    <h6 class="mb-3 text-secondary">Personal Information</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="<?= old('first_name') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="<?= old('last_name') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?= old('phone') ?>" placeholder="10 digit mobile number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= old('email') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender *</label>
                            <select name="gender" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= old('gender') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" 
                                   value="<?= old('date_of_birth') ?>">
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3 text-secondary">Address & Identification</h6>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Full residential address"><?= old('address') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identification Type</label>
                            <select name="unique_id_type" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="aadhaar" <?= old('unique_id_type') === 'aadhaar' ? 'selected' : '' ?>>Aadhaar</option>
                                <option value="pan" <?= old('unique_id_type') === 'pan' ? 'selected' : '' ?>>PAN</option>
                                <option value="voter_id" <?= old('unique_id_type') === 'voter_id' ? 'selected' : '' ?>>Voter ID</option>
                                <option value="other" <?= old('unique_id_type') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identification Number</label>
                            <input type="text" name="unique_id" class="form-control" 
                                   value="<?= old('unique_id') ?>" placeholder="e.g. Aadhaar number">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" name="latitude" class="form-control" step="0.00000001"
                                   value="<?= old('latitude') ?>" placeholder="GPS coordinates">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" name="longitude" class="form-control" step="0.00000001"
                                   value="<?= old('longitude') ?>" placeholder="GPS coordinates">
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3 text-secondary">Status</h6>

                    <div class="mb-3">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="registered" <?= old('status') === 'registered' ? 'selected' : '' ?>>Registered</option>
                            <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Register Beneficiary</button>
                        <a href="/beneficiaries" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>