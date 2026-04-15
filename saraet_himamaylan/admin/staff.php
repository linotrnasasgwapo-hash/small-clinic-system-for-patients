<?php
// admin/staff.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_staff'])) {
    $fn  = clean($db,$_POST['full_name']??'');
    $em  = clean($db,$_POST['email']??'');
    $pos = clean($db,$_POST['position']??'');
    $rl  = clean($db,$_POST['role']??'staff');
    $pw  = password_hash($_POST['password']??'Staff@1234', PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO admins (full_name,email,password,role,position) VALUES(?,?,?,?,?)")->bind_param('sssss',$fn,$em,$pw,$rl,$pos) && true;
    $stmt=$db->prepare("INSERT INTO admins (full_name,email,password,role,position) VALUES(?,?,?,?,?)");
    $stmt->bind_param('sssss',$fn,$em,$pw,$rl,$pos);
    $stmt->execute() ? $msg='Staff account created.' : $msg='Error: Email may already exist.';
}
if (isset($_GET['toggle'])) {
    $tid=(int)$_GET['toggle'];
    $db->query("UPDATE admins SET is_active=NOT is_active WHERE admin_id=$tid AND admin_id!={$_SESSION['admin_id']}");
    header('Location: staff.php'); exit;
}
$staff = $db->query("SELECT * FROM admins ORDER BY role, full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Staff Accounts — Barangay Saraet Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-light:#e6f4ec;--gold:#c9933a;--bg:#f2f6f4;--white:#fff;--text:#1a2e23;--gray:#5a6a62;--border:#dce8e2;--amber:#d97706;--amber-bg:#fef3c7;--red:#dc2626;--blue:#1d4ed8;--blue-bg:#eff6ff;--sidebar:250px;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}
.sidebar{width:var(--sidebar);background:var(--green2);min-height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:200;}
.sb-brand{padding:1.5rem 1.25rem 1.1rem;border-bottom:1px solid rgba(255,255,255,.08);}
.sb-brand h2{font-family:'Playfair Display',serif;color:#fff;font-size:1.05rem;font-weight:700;}
.sb-brand p{color:rgba(255,255,255,.5);font-size:.68rem;margin-top:.2rem;}
.sb-menu{flex:1;padding:.9rem 0;}
.sb-section{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.3);padding:.6rem 1.25rem .3rem;}
.sb-item{display:flex;align-items:center;gap:.65rem;padding:.62rem 1.25rem;color:rgba(255,255,255,.75);font-size:.85rem;text-decoration:none;border-left:3px solid transparent;transition:all .15s;}
.sb-item:hover,.sb-item.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--gold);}
.sb-item .ic{font-size:.95rem;width:18px;text-align:center;}
.sb-footer{padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.08);}
.sb-user{display:flex;align-items:center;gap:.65rem;margin-bottom:.75rem;}
.sb-avatar{width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;}
.sb-user-info p{color:#fff;font-size:.82rem;font-weight:500;}
.sb-user-info span{color:rgba(255,255,255,.5);font-size:.68rem;text-transform:capitalize;}
.sb-logout{display:block;text-align:center;padding:.45rem;background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-radius:7px;font-size:.76rem;text-decoration:none;}
.main{margin-left:var(--sidebar);flex:1;padding:1.75rem 2rem;}
.page-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;}
.page-hd h1{font-family:'Playfair Display',serif;font-size:1.6rem;}
.btn-primary{padding:.55rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;}
.btn-gold{background:var(--gold);}
.msg{background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:9px;padding:.7rem 1.1rem;font-size:.82rem;margin-bottom:1rem;}
.card{background:var(--white);border-radius:16px;border:1px solid var(--border);overflow:hidden;}
.card-hd{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);}
.card-hd h3{font-size:.9rem;font-weight:600;}
table{width:100%;border-collapse:collapse;font-size:.82rem;}
th{padding:.65rem 1rem;text-align:left;font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray);background:var(--bg);}
td{padding:.78rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border:none;}
tr:hover td{background:#fafcfb;}
.p-sub{font-size:.72rem;color:var(--gray);}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.bg-green{background:var(--green-light);color:var(--green);}
.bg-red{background:#fef2f2;color:var(--red);}
.bg-gold{background:#fdf3e3;color:var(--gold);}
.bg-blue{background:var(--blue-bg);color:var(--blue);}
.bg-gray{background:#f3f4f6;color:#374151;}
.btn-xs{padding:.28rem .65rem;border-radius:6px;font-size:.72rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;text-decoration:none;}
.bx-toggle{background:var(--amber-bg);color:var(--amber);}
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center;}
.overlay.open{display:flex;}
.modal{background:#fff;border-radius:20px;width:100%;max-width:440px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal h3{font-size:1.05rem;font-weight:700;margin-bottom:1.25rem;}
.modal label{display:block;font-size:.72rem;font-weight:600;color:var(--text);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em;}
.modal select,.modal input{width:100%;padding:.65rem .9rem;border:1.5px solid var(--border);border-radius:9px;font-size:.86rem;font-family:inherit;outline:none;margin-bottom:.85rem;}
.modal-btns{display:flex;gap:.75rem;margin-top:.25rem;}
.modal-btns button{flex:1;padding:.75rem;border-radius:9px;font-weight:600;font-size:.88rem;font-family:inherit;cursor:pointer;border:none;}
.modal-cancel{background:#f3f4f6;color:#374151;}
.modal-save{background:var(--green);color:#fff;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sb-brand"><h2>Barangay Saraet</h2><p>Himamaylan City Admin</p></div>
  <nav class="sb-menu">
    <div class="sb-section">Main</div>
    <a href="dashboard.php"    class="sb-item"><span class="ic">🏠</span>Dashboard</a>
    <a href="queue.php"        class="sb-item"><span class="ic">🔢</span>Live Queue</a>
    <a href="appointments.php" class="sb-item"><span class="ic">📅</span>Appointments</a>
    <div class="sb-section">Records</div>
    <a href="residents.php"    class="sb-item"><span class="ic">👥</span>Residents</a>
    <a href="records.php"      class="sb-item"><span class="ic">📋</span>Client Records</a>
    <div class="sb-section">Settings</div>
    <a href="services.php"     class="sb-item"><span class="ic">⚙️</span>Services</a>
    <a href="schedules.php"    class="sb-item"><span class="ic">🗓️</span>Schedules</a>
    <a href="staff.php"        class="sb-item active"><span class="ic">🔐</span>Staff Accounts</a>
    <a href="reports.php"      class="sb-item"><span class="ic">📊</span>Reports</a>
  </nav>
  <div class="sb-footer">
    <div class="sb-user"><div class="sb-avatar"><?= strtoupper(substr($_SESSION['admin_name'],0,1)) ?></div><div class="sb-user-info"><p><?= htmlspecialchars($_SESSION['admin_name']) ?></p><span><?= $_SESSION['admin_role'] ?></span></div></div>
    <a href="logout.php" class="sb-logout">Sign Out</a>
  </div>
</aside>

<main class="main">
  <div class="page-hd"><h1>Staff Accounts</h1>
    <button class="btn-primary btn-gold" onclick="document.getElementById('addModal').classList.add('open')">+ Add Staff</button>
  </div>
  <?php if($msg): ?><div class="msg">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <div class="card">
    <div class="card-hd"><h3>Barangay Staff</h3></div>
    <table>
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Position</th><th>Role</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
      <tbody>
      <?php
      $role_map=['captain'=>'bg-gold','secretary'=>'bg-green','staff'=>'bg-blue','health_worker'=>'bg-green'];
      while($st=$staff->fetch_assoc()):
      ?>
      <tr>
        <td style="font-weight:600;color:var(--green)">#<?= $st['admin_id'] ?></td>
        <td><div style="font-weight:500"><?= htmlspecialchars($st['full_name']) ?></div></td>
        <td class="p-sub"><?= htmlspecialchars($st['email']) ?></td>
        <td class="p-sub"><?= htmlspecialchars($st['position']??'—') ?></td>
        <td><span class="badge <?= $role_map[$st['role']]??'bg-gray' ?>"><?= ucfirst(str_replace('_',' ',$st['role'])) ?></span></td>
        <td><span class="badge <?= $st['is_active']?'bg-green':'bg-red' ?>"><?= $st['is_active']?'Active':'Inactive' ?></span></td>
        <td class="p-sub"><?= date('M j, Y',strtotime($st['created_at'])) ?></td>
        <td>
          <?php if($st['admin_id'] != $_SESSION['admin_id']): ?>
          <a href="staff.php?toggle=<?= $st['admin_id'] ?>" class="btn-xs bx-toggle"><?= $st['is_active']?'Disable':'Enable' ?></a>
          <?php else: ?>
          <span class="p-sub">(you)</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>

<div class="overlay" id="addModal">
  <div class="modal">
    <h3>Add Staff Account</h3>
    <form method="POST">
      <input type="hidden" name="add_staff" value="1">
      <label>Full Name *</label>
      <input name="full_name" required placeholder="Full name">
      <label>Email Address *</label>
      <input type="email" name="email" required placeholder="email@saraet.gov.ph">
      <label>Position / Title</label>
      <input name="position" placeholder="e.g. Barangay Secretary">
      <div class="row2">
        <div>
          <label>Role</label>
          <select name="role">
            <option value="staff">Staff</option>
            <option value="secretary">Secretary</option>
            <option value="health_worker">Health Worker</option>
            <option value="captain">Captain</option>
          </select>
        </div>
        <div>
          <label>Password</label>
          <input type="password" name="password" placeholder="Staff@1234">
        </div>
      </div>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Create Account</button>
      </div>
    </form>
  </div>
</div>
<script>document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));</script>
</body>
</html>
