<?php
// admin/schedules.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_schedule'])) {
    $sid  = (int)$_POST['service_id'];
    $date = clean($db,$_POST['sched_date']??'');
    $st   = clean($db,$_POST['start_time']??'');
    $et   = clean($db,$_POST['end_time']??'');
    $ms   = (int)$_POST['max_slots'];
    $hby  = (int)($_POST['handled_by']??$_SESSION['admin_id']);
    $db->query("INSERT INTO schedules (service_id,sched_date,start_time,end_time,max_slots,handled_by) VALUES($sid,'$date','$st','$et',$ms,$hby)");
    $msg = 'Schedule added.';
}
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['toggle_id'])) {
    $tid = (int)$_POST['toggle_id'];
    $db->query("UPDATE schedules SET is_available = NOT is_available WHERE schedule_id=$tid");
    $msg = 'Schedule updated.';
}

$rows = $db->query("
    SELECT sc.*, s.service_name, a.full_name handler_name
    FROM schedules sc
    LEFT JOIN services s ON sc.service_id=s.service_id
    LEFT JOIN admins a ON sc.handled_by=a.admin_id
    WHERE sc.sched_date >= CURDATE()
    ORDER BY sc.sched_date, sc.start_time
    LIMIT 60
");
$services = $db->query("SELECT service_id, service_name FROM services WHERE is_active=1");
$admins   = $db->query("SELECT admin_id, full_name FROM admins WHERE is_active=1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Schedules — Barangay Saraet Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-light:#e6f4ec;--gold:#c9933a;--bg:#f2f6f4;--white:#fff;--text:#1a2e23;--gray:#5a6a62;--border:#dce8e2;--amber:#d97706;--amber-bg:#fef3c7;--red:#dc2626;--sidebar:250px;}
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
.btn-primary{padding:.55rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;}
.btn-gold{background:var(--gold);}
.btn-gold:hover{background:#a87a2a;}
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
.p-sub{font-size:.72rem;color:var(--gray);}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.bg-green{background:var(--green-light);color:var(--green);}
.bg-red{background:#fef2f2;color:var(--red);}
.slots-bar{background:#e5e7eb;border-radius:4px;height:6px;width:80px;display:inline-block;vertical-align:middle;margin-left:.4rem;}
.slots-fill{background:var(--green);height:6px;border-radius:4px;}
.btn-xs{padding:.28rem .65rem;border-radius:6px;font-size:.72rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;}
.bx-toggle{background:var(--amber-bg);color:var(--amber);}
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center;}
.overlay.open{display:flex;}
.modal{background:#fff;border-radius:20px;width:100%;max-width:460px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.2);}
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
    <a href="schedules.php"    class="sb-item active"><span class="ic">🗓️</span>Schedules</a>
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
    <h1>Schedules</h1>
    <button class="btn-primary btn-gold" onclick="document.getElementById('addModal').classList.add('open')">+ Add Schedule</button>
  </div>
  <?php if($msg): ?><div class="msg">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <div class="card">
    <div class="card-hd"><h3>Upcoming Schedules</h3><span style="font-size:.78rem;color:var(--gray)"><?= $rows->num_rows ?> schedules</span></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>ID</th><th>Service</th><th>Date</th><th>Time</th><th>Slots</th><th>Handler</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($sc=$rows->fetch_assoc()):
          $pct = $sc['max_slots']>0 ? round($sc['booked_slots']/$sc['max_slots']*100) : 0;
        ?>
        <tr>
          <td style="font-weight:600;color:var(--green)">#<?= $sc['schedule_id'] ?></td>
          <td style="font-weight:500"><?= htmlspecialchars($sc['service_name']??'—') ?></td>
          <td><?= date('l, M j, Y', strtotime($sc['sched_date'])) ?></td>
          <td class="p-sub"><?= date('h:i A',strtotime($sc['start_time'])) ?> – <?= date('h:i A',strtotime($sc['end_time'])) ?></td>
          <td>
            <span style="font-size:.8rem"><?= $sc['booked_slots'] ?>/<?= $sc['max_slots'] ?></span>
            <div class="slots-bar"><div class="slots-fill" style="width:<?= $pct ?>%"></div></div>
          </td>
          <td class="p-sub"><?= htmlspecialchars($sc['handler_name']??'—') ?></td>
          <td><span class="badge <?= $sc['is_available']?'bg-green':'bg-red' ?>"><?= $sc['is_available']?'Available':'Closed' ?></span></td>
          <td>
            <form method="POST" style="display:inline">
              <input type="hidden" name="toggle_id" value="<?= $sc['schedule_id'] ?>">
              <button class="btn-xs bx-toggle" type="submit"><?= $sc['is_available']?'Close':'Open' ?></button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<div class="overlay" id="addModal">
  <div class="modal">
    <h3>Add New Schedule</h3>
    <form method="POST">
      <input type="hidden" name="add_schedule" value="1">
      <label>Service</label>
      <select name="service_id" required>
        <option value="">— Select service —</option>
        <?php $services->data_seek(0); while($sv=$services->fetch_assoc()): ?>
        <option value="<?= $sv['service_id'] ?>"><?= htmlspecialchars($sv['service_name']) ?></option>
        <?php endwhile; ?>
      </select>
      <label>Schedule Date</label>
      <input type="date" name="sched_date" required min="<?= date('Y-m-d') ?>">
      <div class="row2">
        <div><label>Start Time</label><input type="time" name="start_time" required value="08:00"></div>
        <div><label>End Time</label><input type="time" name="end_time" required value="12:00"></div>
      </div>
      <div class="row2">
        <div><label>Max Slots</label><input type="number" name="max_slots" required value="20" min="1" max="100"></div>
        <div><label>Handled By</label>
          <select name="handled_by">
            <?php $admins->data_seek(0); while($adm=$admins->fetch_assoc()): ?>
            <option value="<?= $adm['admin_id'] ?>" <?= $adm['admin_id']==$_SESSION['admin_id']?'selected':'' ?>><?= htmlspecialchars($adm['full_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Add Schedule</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open')}));
</script>
</body>
</html>
