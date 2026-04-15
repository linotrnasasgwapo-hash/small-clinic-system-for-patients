<?php
// admin/services.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_service'])) {
    $sn  = clean($db,$_POST['service_name']??'');
    $cat = clean($db,$_POST['category']??'other');
    $desc= clean($db,$_POST['description']??'');
    $req = clean($db,$_POST['requirements']??'');
    $fee = (float)$_POST['fee'];
    $dur = (int)$_POST['duration_min'];
    $id  = (int)($_POST['service_id']??0);
    if ($id) {
        $db->query("UPDATE services SET service_name='$sn',category='$cat',description='$desc',requirements='$req',fee=$fee,duration_min=$dur WHERE service_id=$id");
        $msg='Service updated.';
    } else {
        $db->query("INSERT INTO services (service_name,category,description,requirements,fee,duration_min) VALUES('$sn','$cat','$desc','$req',$fee,$dur)");
        $msg='Service added.';
    }
}
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $db->query("UPDATE services SET is_active=NOT is_active WHERE service_id=$tid");
    header('Location: services.php?msg=toggled'); exit;
}
$svcs = $db->query("SELECT * FROM services ORDER BY category, service_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Services — Barangay Saraet Admin</title>
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
.tbl-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:.82rem;}
th{padding:.65rem 1rem;text-align:left;font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray);background:var(--bg);}
td{padding:.78rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border:none;}
tr:hover td{background:#fafcfb;}
.p-sub{font-size:.72rem;color:var(--gray);}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.bg-green{background:var(--green-light);color:var(--green);}
.bg-red{background:#fef2f2;color:var(--red);}
.cat-doc{background:#eff6ff;color:#1d4ed8;}
.cat-health{background:#f0fdf4;color:#166534;}
.cat-social{background:#fef3c7;color:#92400e;}
.cat-legal{background:#fdf2f8;color:#9d174d;}
.cat-financial{background:#f0fdf4;color:#065f46;}
.btn-xs{padding:.28rem .65rem;border-radius:6px;font-size:.72rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;text-decoration:none;display:inline-block;}
.bx-edit{background:var(--blue-bg);color:var(--blue);}
.bx-toggle{background:var(--amber-bg);color:var(--amber);}
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center;}
.overlay.open{display:flex;}
.modal{background:#fff;border-radius:20px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal h3{font-size:1.05rem;font-weight:700;margin-bottom:1.25rem;}
.modal label{display:block;font-size:.72rem;font-weight:600;color:var(--text);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em;}
.modal select,.modal input,.modal textarea{width:100%;padding:.65rem .9rem;border:1.5px solid var(--border);border-radius:9px;font-size:.86rem;font-family:inherit;outline:none;margin-bottom:.8rem;}
.modal textarea{resize:vertical;min-height:60px;}
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
    <a href="services.php"     class="sb-item active"><span class="ic">⚙️</span>Services</a>
    <a href="schedules.php"    class="sb-item"><span class="ic">🗓️</span>Schedules</a>
    <a href="staff.php"        class="sb-item"><span class="ic">🔐</span>Staff Accounts</a>
    <a href="reports.php"      class="sb-item"><span class="ic">📊</span>Reports</a>
  </nav>
  <div class="sb-footer">
    <div class="sb-user"><div class="sb-avatar"><?= strtoupper(substr($_SESSION['admin_name'],0,1)) ?></div><div class="sb-user-info"><p><?= htmlspecialchars($_SESSION['admin_name']) ?></p><span><?= $_SESSION['admin_role'] ?></span></div></div>
    <a href="logout.php" class="sb-logout">Sign Out</a>
  </div>
</aside>

<main class="main">
  <div class="page-hd">
    <h1>Services</h1>
    <button class="btn-primary btn-gold" onclick="openEdit(0,'','other','','','0','20')">+ Add Service</button>
  </div>
  <?php if($msg || isset($_GET['msg'])): ?><div class="msg">✅ <?= htmlspecialchars($msg ?: 'Done.') ?></div><?php endif; ?>
  <div class="card">
    <div class="card-hd"><h3>All Barangay Services</h3></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>ID</th><th>Service Name</th><th>Category</th><th>Requirements</th><th>Fee</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php
        $cats=['document'=>'cat-doc','health'=>'cat-health','social'=>'cat-social','legal'=>'cat-legal','financial'=>'cat-financial'];
        while($sv=$svcs->fetch_assoc()):
        ?>
        <tr>
          <td style="font-weight:600;color:var(--green)">#<?= $sv['service_id'] ?></td>
          <td>
            <div style="font-weight:500"><?= htmlspecialchars($sv['service_name']) ?></div>
            <div class="p-sub"><?= htmlspecialchars(substr($sv['description']??'',0,50)) ?></div>
          </td>
          <td><span class="badge <?= $cats[$sv['category']]??'bg-green' ?>"><?= ucfirst($sv['category']) ?></span></td>
          <td class="p-sub" style="max-width:160px"><?= htmlspecialchars(substr($sv['requirements']??'—',0,50)) ?></td>
          <td class="p-sub">₱<?= number_format($sv['fee'],2) ?></td>
          <td class="p-sub"><?= $sv['duration_min'] ?> min</td>
          <td><span class="badge <?= $sv['is_active']?'bg-green':'bg-red' ?>"><?= $sv['is_active']?'Active':'Inactive' ?></span></td>
          <td style="display:flex;gap:.35rem">
            <button class="btn-xs bx-edit" onclick="openEdit(<?= $sv['service_id'] ?>,'<?= addslashes($sv['service_name']) ?>','<?= $sv['category'] ?>','<?= addslashes($sv['description']??'') ?>','<?= addslashes($sv['requirements']??'') ?>','<?= $sv['fee'] ?>','<?= $sv['duration_min'] ?>')">Edit</button>
            <a href="services.php?toggle=<?= $sv['service_id'] ?>" class="btn-xs bx-toggle"><?= $sv['is_active']?'Disable':'Enable' ?></a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div class="overlay" id="svcModal">
  <div class="modal">
    <h3 id="modal-title">Add Service</h3>
    <form method="POST">
      <input type="hidden" name="save_service" value="1">
      <input type="hidden" name="service_id" id="svc_id">
      <label>Service Name *</label>
      <input name="service_name" id="svc_name" required placeholder="e.g. Barangay Clearance">
      <div class="row2">
        <div>
          <label>Category</label>
          <select name="category" id="svc_cat">
            <option value="document">Document</option><option value="health">Health</option>
            <option value="social">Social</option><option value="legal">Legal</option>
            <option value="financial">Financial</option><option value="other">Other</option>
          </select>
        </div>
        <div>
          <label>Fee (₱)</label>
          <input type="number" name="fee" id="svc_fee" min="0" step="0.01" value="0">
        </div>
      </div>
      <label>Description</label>
      <textarea name="description" id="svc_desc" placeholder="Brief description..."></textarea>
      <label>Requirements</label>
      <textarea name="requirements" id="svc_req" placeholder="List required documents..."></textarea>
      <label>Duration (minutes)</label>
      <input type="number" name="duration_min" id="svc_dur" value="20" min="5" max="120">
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('svcModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Save Service</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(id, name, cat, desc, req, fee, dur) {
  document.getElementById('modal-title').textContent = id ? 'Edit Service' : 'Add Service';
  document.getElementById('svc_id').value   = id;
  document.getElementById('svc_name').value = name;
  document.getElementById('svc_cat').value  = cat;
  document.getElementById('svc_desc').value = desc;
  document.getElementById('svc_req').value  = req;
  document.getElementById('svc_fee').value  = fee;
  document.getElementById('svc_dur').value  = dur;
  document.getElementById('svcModal').classList.add('open');
}
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));
</script>
</body>
</html>
