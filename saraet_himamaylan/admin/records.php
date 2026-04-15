<?php
// admin/records.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$msg = '';

// Save new record
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_record'])) {
    $rid  = (int)$_POST['resident_id'];
    $sid  = (int)($_POST['service_id']??0);
    $aid  = (int)($_POST['appt_id']??0) ?: 'NULL';
    $rtype= clean($db,$_POST['record_type']??'other');
    $det  = clean($db,$_POST['details']??'');
    $dn   = clean($db,$_POST['documents_needed']??'');
    $ds   = clean($db,$_POST['documents_submitted']??'');
    $out  = clean($db,$_POST['outcome']??'');
    $st   = clean($db,$_POST['status']??'open');
    $adid = $_SESSION['admin_id'];
    $db->query("INSERT INTO client_records (resident_id,appt_id,service_id,record_type,details,documents_needed,documents_submitted,outcome,status,recorded_by) VALUES($rid,$aid,$sid,'$rtype','$det','$dn','$ds','$out','$st',$adid)");
    $msg = 'Client record saved.';
}
// Update record
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_record'])) {
    $recid= (int)$_POST['record_id'];
    $out  = clean($db,$_POST['outcome']??'');
    $ds   = clean($db,$_POST['documents_submitted']??'');
    $st   = clean($db,$_POST['status']??'open');
    $db->query("UPDATE client_records SET outcome='$out',documents_submitted='$ds',status='$st',recorded_by={$_SESSION['admin_id']} WHERE record_id=$recid");
    $msg = 'Record updated.';
}

$fsearch = clean($db,$_GET['q']??'');
$fstatus = clean($db,$_GET['status']??'');
$ftype   = clean($db,$_GET['type']??'');

$where='WHERE 1=1';
if($fsearch) $where.=" AND (r.first_name LIKE '%$fsearch%' OR r.last_name LIKE '%$fsearch%' OR cr.details LIKE '%$fsearch%')";
if($fstatus) $where.=" AND cr.status='$fstatus'";
if($ftype)   $where.=" AND cr.record_type='$ftype'";

$records = $db->query("
    SELECT cr.*, r.first_name, r.last_name, r.phone, r.purok,
           s.service_name, adm.full_name AS recorder
    FROM client_records cr
    JOIN residents r  ON cr.resident_id=r.resident_id
    LEFT JOIN services s ON cr.service_id=s.service_id
    LEFT JOIN admins adm ON cr.recorded_by=adm.admin_id
    $where ORDER BY cr.created_at DESC LIMIT 50
");
$residents = $db->query("SELECT resident_id,first_name,last_name,phone FROM residents ORDER BY last_name");
$services  = $db->query("SELECT service_id,service_name FROM services WHERE is_active=1");
$rtypes    = ['document_request','health_record','social_assistance','certificate','complaint','other'];
$rstatuses = ['open','processing','released','rejected','archived'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Client Records — Barangay Saraet Admin</title>
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
.sb-avatar{width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;}
.sb-user-info p{color:#fff;font-size:.82rem;font-weight:500;}
.sb-user-info span{color:rgba(255,255,255,.5);font-size:.68rem;text-transform:capitalize;}
.sb-logout{display:block;text-align:center;padding:.45rem;background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-radius:7px;font-size:.76rem;text-decoration:none;}
.main{margin-left:var(--sidebar);flex:1;padding:1.75rem 2rem;}
.page-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;}
.page-hd h1{font-family:'Playfair Display',serif;font-size:1.6rem;}
.btn-primary{padding:.55rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:.82rem;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;}
.btn-primary:hover{background:var(--green2);}
.btn-gold{background:var(--gold);}
.btn-gold:hover{background:#a87a2a;}
.filter-bar{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:.9rem 1.25rem;display:flex;gap:.65rem;flex-wrap:wrap;align-items:center;margin-bottom:1.25rem;}
.filter-bar input,.filter-bar select{padding:.5rem .8rem;border:1.5px solid var(--border);border-radius:8px;font-size:.82rem;font-family:inherit;outline:none;}
.filter-bar input:focus,.filter-bar select:focus{border-color:var(--green);}
.filter-bar button{padding:.5rem .9rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.msg{background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:9px;padding:.7rem 1.1rem;font-size:.82rem;margin-bottom:1rem;}
.card{background:var(--white);border-radius:16px;border:1px solid var(--border);overflow:hidden;}
.card-hd{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);}
.card-hd h3{font-size:.9rem;font-weight:600;}
.tbl-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;font-size:.81rem;}
th{padding:.65rem 1rem;text-align:left;font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray);background:var(--bg);}
td{padding:.72rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border:none;}
tr:hover td{background:#fafcfb;}
.p-name{font-weight:500;}
.p-sub{font-size:.72rem;color:var(--gray);}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.b-open{background:#fef3c7;color:#92400e;}
.b-processing{background:var(--blue-bg);color:var(--blue);}
.b-released{background:var(--green-light);color:var(--green);}
.b-rejected{background:var(--red-bg);color:var(--red);}
.b-archived{background:#f3f4f6;color:#374151;}
.btn-xs{padding:.28rem .65rem;border-radius:6px;font-size:.72rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;}
.bx-edit{background:var(--blue-bg);color:var(--blue);}
/* MODAL */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center;}
.overlay.open{display:flex;}
.modal{background:#fff;border-radius:20px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.modal h3{font-size:1.05rem;font-weight:700;margin-bottom:1.25rem;}
.modal label{display:block;font-size:.72rem;font-weight:600;color:var(--text);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em;}
.modal select,.modal textarea,.modal input{width:100%;padding:.65rem .9rem;border:1.5px solid var(--border);border-radius:9px;font-size:.86rem;font-family:inherit;outline:none;margin-bottom:.85rem;}
.modal select:focus,.modal textarea:focus{border-color:var(--green);}
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
    <a href="records.php"      class="sb-item active"><span class="ic">📋</span>Client Records</a>
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
    <h1>Client Records</h1>
    <button class="btn-primary btn-gold" onclick="document.getElementById('newModal').classList.add('open')">+ New Record</button>
  </div>

  <?php if($msg): ?><div class="msg">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <form method="GET" class="filter-bar">
    <input type="text" name="q" placeholder="Search name or details..." value="<?= htmlspecialchars($fsearch) ?>" style="min-width:200px">
    <select name="status">
      <option value="">All Status</option>
      <?php foreach($rstatuses as $st): ?><option <?=$fstatus===$st?'selected':''?> value="<?=$st?>"><?=ucfirst($st)?></option><?php endforeach; ?>
    </select>
    <select name="type">
      <option value="">All Types</option>
      <?php foreach($rtypes as $rt): ?><option <?=$ftype===$rt?'selected':''?> value="<?=$rt?>"><?=ucwords(str_replace('_',' ',$rt))?></option><?php endforeach; ?>
    </select>
    <button type="submit">Filter</button>
    <a href="records.php" style="font-size:.8rem;color:var(--gray);text-decoration:none;">Reset</a>
  </form>

  <div class="card">
    <div class="card-hd"><h3>Client Records</h3><span style="font-size:.78rem;color:var(--gray)"><?=$records->num_rows?> records</span></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>ID</th><th>Resident</th><th>Type</th><th>Service</th><th>Details</th><th>Docs Needed</th><th>Outcome</th><th>Status</th><th>Recorded</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($rec=$records->fetch_assoc()):
          $bmap=['open'=>'b-open','processing'=>'b-processing','released'=>'b-released','rejected'=>'b-rejected','archived'=>'b-archived'];
        ?>
        <tr>
          <td style="font-weight:600;color:var(--green)">#<?=$rec['record_id']?></td>
          <td>
            <div class="p-name"><?=htmlspecialchars($rec['first_name'].' '.$rec['last_name'])?></div>
            <div class="p-sub"><?=htmlspecialchars($rec['purok']??'')?></div>
          </td>
          <td><span class="p-sub"><?=ucwords(str_replace('_',' ',$rec['record_type']))?></span></td>
          <td class="p-sub"><?=htmlspecialchars($rec['service_name']??'—')?></td>
          <td class="p-sub" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?=htmlspecialchars($rec['details']??'')?>"><?=htmlspecialchars(substr($rec['details']??'—',0,35))?></td>
          <td class="p-sub" style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars(substr($rec['documents_needed']??'—',0,25))?></td>
          <td class="p-sub" style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars(substr($rec['outcome']??'—',0,25))?></td>
          <td><span class="badge <?=$bmap[$rec['status']]??'b-open'?>"><?=ucfirst($rec['status'])?></span></td>
          <td class="p-sub"><?=htmlspecialchars($rec['recorder']??'—')?><br><?=date('M j',strtotime($rec['created_at']))?></td>
          <td><button class="btn-xs bx-edit" onclick="openUpdate(<?=$rec['record_id']?>,'<?=$rec['status']?>','<?=addslashes($rec['outcome']??'')?>','<?=addslashes($rec['documents_submitted']??'')?>')">Update</button></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- NEW RECORD MODAL -->
<div class="overlay" id="newModal">
  <div class="modal">
    <h3>New Client Record</h3>
    <form method="POST">
      <input type="hidden" name="save_record" value="1">
      <label>Resident</label>
      <select name="resident_id" required>
        <option value="">— Select resident —</option>
        <?php $residents->data_seek(0); while($rr=$residents->fetch_assoc()): ?>
        <option value="<?=$rr['resident_id']?>"><?=htmlspecialchars($rr['last_name'].', '.$rr['first_name'].' ('.$rr['phone'].')')?></option>
        <?php endwhile; ?>
      </select>
      <div class="row2">
        <div>
          <label>Service</label>
          <select name="service_id">
            <option value="">— Select —</option>
            <?php $services->data_seek(0); while($sv=$services->fetch_assoc()): ?>
            <option value="<?=$sv['service_id']?>"><?=htmlspecialchars($sv['service_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label>Record Type</label>
          <select name="record_type" required>
            <?php foreach($rtypes as $rt): ?><option value="<?=$rt?>"><?=ucwords(str_replace('_',' ',$rt))?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <label>Details / Description</label>
      <textarea name="details" rows="3" placeholder="Describe the nature of the transaction..."></textarea>
      <label>Documents Needed</label>
      <textarea name="documents_needed" rows="2" placeholder="List required documents..."></textarea>
      <label>Documents Submitted</label>
      <textarea name="documents_submitted" rows="2" placeholder="Documents already submitted by client..."></textarea>
      <label>Initial Outcome / Remarks</label>
      <textarea name="outcome" rows="2" placeholder="Initial remarks or outcome..."></textarea>
      <div class="row2">
        <div>
          <label>Status</label>
          <select name="status">
            <?php foreach($rstatuses as $st): ?><option value="<?=$st?>"><?=ucfirst($st)?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('newModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Save Record</button>
      </div>
    </form>
  </div>
</div>

<!-- UPDATE RECORD MODAL -->
<div class="overlay" id="updateModal">
  <div class="modal">
    <h3>Update Client Record</h3>
    <form method="POST">
      <input type="hidden" name="update_record" value="1">
      <input type="hidden" name="record_id" id="upd_id">
      <label>Documents Submitted</label>
      <textarea name="documents_submitted" id="upd_ds" rows="2"></textarea>
      <label>Outcome / Resolution</label>
      <textarea name="outcome" id="upd_outcome" rows="3" placeholder="Describe what happened / was released..."></textarea>
      <label>Status</label>
      <select name="status" id="upd_status">
        <?php foreach($rstatuses as $st): ?><option value="<?=$st?>"><?=ucfirst($st)?></option><?php endforeach; ?>
      </select>
      <div class="modal-btns">
        <button type="button" class="modal-cancel" onclick="document.getElementById('updateModal').classList.remove('open')">Cancel</button>
        <button type="submit" class="modal-save">Update Record</button>
      </div>
    </form>
  </div>
</div>

<script>
function openUpdate(id, status, outcome, ds) {
  document.getElementById('upd_id').value     = id;
  document.getElementById('upd_status').value = status;
  document.getElementById('upd_outcome').value = outcome;
  document.getElementById('upd_ds').value     = ds;
  document.getElementById('updateModal').classList.add('open');
}
document.querySelectorAll('.overlay').forEach(o => {
  o.addEventListener('click', e => { if(e.target===o) o.classList.remove('open'); });
});
</script>
</body>
</html>
