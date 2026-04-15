<?php
// admin/residents.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';

// Add resident
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_resident'])) {
    $fn  = clean($db,$_POST['first_name']??'');
    $ln  = clean($db,$_POST['last_name']??'');
    $mn  = clean($db,$_POST['middle_name']??'');
    $em  = clean($db,$_POST['email']??'');
    $ph  = clean($db,$_POST['phone']??'');
    $bd  = clean($db,$_POST['birthdate']??'');
    $gn  = clean($db,$_POST['gender']??'');
    $cs  = clean($db,$_POST['civil_status']??'Single');
    $pur = clean($db,$_POST['purok']??'');
    $add = clean($db,$_POST['address']??'');
    $pw  = password_hash('Resident@1234', PASSWORD_BCRYPT);
    $stmt= $db->prepare("INSERT INTO residents (first_name,last_name,middle_name,email,password,phone,address,birthdate,gender,civil_status,purok) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssssssss',$fn,$ln,$mn,$em,$pw,$ph,$add,$bd,$gn,$cs,$pur);
    $stmt->execute() ? $msg="Resident added. Default password: Resident\@1234" : $msg='Error: Email may already exist.';
}

$search = clean($db,$_GET['q']??'');
$where  = $search ? "WHERE first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR phone LIKE '%$search%' OR purok LIKE '%$search%'" : '';
$rows   = $db->query("SELECT r.*, (SELECT COUNT(*) FROM appointments WHERE resident_id=r.resident_id) appt_count, (SELECT COUNT(*) FROM client_records WHERE resident_id=r.resident_id) rec_count FROM residents r $where ORDER BY last_name, first_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Residents — Barangay Saraet Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-mid:#2a7d50;--green-light:#e6f4ec;--gold:#c9933a;--bg:#f2f6f4;--white:#fff;--text:#1a2e23;--gray:#5a6a62;--border:#dce8e2;--amber:#d97706;--amber-bg:#fef3c7;--red:#dc2626;--red-bg:#fef2f2;--blue:#1d4ed8;--blue-bg:#eff6ff;--sidebar:250px;}
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
.sb-avatar{width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;}
.sb-user-info p{color:#fff;font-size:.82rem;font-weight:500;}
.sb-user-info span{color:rgba(255,255,255,.5);font-size:.68rem;text-transform:capitalize;}
.sb-logout{display:block;text-align:center;padding:.45rem;background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-radius:7px;font-size:.76rem;text-decoration:none;}
.sb-logout:hover{background:rgba(255,255,255,.15);}
.main{margin-left:var(--sidebar);flex:1;padding:1.75rem 2rem;}
.page-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;}
.page-hd h1{font-family:'Playfair Display',serif;font-size:1.6rem;}
.btn-primary{padding:.55rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;transition:background .15s;}
.btn-primary:hover{background:var(--green2);}
.btn-gold{background:var(--gold);}
.btn-gold:hover{background:#a87a2a;}
.filter-bar{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:.9rem 1.25rem;display:flex;gap:.65rem;flex-wrap:wrap;align-items:center;margin-bottom:1.25rem;}
.filter-bar input{padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:.82rem;font-family:inherit;outline:none;min-width:240px;}
.filter-bar input:focus{border-color:var(--green);}
.filter-bar button{padding:.5rem .9rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
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
.res-name{font-weight:600;font-size:.88rem;}
.res-sub{font-size:.72rem;color:var(--gray);}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.bg-green{background:var(--green-light);color:var(--green);}
.bg-blue{background:var(--blue-bg);color:var(--blue);}
.count-chip{display:inline-flex;align-items:center;gap:.28rem;background:var(--blue-bg);color:var(--blue);padding:.18rem .55rem;border-radius:7px;font-size:.7rem;font-weight:700;}
.btn-xs{padding:.28rem .65rem;border-radius:6px;font-size:.72rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;}
.bx-view{background:var(--green-light);color:var(--green);}
.bx-rec{background:var(--blue-bg);color:var(--blue);}
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center;}
.overlay.open{display:flex;}
.modal{background:#fff;border-radius:20px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal h3{font-size:1.05rem;font-weight:700;margin-bottom:1.25rem;}
.modal label{display:block;font-size:.72rem;font-weight:600;color:var(--text);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em;}
.modal select,.modal input{width:100%;padding:.65rem .9rem;border:1.5px solid var(--border);border-radius:9px;font-size:.86rem;font-family:inherit;outline:none;margin-bottom:.8rem;}
.modal select:focus,.modal input:focus{border-color:var(--green);}
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
    <a href="residents.php"    class="sb-item active"><span class="ic">👥</span>Residents</a>
    <a href="records.php"      class="sb-item"><span class="ic">📋</span>Client Records</a>
    <div class="sb-section">Settings</div>
    <a href="services.php"     class="sb-item"><span class="ic">⚙️</span>Services</a>
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
    <h1>Residents</h1>
    <button class="btn-primary btn-gold" onclick="document.getElementById('addModal').classList.add('open')">+ Add Resident</button>
  </div>

  <?php if($msg): ?><div class="msg">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <form method="GET" class="filter-bar">
    <input type="text" name="q" placeholder="🔍 Search by name, phone, or purok..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
    <?php if($search): ?><a href="residents.php" style="font-size:.8rem;color:var(--gray);text-decoration:none;">Clear</a><?php endif; ?>
  </form>

  <div class="card">
    <div class="card-hd">
      <h3>Registered Residents</h3>
      <span style="font-size:.78rem;color:var(--gray)"><?= $rows->num_rows ?> found</span>
    </div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>ID</th><th>Name</th><th>Contact</th><th>Purok</th><th>Gender</th><th>Age</th><th>Appts</th><th>Records</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($res=$rows->fetch_assoc()):
          $age = $res['birthdate'] ? (int)date_diff(date_create($res['birthdate']),date_create('today'))->y : '—';
        ?>
        <tr>
          <td style="font-weight:600;color:var(--green)">#<?= $res['resident_id'] ?></td>
          <td>
            <div class="res-name"><?= htmlspecialchars($res['last_name'].', '.$res['first_name']) ?></div>
            <div class="res-sub"><?= htmlspecialchars($res['email']) ?></div>
          </td>
          <td class="res-sub"><?= htmlspecialchars($res['phone']??'—') ?></td>
          <td class="res-sub"><?= htmlspecialchars($res['purok']??'—') ?></td>
          <td class="res-sub"><?= htmlspecialchars($res['gender']??'—') ?></td>
          <td class="res-sub"><?= $age ?></td>
          <td><span class="count-chip">📅 <?= $res['appt_count'] ?></span></td>
          <td><span class="count-chip">📋 <?= $res['rec_count'] ?></span></td>
          <td class="res-sub"><?= date('M j, Y', strtotime($res['created_at'])) ?></td>
          <td style="display:flex;gap:.35rem">
            <a href="records.php?resident_id=<?= $res['resident_id'] ?>" class="btn-xs bx-rec">Records</a>
            <a href="appointments.php?resident_id=<?= $res['resident_id'] ?>" class="btn-xs bx-view">Appts</a>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- ADD RESIDENT MODAL -->
<div class="overlay" id="addModal">
  <div class="modal">
    <h3>Add New Resident</h3>
    <form method="POST">
      <input type="hidden" name="add_resident" value="1">
      <div class="row2">
        <div><label>First Name *</label><input name="first_name" required placeholder="Juan"></div>
        <div><label>Last Name *</label><input name="last_name" required placeholder="Cruz"></div>
      </div>
      <div><label>Middle Name</label><input name="middle_name" placeholder="Optional"></div>
      <div><label>Email Address *</label><input type="email" name="email" required placeholder="juan@email.com"></div>
      <div class="row2">
        <div><label>Phone</label><input name="phone" placeholder="09171234567"></div>
        <div><label>Purok</label><input name="purok" placeholder="Purok 1"></div>
      </div>
      <div class="row2">
        <div><label>Birthdate</label><input type="date" name="birthdate"></div>
        <div><label>Gender</label>
          <select name="gender"><option value="">Select</option><option>Male</option><option>Female</option><option>Other</option></select>
        </div>
      </div>
      <div class="row2">
        <div><label>Civil Status</label>
          <select name="civil_status"><option>Single</option><option>Married</option><option>Widowed</option><option>Separated</option></select>
        </div>
        <div><label>Address</label><input name="address" placeholder="Barangay Saraet"></div>
      </div>
      <p style="font-size:.74rem;color:var(--gray);margin-bottom:1rem;">Default password will be set to <strong>Resident@1234</strong></p>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Add Resident</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.overlay').forEach(o => {
  o.addEventListener('click', e => { if(e.target===o) o.classList.remove('open'); });
});
</script>
</body>
</html>
