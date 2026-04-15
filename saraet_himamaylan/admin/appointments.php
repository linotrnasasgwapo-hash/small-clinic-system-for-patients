<?php
// admin/appointments.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();

$msg = '';

// ── Update status ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_status'])) {
    $id  = (int)$_POST['appt_id'];
    $st  = clean($db, $_POST['status']);
    $rem = clean($db, $_POST['remarks']??'');
    $db->query("UPDATE appointments SET status='$st', remarks='$rem', handled_by={$_SESSION['admin_id']} WHERE appt_id=$id");
    $msg = 'Appointment updated.';
}

// ── Walk-in booking ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['walkin_book'])) {
    $rid  = (int)$_POST['resident_id'];
    $sid  = (int)$_POST['service_id'];
    $schid= (int)($_POST['schedule_id']??0);
    $purp = clean($db, $_POST['purpose']??'Walk-in');
    $today= date('Y-m-d');
    $now  = date('H:i:s');
    $r2   = $db->query("SELECT COALESCE(MAX(queue_number),0)+1 AS n FROM appointments WHERE appointment_date='$today'");
    $qn   = $r2->fetch_row()[0];
    $db->query("INSERT INTO appointments (resident_id,service_id,schedule_id,queue_number,appointment_date,appointment_time,purpose,status,handled_by) VALUES($rid,$sid,".($schid?:NULL).",$qn,'$today','$now','$purp','confirmed',{$_SESSION['admin_id']})");
    $aid = $db->insert_id;
    $db->query("INSERT INTO queue (appt_id,queue_number,queue_date,service_id,status) VALUES($aid,$qn,'$today',$sid,'waiting')");
    $msg = "Walk-in booked. Queue #$qn";
}

// ── Filters ────────────────────────────────────────────────────
$fdate   = clean($db, $_GET['date']??date('Y-m-d'));
$fstatus = clean($db, $_GET['status']??'');
$fsearch = clean($db, $_GET['q']??'');

$where = "WHERE a.appointment_date='$fdate'";
if ($fstatus) $where .= " AND a.status='$fstatus'";
if ($fsearch) $where .= " AND (r.first_name LIKE '%$fsearch%' OR r.last_name LIKE '%$fsearch%' OR r.phone LIKE '%$fsearch%')";

$rows = $db->query("
    SELECT a.*, r.first_name, r.last_name, r.phone, r.purok,
           s.service_name, adm.full_name AS handled_name
    FROM appointments a
    JOIN residents r ON a.resident_id=r.resident_id
    LEFT JOIN services s ON a.service_id=s.service_id
    LEFT JOIN admins adm ON a.handled_by=adm.admin_id
    $where ORDER BY a.appointment_time ASC
");

$residents = $db->query("SELECT resident_id, first_name, last_name, phone FROM residents ORDER BY last_name");
$services  = $db->query("SELECT service_id, service_name FROM services WHERE is_active=1");

$statuses=['pending','confirmed','in_queue','serving','done','cancelled','no_show'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Appointments — Barangay Saraet Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
<?php include_once 'shared_admin_style.css.php'; ?>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-mid:#2a7d50;--green-light:#e6f4ec;--gold:#c9933a;--gold-light:#fdf3e3;--bg:#f2f6f4;--white:#fff;--text:#1a2e23;--gray:#5a6a62;--border:#dce8e2;--amber:#d97706;--amber-bg:#fef3c7;--red:#dc2626;--red-bg:#fef2f2;--blue:#1d4ed8;--blue-bg:#eff6ff;--sidebar:250px;}
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
.sb-avatar{width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:.85rem;color:#fff;font-weight:700;}
.sb-user-info p{color:#fff;font-size:.82rem;font-weight:500;}
.sb-user-info span{color:rgba(255,255,255,.5);font-size:.68rem;text-transform:capitalize;}
.sb-logout{display:block;text-align:center;padding:.45rem;background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-radius:7px;font-size:.76rem;text-decoration:none;}
.sb-logout:hover{background:rgba(255,255,255,.15);}
.main{margin-left:var(--sidebar);flex:1;padding:1.75rem 2rem;}
.page-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;}
.page-hd h1{font-family:'Playfair Display',serif;font-size:1.6rem;}
.btn-primary{padding:.55rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:.82rem;font-weight:600;font-family:inherit;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;transition:background .15s;}
.btn-primary:hover{background:var(--green2);}
.btn-gold{background:var(--gold);}
.btn-gold:hover{background:#a87a2a;}
.filter-bar{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:.9rem 1.25rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;margin-bottom:1.25rem;}
.filter-bar input,.filter-bar select{padding:.55rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:.82rem;font-family:inherit;outline:none;}
.filter-bar input:focus,.filter-bar select:focus{border-color:var(--green);}
.filter-bar button{padding:.55rem 1rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.msg{background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:9px;padding:.7rem 1.1rem;font-size:.82rem;margin-bottom:1rem;}
.card{background:var(--white);border-radius:16px;border:1px solid var(--border);overflow:hidden;}
.card-hd{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);}
.card-hd h3{font-size:.9rem;font-weight:600;}
.tbl-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:.82rem;}
th{padding:.65rem 1rem;text-align:left;font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray);background:var(--bg);}
td{padding:.75rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border:none;}
tr:hover td{background:#fafcfb;}
.p-name{font-weight:500;}
.p-sub{font-size:.72rem;color:var(--gray);}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.bg-green{background:var(--green-light);color:var(--green);}
.bg-amber{background:var(--amber-bg);color:var(--amber);}
.bg-red{background:var(--red-bg);color:var(--red);}
.bg-blue{background:var(--blue-bg);color:var(--blue);}
.bg-gray{background:#f3f4f6;color:#374151;}
.q-num{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:var(--green-light);color:var(--green);font-weight:700;font-size:.82rem;}
.btn-xs{padding:.28rem .65rem;border-radius:6px;font-size:.72rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;}
.bx-edit{background:var(--blue-bg);color:var(--blue);}

/* MODAL */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center;}
.overlay.open{display:flex;}
.modal{background:#fff;border-radius:20px;width:100%;max-width:460px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal h3{font-size:1.05rem;font-weight:700;margin-bottom:1.25rem;}
.modal label{display:block;font-size:.75rem;font-weight:600;color:var(--text);margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.04em;}
.modal select,.modal textarea,.modal input{width:100%;padding:.7rem .9rem;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;font-family:inherit;outline:none;margin-bottom:.9rem;}
.modal select:focus,.modal textarea:focus{border-color:var(--green);}
.modal-btns{display:flex;gap:.75rem;margin-top:.25rem;}
.modal-btns button{flex:1;padding:.75rem;border-radius:9px;font-weight:600;font-size:.88rem;font-family:inherit;cursor:pointer;border:none;}
.modal-cancel{background:#f3f4f6;color:#374151;}
.modal-save{background:var(--green);color:#fff;}

/* WALKIN MODAL */
.wm select,.wm input{margin-bottom:.75rem;}
</style>
</head>
<body>

<!-- SIDEBAR (reused) -->
<aside class="sidebar">
  <div class="sb-brand"><h2>Barangay Saraet</h2><p>Himamaylan City Admin</p></div>
  <nav class="sb-menu">
    <div class="sb-section">Main</div>
    <a href="dashboard.php"    class="sb-item"><span class="ic">🏠</span>Dashboard</a>
    <a href="queue.php"        class="sb-item"><span class="ic">🔢</span>Live Queue</a>
    <a href="appointments.php" class="sb-item active"><span class="ic">📅</span>Appointments</a>
    <div class="sb-section">Records</div>
    <a href="residents.php"    class="sb-item"><span class="ic">👥</span>Residents</a>
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

<!-- MAIN -->
<main class="main">
  <div class="page-hd">
    <h1>Appointments</h1>
    <div style="display:flex;gap:.6rem">
      <button class="btn-primary btn-gold" onclick="document.getElementById('walkinModal').classList.add('open')">+ Walk-in</button>
    </div>
  </div>

  <?php if($msg): ?><div class="msg">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <!-- FILTER BAR -->
  <form method="GET" class="filter-bar">
    <input type="date" name="date" value="<?= $fdate ?>">
    <input type="text" name="q" placeholder="Search resident name / phone" value="<?= htmlspecialchars($fsearch) ?>" style="min-width:200px;">
    <select name="status">
      <option value="">All Status</option>
      <?php foreach($statuses as $st): ?>
      <option <?= $fstatus===$st?'selected':'' ?> value="<?= $st ?>"><?= ucfirst(str_replace('_',' ',$st)) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
    <a href="appointments.php" style="font-size:.8rem;color:var(--gray);text-decoration:none;">Reset</a>
  </form>

  <!-- TABLE -->
  <div class="card">
    <div class="card-hd">
      <h3>Appointments for <?= date('F j, Y', strtotime($fdate)) ?></h3>
      <span style="font-size:.78rem;color:var(--gray)"><?= $rows->num_rows ?> records</span>
    </div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>#</th><th>Queue</th><th>Resident</th><th>Service</th><th>Time</th><th>Purpose</th><th>Status</th><th>Handled By</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($a=$rows->fetch_assoc()):
          $smap=['pending'=>'bg-gray','confirmed'=>'bg-green','in_queue'=>'bg-amber','serving'=>'bg-green','done'=>'bg-blue','cancelled'=>'bg-red','no_show'=>'bg-red'];
        ?>
        <tr>
          <td style="font-weight:600;color:var(--green)">#<?= $a['appt_id'] ?></td>
          <td><div class="q-num"><?= $a['queue_number'] ?></div></td>
          <td>
            <div class="p-name"><?= htmlspecialchars($a['first_name'].' '.$a['last_name']) ?></div>
            <div class="p-sub"><?= htmlspecialchars($a['phone']??'') ?> &middot; <?= htmlspecialchars($a['purok']??'') ?></div>
          </td>
          <td class="p-sub"><?= htmlspecialchars($a['service_name']??'—') ?></td>
          <td class="p-sub"><?= date('h:i A',strtotime($a['appointment_time'])) ?></td>
          <td class="p-sub" style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($a['purpose']??'') ?>"><?= htmlspecialchars(substr($a['purpose']??'—',0,30)) ?></td>
          <td><span class="badge <?= $smap[$a['status']]??'bg-gray' ?>"><?= ucfirst(str_replace('_',' ',$a['status'])) ?></span></td>
          <td class="p-sub"><?= htmlspecialchars($a['handled_name']??'—') ?></td>
          <td><button class="btn-xs bx-edit" onclick="openEdit(<?= $a['appt_id'] ?>,'<?= $a['status'] ?>','<?= addslashes($a['remarks']??'') ?>')">Edit</button></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- EDIT MODAL -->
<div class="overlay" id="editModal">
  <div class="modal">
    <h3>Update Appointment</h3>
    <form method="POST">
      <input type="hidden" name="update_status" value="1">
      <input type="hidden" name="appt_id" id="editId">
      <label>Status</label>
      <select name="status" id="editStatus">
        <?php foreach($statuses as $st): ?>
        <option value="<?= $st ?>"><?= ucfirst(str_replace('_',' ',$st)) ?></option>
        <?php endforeach; ?>
      </select>
      <label>Remarks / Notes</label>
      <textarea name="remarks" id="editRemarks" rows="3" placeholder="Optional remarks..."></textarea>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- WALK-IN MODAL -->
<div class="overlay wm" id="walkinModal">
  <div class="modal">
    <h3>Walk-in Appointment</h3>
    <form method="POST">
      <input type="hidden" name="walkin_book" value="1">
      <label>Resident</label>
      <select name="resident_id" required>
        <option value="">— Select resident —</option>
        <?php $residents->data_seek(0); while($rr=$residents->fetch_assoc()): ?>
        <option value="<?= $rr['resident_id'] ?>"><?= htmlspecialchars($rr['last_name'].', '.$rr['first_name'].' ('.$rr['phone'].')') ?></option>
        <?php endwhile; ?>
      </select>
      <label>Service</label>
      <select name="service_id" required>
        <option value="">— Select service —</option>
        <?php $services->data_seek(0); while($sv=$services->fetch_assoc()): ?>
        <option value="<?= $sv['service_id'] ?>"><?= htmlspecialchars($sv['service_name']) ?></option>
        <?php endwhile; ?>
      </select>
      <label>Purpose / Notes</label>
      <textarea name="purpose" rows="2" placeholder="Brief description of visit..."></textarea>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('walkinModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Book Walk-in</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(id, status, remarks) {
  document.getElementById('editId').value     = id;
  document.getElementById('editStatus').value = status;
  document.getElementById('editRemarks').value = remarks;
  document.getElementById('editModal').classList.add('open');
}
document.querySelectorAll('.overlay').forEach(o => {
  o.addEventListener('click', e => { if(e.target===o) o.classList.remove('open'); });
});
</script>
</body>
</html>
