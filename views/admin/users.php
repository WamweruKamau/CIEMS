<?php
$pageTitle = 'User Access & Roles';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-users-gear text-primary me-2"></i> User Access & Role Administration
        </h4>
        <small class="text-muted">Manage system analysts, evidence custodians, and security privileges</small>
    </div>
    <div>
        <button type="button" class="btn btn-primary px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="fa-solid fa-user-plus me-1"></i> Add System User
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th class="ps-3">Badge / Staff ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Assigned Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-3 font-monospace fw-bold text-dark">
                                <?= htmlspecialchars($u['badge_number']) ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($u['full_name']) ?></div>
                            </td>
                            <td class="font-monospace small">
                                <?= htmlspecialchars($u['email']) ?>
                            </td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-danger text-uppercase px-2 py-1">Admin</span>
                                <?php elseif ($u['role'] === 'investigator'): ?>
                                    <span class="badge bg-primary text-uppercase px-2 py-1">Investigator</span>
                                <?php else: ?>
                                    <span class="badge bg-success text-uppercase px-2 py-1">Custodian</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted font-monospace">
                                <?= $u['last_login'] ? date('Y-m-d H:i', strtotime($u['last_login'])) : 'Never' ?>
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm edit-user-btn"
                                    data-id="<?= $u['id'] ?>"
                                    data-name="<?= htmlspecialchars($u['full_name']) ?>"
                                    data-badge="<?= htmlspecialchars($u['badge_number']) ?>"
                                    data-email="<?= htmlspecialchars($u['email']) ?>"
                                    data-role="<?= $u['role'] ?>"
                                    data-status="<?= $u['status'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#editUserModal">
                                    <i class="fa-solid fa-user-pen"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create User -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title" id="createUserModalLabel">
                    <i class="fa-solid fa-user-plus me-2"></i> Register New System Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/index.php?action=admin-users" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Badge / Staff ID Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="badge_number" required placeholder="e.g. INV-669925">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Legal Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" required placeholder="e.g. Jane Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Official Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required placeholder="jdoe@ciems.usiu.ac.ke">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Temporary Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required placeholder="••••••••••••">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">System Role</label>
                            <select name="role" class="form-select">
                                <option value="investigator">Lead Investigator</option>
                                <option value="custodian">Evidence Custodian</option>
                                <option value="admin">System Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Account Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="fa-solid fa-user-pen me-2"></i> Update User Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/index.php?action=admin-users" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="user_id" id="edit_user_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Badge / Staff ID Number</label>
                        <input type="text" class="form-control" name="badge_number" id="edit_badge" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Legal Name</label>
                        <input type="text" class="form-control" name="full_name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address (Immutable)</label>
                        <input type="email" class="form-control bg-light" id="edit_email" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Reset Password (Leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password" placeholder="••••••••••••">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">System Role</label>
                            <select name="role" id="edit_role" class="form-select">
                                <option value="investigator">Lead Investigator</option>
                                <option value="custodian">Evidence Custodian</option>
                                <option value="admin">System Administrator</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Account Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.edit-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_user_id').value = this.getAttribute('data-id');
            document.getElementById('edit_name').value = this.getAttribute('data-name');
            document.getElementById('edit_badge').value = this.getAttribute('data-badge');
            document.getElementById('edit_email').value = this.getAttribute('data-email');
            document.getElementById('edit_role').value = this.getAttribute('data-role');
            document.getElementById('edit_status').value = this.getAttribute('data-status');
        });
    });
</script>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
