<?= $this->extend('layout') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Edit Beneficiary</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/beneficiaries/<?= $beneficiary['id'] ?>/update">
                    <?= csrf_field() ?>

                    <h6 class="mb-3 text-secondary">Personal Information</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" 
                                   value="<?= old('first_name') ?? $beneficiary['first_name'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" 
                                   value="<?= old('last_name') ?? $beneficiary['last_name'] ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?= $beneficiary['phone'] ?>" disabled>
                            <small class="text-muted">Phone cannot be changed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= old('email') ?? $beneficiary['email'] ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select" required>
                                <option value="male" <?= (old('gender') ?? $beneficiary['gender']) === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= (old('gender') ?? $beneficiary['gender']) === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= (old('gender') ?? $beneficiary['gender']) === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" 
                                   value="<?= old('date_of_birth') ?? $beneficiary['date_of_birth'] ?>">
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3 text-secondary">Address & Identification</h6>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= old('address') ?? $beneficiary['address'] ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identification Type</label>
                            <select name="unique_id_type" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="aadhaar" <?= (old('unique_id_type') ?? $beneficiary['unique_id_type']) === 'aadhaar' ? 'selected' : '' ?>>Aadhaar</option>
                                <option value="pan" <?= (old('unique_id_type') ?? $beneficiary['unique_id_type']) === 'pan' ? 'selected' : '' ?>>PAN</option>
                                <option value="voter_id" <?= (old('unique_id_type') ?? $beneficiary['unique_id_type']) === 'voter_id' ? 'selected' : '' ?>>Voter ID</option>
                                <option value="other" <?= (old('unique_id_type') ?? $beneficiary['unique_id_type']) === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identification Number</label>
                            <input type="text" name="unique_id" class="form-control" 
                                   value="<?= old('unique_id') ?? $beneficiary['unique_id'] ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" name="latitude" class="form-control" step="0.00000001"
                                   value="<?= old('latitude') ?? $beneficiary['latitude'] ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" name="longitude" class="form-control" step="0.00000001"
                                   value="<?= old('longitude') ?? $beneficiary['longitude'] ?>">
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3 text-secondary">Status</h6>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="registered" <?= (old('status') ?? $beneficiary['status']) === 'registered' ? 'selected' : '' ?>>Registered</option>
                            <option value="active" <?= (old('status') ?? $beneficiary['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (old('status') ?? $beneficiary['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Beneficiary</button>
                        <a href="/beneficiaries" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>