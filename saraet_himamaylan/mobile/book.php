<?php
// mobile/book.php
require_once '../includes/auth.php';
requireResident();
require_once '../config/db.php';
$db  = getDB();
$rid = $_SESSION['resident_id'];
$success_data = null; $error = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $sid   = (int)$_POST['service_id'];
    $schid = (int)$_POST['schedule_id'];
    $purp  = clean($db,$_POST['purpose']??'');
    $sr    = $db->prepare("SELECT * FROM schedules WHERE schedule_id=? AND is_available=1 AND booked_slots<max_slots LIMIT 1");
    $sr->bind_param('i',$schid); $sr->execute();
    $sch   = $sr->get_result()->fetch_assoc();
    if (!$sch) { $error='Schedule no longer available.'; }
    else {
        $sd   = $sch['sched_date'];
        $r2   = $db->query("SELECT COALESCE(MAX(queue_number),0)+1 n FROM appointments WHERE appointment_date='$sd'");
        $qn   = $r2->fetch_row()[0];
        $stmt = $db->prepare("INSERT INTO appointments (resident_id,service_id,schedule_id,queue_number,appointment_date,appointment_time,purpose,status) VALUES(?,?,?,?,?,?,'$purp','confirmed')");
        $stmt->bind_param('iiiiss',$rid,$sid,$schid,$qn,$sd,$sch['start_time']);
        $stmt->execute();
        $aid = $db->insert_id;
        $q2  = $db->prepare("INSERT INTO queue (appt_id,queue_number,queue_date,service_id,status) VALUES(?,?,?,?,'waiting')");
        $q2->bind_param('iisi',$aid,$qn,$sd,$sid);
        $q2->execute();
        $db->query("UPDATE schedules SET booked_slots=booked_slots+1 WHERE schedule_id=$schid");
        $ins = $db->prepare("INSERT INTO notifications (resident_id,title,message) VALUES(?,?,?)");
        $t = 'Appointment Confirmed';
        $m = "Your appointment #$aid has been booked. Queue #$qn on $sd. Service: ".($db->query("SELECT service_name FROM services WHERE service_id=$sid")->fetch_row()[0]);
        $ins->bind_param('iss',$rid,$t,$m); $ins->execute();
        $success_data = ['appt_id'=>$aid,'queue'=>$qn,'date'=>$sd,'time'=>$sch['start_time']];
    }
}

$presvc = (int)($_GET['service_id']??0);
$svcs   = $db->query("SELECT * FROM services WHERE is_active=1 ORDER BY service_name");
$scheds = null;
if ($presvc) {
    $ss = $db->prepare("SELECT sc.*,s.service_name FROM schedules sc JOIN services s ON sc.service_id=s.service_id WHERE sc.service_id=? AND sc.sched_date>=CURDATE() AND sc.is_available=1 AND sc.booked_slots<sc.max_slots ORDER BY sc.sched_date,sc.start_time");
    $ss->bind_param('i',$presvc); $ss->execute();
    $scheds = $ss->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Book Appointment — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-light:#e6f4ec;--border:#dce8e2;--bg:#f5f8f6;--text:#1a2e23;--gray:#5a6a62;--red:#dc2626;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);max-width:430px;margin:0 auto;padding-bottom:84px;}
.top-bar{background:var(--green);padding:1rem 1.25rem;display:flex;align-items:center;gap:.75rem;}
.back{color:rgba(255,255,255,.8);font-size:1.15rem;text-decoration:none;}
.top-bar h1{font-size:1rem;font-weight:800;color:#fff;}
.pad{padding:1.25rem 1rem;}
label{display:block;font-size:.72rem;font-weight:800;color:var(--text);margin-bottom:.38rem;text-transform:uppercase;letter-spacing:.05em;}
select,textarea{width:100%;padding:.78rem .95rem;border:1.5px solid var(--border);border-radius:11px;font-size:.9rem;font-family:inherit;outline:none;transition:border-color .2s;background:#fff;}
select:focus,textarea:focus{border-color:var(--green);}
textarea{resize:vertical;min-height:72px;}
.field{margin-bottom:1.05rem;}
.sched-grid{display:grid;gap:.6rem;}
.sched-item{background:#fff;border:1.5px solid var(--border);border-radius:13px;padding:.95rem;cursor:pointer;transition:border-color .2s;}
.sched-item:has(input:checked),.sched-item.sel{border-color:var(--green);background:var(--green-light);}
.sched-item input{display:none;}
.sd{font-weight:700;font-size:.88rem;}
.st{font-size:.76rem;color:var(--gray);margin-top:.18rem;}
.ss{display:inline-block;margin-top:.4rem;background:var(--green-light);color:var(--green);padding:.18rem .6rem;border-radius:20px;font-size:.68rem;font-weight:800;}
.btn{width:100%;padding:.9rem;background:var(--green);color:#fff;border:none;border-radius:13px;font-size:.98rem;font-weight:800;cursor:pointer;margin-top:.5rem;font-family:inherit;}
.btn:hover{background:var(--green2);}
.err{background:#fef2f2;color:var(--red);border:1px solid #fca5a5;border-radius:10px;padding:.7rem;font-size:.82rem;margin-bottom:1rem;}
/* SUCCESS */
.success-wrap{text-align:center;padding:.5rem;}
.big-check{font-size:3.5rem;margin-bottom:.75rem;}
.ticket{background:var(--green-light);border-radius:16px;padding:1.5rem;margin:1.25rem 0;border:1.5px dashed #6ee7b7;}
.ticket-q{font-size:4.5rem;font-weight:900;color:var(--green);line-height:1;letter-spacing:-.04em;}
.ticket-lbl{font-size:.72rem;color:var(--gray);}
.ticket-info{font-size:.82rem;color:var(--text);margin-top:.5rem;}
.btn-home{display:inline-block;padding:.82rem 2rem;background:var(--green);color:#fff;border-radius:13px;font-weight:800;font-size:.9rem;text-decoration:none;}
.bnav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;background:#fff;border-top:1px solid var(--border);display:flex;padding:.55rem 0;}
.ni{flex:1;display:flex;flex-direction:column;align-items:center;gap:.18rem;font-size:.6rem;font-weight:700;color:var(--gray);text-decoration:none;}
.ni.on{color:var(--green);}
.ni .ic{font-size:1.25rem;}
</style>
</head>
<body>
<div class="top-bar"><a href="home.php" class="back">←</a><h1>Book Appointment</h1></div>
<div class="pad">
<?php if($success_data): ?>
  <div class="success-wrap">
    <div class="big-check">✅</div>
    <h2 style="font-size:1.2rem;font-weight:800;color:#166534">Appointment Booked!</h2>
    <p style="color:var(--gray);font-size:.83rem;margin-top:.4rem">Your appointment has been confirmed.</p>
    <div class="ticket">
      <div class="ticket-lbl">Your Queue Number</div>
      <div class="ticket-q"><?=str_pad($success_data['queue'],3,'0',STR_PAD_LEFT)?></div>
      <div class="ticket-info">📅 <?=date('F j, Y',strtotime($success_data['date']))?></div>
      <div class="ticket-info">🕐 <?=date('h:i A',strtotime($success_data['time']))?></div>
      <div class="ticket-info">📌 Appt. #<?=$success_data['appt_id']?></div>
    </div>
    <p style="font-size:.76rem;color:var(--gray);margin-bottom:1.1rem">Please arrive 15 minutes before your scheduled time. Track your queue status live from the home screen.</p>
    <a class="btn-home" href="home.php">← Back to Home</a>
  </div>
<?php else: ?>
  <?php if($error):?><div class="err"><?=htmlspecialchars($error)?></div><?php endif;?>
  <form method="POST" id="bk">
    <div class="field">
      <label>1. Select Service</label>
      <select name="service_id" id="svcSel" onchange="loadSch(this.value)" required>
        <option value="">— Choose a service —</option>
        <?php while($sv=$svcs->fetch_assoc()):?>
        <option value="<?=$sv['service_id']?>" <?=$presvc==$sv['service_id']?'selected':''?>><?=htmlspecialchars($sv['service_name'])?></option>
        <?php endwhile;?>
      </select>
    </div>
    <div class="field" id="schSec" style="<?=$presvc?'':'display:none'?>">
      <label>2. Choose Schedule</label>
      <div class="sched-grid" id="schGrid">
        <?php if($presvc && $scheds): while($sc=$scheds->fetch_assoc()):?>
        <label class="sched-item">
          <input type="radio" name="schedule_id" value="<?=$sc['schedule_id']?>" required>
          <div class="sd">📅 <?=date('l, F j, Y',strtotime($sc['sched_date']))?></div>
          <div class="st">🕐 <?=date('h:i A',strtotime($sc['start_time']))?> – <?=date('h:i A',strtotime($sc['end_time']))?></div>
          <div class="ss"><?=$sc['max_slots']-$sc['booked_slots']?> slots left</div>
        </label>
        <?php endwhile; elseif($presvc):?><p style="color:var(--gray);font-size:.83rem">No available schedules.</p><?php endif;?>
      </div>
    </div>
    <div class="field" id="purpSec" style="<?=$presvc?'':'display:none'?>">
      <label>3. Purpose of Visit</label>
      <textarea name="purpose" placeholder="Briefly describe what you need..."></textarea>
    </div>
    <button class="btn" id="subBtn" style="<?=$presvc?'':'display:none'?>" type="submit">Confirm Booking →</button>
  </form>
<?php endif;?>
</div>
<nav class="bnav">
  <a href="home.php" class="ni"><span class="ic">🏠</span>Home</a>
  <a href="book.php" class="ni on"><span class="ic">📅</span>Book</a>
  <a href="queue_status.php" class="ni"><span class="ic">🔢</span>Queue</a>
  <a href="appointments.php" class="ni"><span class="ic">📋</span>My Appts</a>
  <a href="profile.php" class="ni"><span class="ic">👤</span>Profile</a>
</nav>
<script>
async function loadSch(sid) {
  if (!sid) return;
  ['schSec','purpSec','subBtn'].forEach(id => document.getElementById(id).style.display='block');
  const res = await fetch('get_schedules.php?service_id=' + sid);
  const data = await res.json();
  const g = document.getElementById('schGrid');
  if (!data.length) { g.innerHTML='<p style="color:#5a6a62;font-size:.83rem">No available schedules.</p>'; return; }
  g.innerHTML = data.map(s=>`
    <label class="sched-item" onclick="document.querySelectorAll('.sched-item').forEach(x=>x.classList.remove('sel'));this.classList.add('sel')">
      <input type="radio" name="schedule_id" value="${s.schedule_id}" required>
      <div class="sd">📅 ${s.date_fmt}</div>
      <div class="st">🕐 ${s.time_range}</div>
      <div class="ss">${s.slots_left} slots left</div>
    </label>`).join('');
}
</script>
</body>
</html>
