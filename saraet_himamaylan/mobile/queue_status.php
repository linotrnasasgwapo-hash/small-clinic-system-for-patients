<?php
// mobile/queue_status.php
require_once '../includes/auth.php';
requireResident();
require_once '../config/db.php';
$db  = getDB();
$rid = $_SESSION['resident_id'];

$qr = $db->prepare("
    SELECT q.*, s.service_name, a.appointment_date, a.appointment_time,
        (SELECT COUNT(*) FROM queue WHERE queue_date=CURDATE() AND status='waiting' AND queue_number<q.queue_number) ahead,
        (SELECT queue_number FROM queue WHERE queue_date=CURDATE() AND status='serving' LIMIT 1) now_srv,
        (SELECT COUNT(*) FROM queue WHERE queue_date=CURDATE() AND status='waiting') total_w
    FROM queue q JOIN appointments a ON q.appt_id=a.appt_id
    LEFT JOIN services s ON q.service_id=s.service_id
    WHERE a.resident_id=? AND q.queue_date=CURDATE() AND q.status IN ('waiting','serving')
    LIMIT 1
");
$qr->bind_param('i',$rid); $qr->execute();
$myq = $qr->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Queue Status — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-light:#e6f4ec;--border:#dce8e2;--bg:#f5f8f6;--text:#1a2e23;--gray:#5a6a62;--amber:#d97706;--amber-bg:#fef3c7;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);max-width:430px;margin:0 auto;padding-bottom:84px;}
.top-bar{background:var(--green);padding:1rem 1.25rem;display:flex;align-items:center;gap:.75rem;}
.back{color:rgba(255,255,255,.8);font-size:1.15rem;text-decoration:none;}
.top-bar h1{font-size:1rem;font-weight:800;color:#fff;}
.pad{padding:1.1rem 1rem;}
.live-chip{display:inline-flex;align-items:center;gap:.4rem;background:var(--green-light);color:var(--green);padding:.3rem .85rem;border-radius:20px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin-bottom:1rem;}
.dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:bl 1.4s infinite;}
@keyframes bl{0%,100%{opacity:1}50%{opacity:.2}}

.srv-bar{background:var(--green);border-radius:14px;padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem;margin-bottom:1.2rem;}
.srv-num{font-size:2rem;font-weight:900;color:#fff;min-width:60px;text-align:center;}
.srv-info p{color:rgba(255,255,255,.7);font-size:.72rem;}
.srv-info h4{color:#fff;font-size:.88rem;font-weight:700;}

.q-card{background:#fff;border-radius:20px;padding:2rem 1.5rem;border:1px solid var(--border);text-align:center;margin-bottom:1.2rem;position:relative;overflow:hidden;}
.q-card.live{border-color:var(--green);background:linear-gradient(135deg,#edfaf4,#e0f5ec);}
.q-card::before{content:'';position:absolute;top:-50px;right:-50px;width:140px;height:140px;border-radius:50%;background:var(--green-light);opacity:.5;}
.my-lbl{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--gray);margin-bottom:.5rem;}
.my-num{font-size:5rem;font-weight:900;color:var(--green);line-height:1;letter-spacing:-.05em;}
.st-pill{display:inline-block;margin:.7rem 0 .4rem;padding:.38rem 1rem;border-radius:20px;font-size:.76rem;font-weight:800;}
.sp-wait{background:var(--amber-bg);color:var(--amber);}
.sp-srv{background:var(--green);color:#fff;}
.svc-tag{font-size:.8rem;color:var(--gray);}
.stats-row{display:flex;justify-content:space-around;padding:.95rem 0;border-top:1px solid var(--border);margin-top:.95rem;}
.st-box .v{font-size:1.5rem;font-weight:900;color:var(--text);}
.st-box .l{font-size:.65rem;color:var(--gray);font-weight:600;}

.prog-wrap{margin:0 0 1.2rem;}
.prog-lbl{display:flex;justify-content:space-between;font-size:.74rem;color:var(--gray);margin-bottom:.45rem;}
.prog-bg{background:#e5e7eb;border-radius:8px;height:9px;}
.prog-fill{background:var(--green);height:9px;border-radius:8px;transition:width 1s ease;}

.tips{background:#fff;border-radius:14px;padding:1rem 1.1rem;border:1px solid var(--border);}
.tips h4{font-size:.78rem;font-weight:800;margin-bottom:.65rem;}
.tip{display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.5rem;font-size:.76rem;color:var(--gray);}
.ti{font-size:.9rem;flex-shrink:0;}

.no-q{background:#fff;border-radius:20px;padding:2.5rem;text-align:center;border:1px solid var(--border);}
.no-q h3{font-size:.95rem;font-weight:800;margin:.9rem 0 .45rem;}
.no-q p{font-size:.82rem;color:var(--gray);margin-bottom:1.1rem;}
.btn-bk{display:inline-block;padding:.78rem 1.75rem;background:var(--green);color:#fff;border-radius:12px;font-weight:800;font-size:.88rem;text-decoration:none;}
.refresh{text-align:center;font-size:.7rem;color:var(--gray);margin-top:1rem;}
.bnav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;background:#fff;border-top:1px solid var(--border);display:flex;padding:.55rem 0;}
.ni{flex:1;display:flex;flex-direction:column;align-items:center;gap:.18rem;font-size:.6rem;font-weight:700;color:var(--gray);text-decoration:none;}
.ni.on{color:var(--green);}
.ni .ic{font-size:1.25rem;}
</style>
</head>
<body>
<div class="top-bar"><a href="home.php" class="back">←</a><h1>Queue Status</h1></div>
<div class="pad">
  <div class="live-chip"><div class="dot"></div>Auto-refresh every 30s</div>

  <?php if($myq):
    $ahead = (int)$myq['ahead'];
    $totw  = (int)$myq['total_w'];
    $pct   = $totw>0 ? max(5,min(95, round(($totw-$ahead)/$totw*100))) : 100;
    $est   = max(0,$ahead*15);
  ?>
  <?php if($myq['now_srv']):?>
  <div class="srv-bar">
    <div class="srv-num"><?=str_pad($myq['now_srv'],3,'0',STR_PAD_LEFT)?></div>
    <div class="srv-info"><p>Now serving</p><h4>Please be ready if yours is close</h4></div>
  </div>
  <?php endif;?>

  <div class="q-card live">
    <div class="my-lbl">Your Queue Number</div>
    <div class="my-num"><?=str_pad($myq['queue_number'],3,'0',STR_PAD_LEFT)?></div>
    <div class="st-pill <?=$myq['status']==='serving'?'sp-srv':'sp-wait'?>">
      <?=$myq['status']==='serving'?'🟢 Now Being Served':'⏳ Waiting'?>
    </div>
    <div class="svc-tag">📋 <?=htmlspecialchars($myq['service_name']??'General')?></div>
    <div class="stats-row">
      <div class="st-box"><div class="v"><?=$ahead?></div><div class="l">Ahead</div></div>
      <div class="st-box"><div class="v"><?=$est?>m</div><div class="l">Est. wait</div></div>
      <div class="st-box"><div class="v"><?=$totw?></div><div class="l">Waiting</div></div>
    </div>
  </div>

  <div class="prog-wrap">
    <div class="prog-lbl"><span>Queue progress</span><span><?=$pct?>% through</span></div>
    <div class="prog-bg"><div class="prog-fill" style="width:<?=$pct?>%"></div></div>
  </div>

  <div class="tips">
    <h4>💡 While You Wait</h4>
    <div class="tip"><span class="ti">📱</span>This page refreshes automatically every 30 seconds.</div>
    <div class="tip"><span class="ti">🏛️</span>Please be at the waiting area when you're 3 numbers away.</div>
    <div class="tip"><span class="ti">📄</span>Bring a valid ID and required documents for your service.</div>
    <div class="tip"><span class="ti">🔔</span>You'll receive a notification when your turn is near.</div>
  </div>

  <?php else:?>
  <div class="no-q">
    <div style="font-size:3rem">📋</div>
    <h3>No Active Queue Today</h3>
    <p>Book an appointment to receive a queue number and track your position.</p>
    <a class="btn-bk" href="book.php">Book Now →</a>
  </div>
  <?php endif;?>

  <div class="refresh">Refreshing in <span id="cd">30</span>s</div>
</div>

<nav class="bnav">
  <a href="home.php" class="ni"><span class="ic">🏠</span>Home</a>
  <a href="book.php" class="ni"><span class="ic">📅</span>Book</a>
  <a href="queue_status.php" class="ni on"><span class="ic">🔢</span>Queue</a>
  <a href="appointments.php" class="ni"><span class="ic">📋</span>My Appts</a>
  <a href="profile.php" class="ni"><span class="ic">👤</span>Profile</a>
</nav>
<script>
let c=30,el=document.getElementById('cd');
setInterval(()=>{c--;if(el)el.textContent=c;if(c<=0)location.reload();},1000);
</script>
</body>
</html>
