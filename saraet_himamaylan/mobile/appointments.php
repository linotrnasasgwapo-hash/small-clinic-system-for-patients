<?php
// mobile/appointments.php
require_once '../includes/auth.php';
requireResident();
require_once '../config/db.php';
$db  = getDB();
$rid = $_SESSION['resident_id'];

if (isset($_POST['cancel_id'])) {
    $cid = (int)$_POST['cancel_id'];
    $db->query("UPDATE appointments SET status='cancelled' WHERE appt_id=$cid AND resident_id=$rid");
    header('Location: appointments.php?msg=cancelled'); exit;
}

$f   = $_GET['filter']??'upcoming';
$sql = "SELECT a.*,s.service_name FROM appointments a LEFT JOIN services s ON a.service_id=s.service_id WHERE a.resident_id=$rid";
if ($f==='upcoming') $sql.=" AND a.appointment_date>=CURDATE() AND a.status NOT IN ('done','cancelled') ORDER BY a.appointment_date,a.appointment_time";
elseif ($f==='past') $sql.=" AND (a.appointment_date<CURDATE() OR a.status IN ('done','cancelled')) ORDER BY a.appointment_date DESC";
else $sql.=" ORDER BY a.appointment_date DESC,a.appointment_time DESC";
$rows = $db->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>My Appointments — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-light:#e6f4ec;--border:#dce8e2;--bg:#f5f8f6;--text:#1a2e23;--gray:#5a6a62;--red:#dc2626;--amber:#d97706;--amber-bg:#fef3c7;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);max-width:430px;margin:0 auto;padding-bottom:84px;}
.top-bar{background:var(--green);padding:1rem 1.25rem;display:flex;align-items:center;gap:.75rem;}
.back{color:rgba(255,255,255,.8);font-size:1.15rem;text-decoration:none;}
.top-bar h1{font-size:1rem;font-weight:800;color:#fff;}
.pad{padding:1rem;}
.tabs{display:flex;background:#fff;border-radius:12px;padding:.28rem;gap:.22rem;margin-bottom:.95rem;border:1px solid var(--border);}
.tab{flex:1;text-align:center;padding:.52rem;border-radius:9px;font-size:.76rem;font-weight:700;cursor:pointer;color:var(--gray);text-decoration:none;transition:background .15s;}
.tab.on{background:var(--green);color:#fff;}
.appt-card{background:#fff;border-radius:14px;padding:1.1rem;border:1px solid var(--border);margin-bottom:.7rem;}
.ac-hd{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.75rem;}
.ac-id{font-size:.68rem;color:var(--gray);}
.ac-svc{font-size:.92rem;font-weight:800;color:var(--text);margin-top:.12rem;}
.badge{padding:.22rem .65rem;border-radius:20px;font-size:.67rem;font-weight:800;white-space:nowrap;}
.b-p{background:#f3f4f6;color:#374151;} .b-c{background:var(--green-light);color:var(--green);}
.b-q{background:#ede9fe;color:#7c3aed;} .b-d{background:#eff6ff;color:#1d4ed8;}
.b-cn{background:#fef2f2;color:var(--red);} .b-s{background:var(--green);color:#fff;}
.meta{display:grid;grid-template-columns:1fr 1fr;gap:.4rem;}
.mi{font-size:.76rem;color:var(--gray);}
.mi strong{color:var(--text);display:block;font-weight:700;}
.q-chip{display:inline-flex;align-items:center;gap:.28rem;background:var(--green-light);color:var(--green);padding:.18rem .58rem;border-radius:7px;font-size:.69rem;font-weight:800;margin-top:.5rem;}
.purp{font-size:.76rem;color:var(--gray);margin-top:.55rem;padding-top:.55rem;border-top:1px solid var(--border);}
.cancel-row{margin-top:.65rem;padding-top:.65rem;border-top:1px solid var(--border);}
.btn-can{background:#fef2f2;color:var(--red);border:1px solid #fca5a5;border-radius:9px;padding:.42rem .88rem;font-size:.76rem;font-weight:700;font-family:inherit;cursor:pointer;}
.empty{text-align:center;padding:3rem 2rem;color:var(--gray);}
.empty div{font-size:3rem;margin-bottom:.75rem;}
.btn-bk{display:inline-block;padding:.75rem 1.5rem;background:var(--green);color:#fff;border-radius:12px;font-weight:800;font-size:.85rem;text-decoration:none;}
.msg{background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:10px;padding:.65rem .9rem;font-size:.8rem;margin-bottom:.75rem;}
.bnav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;background:#fff;border-top:1px solid var(--border);display:flex;padding:.55rem 0;}
.ni{flex:1;display:flex;flex-direction:column;align-items:center;gap:.18rem;font-size:.6rem;font-weight:700;color:var(--gray);text-decoration:none;}
.ni.on{color:var(--green);}
.ni .ic{font-size:1.25rem;}
</style>
</head>
<body>
<div class="top-bar"><a href="home.php" class="back">←</a><h1>My Appointments</h1></div>
<div class="pad">
  <?php if(isset($_GET['msg']) && $_GET['msg']==='cancelled'):?><div class="msg">Appointment cancelled successfully.</div><?php endif;?>
  <div class="tabs">
    <a href="?filter=upcoming" class="tab <?=$f==='upcoming'?'on':''?>">Upcoming</a>
    <a href="?filter=past"     class="tab <?=$f==='past'?'on':''?>">Past</a>
    <a href="?filter=all"      class="tab <?=$f==='all'?'on':''?>">All</a>
  </div>
  <?php $cnt=0; while($a=$rows->fetch_assoc()): $cnt++;
    $bm=['pending'=>'b-p','confirmed'=>'b-c','in_queue'=>'b-q','serving'=>'b-s','done'=>'b-d','cancelled'=>'b-cn','no_show'=>'b-cn'];
  ?>
  <div class="appt-card">
    <div class="ac-hd">
      <div><div class="ac-id">Appointment #<?=$a['appt_id']?></div><div class="ac-svc"><?=htmlspecialchars($a['service_name']??'General Consultation')?></div></div>
      <span class="badge <?=$bm[$a['status']]??'b-p'?>"><?=ucfirst(str_replace('_',' ',$a['status']))?></span>
    </div>
    <div class="meta">
      <div class="mi"><strong>📅 <?=date('M j, Y',strtotime($a['appointment_date']))?></strong>Date</div>
      <div class="mi"><strong>🕐 <?=date('h:i A',strtotime($a['appointment_time']))?></strong>Time</div>
    </div>
    <div class="q-chip">🔢 Queue #<?=str_pad($a['queue_number'],3,'0',STR_PAD_LEFT)?></div>
    <?php if($a['purpose']):?><div class="purp">📝 <?=htmlspecialchars($a['purpose'])?></div><?php endif;?>
    <?php if(in_array($a['status'],['pending','confirmed']) && $a['appointment_date']>=date('Y-m-d')):?>
    <div class="cancel-row">
      <form method="POST">
        <input type="hidden" name="cancel_id" value="<?=$a['appt_id']?>">
        <button class="btn-can" type="submit" onclick="return confirm('Cancel this appointment?')">Cancel Appointment</button>
      </form>
    </div>
    <?php endif;?>
  </div>
  <?php endwhile;?>
  <?php if($cnt===0):?>
  <div class="empty"><div>📋</div><p style="margin-bottom:1rem">No <?=$f?> appointments found.</p><a class="btn-bk" href="book.php">Book an Appointment</a></div>
  <?php endif;?>
</div>
<nav class="bnav">
  <a href="home.php" class="ni"><span class="ic">🏠</span>Home</a>
  <a href="book.php" class="ni"><span class="ic">📅</span>Book</a>
  <a href="queue_status.php" class="ni"><span class="ic">🔢</span>Queue</a>
  <a href="appointments.php" class="ni on"><span class="ic">📋</span>My Appts</a>
  <a href="profile.php" class="ni"><span class="ic">👤</span>Profile</a>
</nav>
</body>
</html>
