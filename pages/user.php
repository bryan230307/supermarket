<?php
session_start();
require_once '../classes/Auth.php';
require_once '../classes/User.php';

Auth::requireAdmin();

$userModel = new User();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        if ($userModel->usernameExists($_POST['username'])) {
            $message = 'Username sudah digunakan!'; $messageType = 'danger';
        } elseif ($userModel->create($_POST['nama'], $_POST['username'], $_POST['password'], $_POST['role'])) {
            $message = 'User berhasil ditambahkan!'; $messageType = 'success';
        } else { $message = 'Gagal menambahkan user.'; $messageType = 'danger'; }
    } elseif ($action === 'update') {
        if ($userModel->usernameExists($_POST['username'], $_POST['id'])) {
            $message = 'Username sudah digunakan!'; $messageType = 'danger';
        } else {
            $pw = !empty($_POST['password']) ? $_POST['password'] : null;
            if ($userModel->update($_POST['id'], $_POST['nama'], $_POST['username'], $_POST['role'], $pw)) {
                $message = 'User berhasil diupdate!'; $messageType = 'success';
            } else { $message = 'Gagal mengupdate user.'; $messageType = 'danger'; }
        }
    } elseif ($action === 'delete') {
        if ($_POST['id'] == $_SESSION['user_id']) {
            $message = 'Tidak bisa menghapus akun sendiri!'; $messageType = 'danger';
        } elseif ($userModel->delete($_POST['id'])) {
            $message = 'User berhasil dihapus!'; $messageType = 'success';
        } else { $message = 'Gagal menghapus user.'; $messageType = 'danger'; }
    }
}

$search = $_GET['search'] ?? '';
$data = $search ? $userModel->search($search) : $userModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users – Supermarket</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
  <div class="topbar">
    <h2>👥 Manajemen User</h2>
  </div>
  <div class="page-body">

    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>">
      <?= $messageType==='success' ? '✅' : '❌' ?> <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Daftar User</span>
        <div style="display:flex;gap:8px;">
          <form method="GET" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Cari nama / username..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-secondary">🔍</button>
            <?php if ($search): ?><a href="user.php" class="btn btn-secondary">✕</a><?php endif; ?>
          </form>
          <button class="btn btn-primary" onclick="openModal('modalTambah')">➕ Tambah User</button>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Role</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr><td colspan="5" style="text-align:center;padding:30px;color:#64748b;">Tidak ada data</td></tr>
          <?php else: ?>
            <?php foreach ($data as $i => $u): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= htmlspecialchars($u['nama']) ?></td>
              <td><?= htmlspecialchars($u['username']) ?></td>
              <td>
                <?php if ($u['role']==='admin'): ?>
                  <span class="badge badge-warning">👑 Admin</span>
                <?php else: ?>
                  <span class="badge badge-secondary">👤 Petugas</span>
                <?php endif; ?>
              </td>
              <td>
                <button class="btn btn-sm btn-warning"
                  onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)">✏️ Edit</button>
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <button class="btn btn-sm btn-danger"
                  onclick="delUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['nama'], ENT_QUOTES) ?>')">🗑️</button>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">➕ Tambah User</span>
      <button class="modal-close" onclick="closeModal('modalTambah')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Username *</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="form-group">
          <label class="form-label">Role *</label>
          <select name="role" class="form-control" required>
            <option value="petugas">Petugas</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✏️ Edit User</span>
      <button class="modal-close" onclick="closeModal('modalEdit')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_id">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nama Lengkap *</label>
          <input type="text" name="nama" id="edit_nama" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Username *</label>
          <input type="text" name="username" id="edit_username" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password Baru <small style="color:#94a3b8;">(kosongkan jika tidak diubah)</small></label>
          <input type="password" name="password" class="form-control" minlength="6">
        </div>
        <div class="form-group">
          <label class="form-label">Role *</label>
          <select name="role" id="edit_role" class="form-control" required>
            <option value="petugas">Petugas</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-warning">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Delete -->
<div class="modal-overlay" id="modalDelete">
  <div class="modal" style="width:360px;">
    <div class="modal-header">
      <span class="modal-title">🗑️ Hapus User</span>
      <button class="modal-close" onclick="closeModal('modalDelete')">×</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" id="del_id">
      <div class="modal-body">
        <p>Yakin hapus user <strong id="del_nama"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modalDelete')">Batal</button>
        <button type="submit" class="btn btn-danger">Hapus</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
function editUser(u) {
  document.getElementById('edit_id').value = u.id;
  document.getElementById('edit_nama').value = u.nama;
  document.getElementById('edit_username').value = u.username;
  document.getElementById('edit_role').value = u.role;
  openModal('modalEdit');
}
function delUser(id, nama) {
  document.getElementById('del_id').value = id;
  document.getElementById('del_nama').textContent = nama;
  openModal('modalDelete');
}
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) el.classList.remove('show'); });
});
</script>
</body>
</html>
