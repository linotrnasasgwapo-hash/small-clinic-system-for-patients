<?php
// admin/queue.php — Full-screen live queue display board
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db    = getDB();
$today = date('Y-m-d');

$serving = $db->query("SELECT q.*,r.first_name,r.last_name,s.service_name FROM queue q JOIN appointments a ON q.appt_id=a.appt_id JOIN residents r ON a.resident_id=r.resident_id LEFT JOIN services s ON q.service_id=s.service_id WHERE q.queue_date='$today' AND q.status='serving' ORDER BY q.queue_number LIMIT 3");
$waiting = $db->query("SELECT q.*,r.first_name,r.last_name,s.service_name FROM queue q JOIN appointments a ON q.appt_id=a.appt_id JOIN residents r ON a.resident_id=r.resident_id LEFT JOIN services s ON q.service_id=s.service_id WHERE q.queue_date='$today' AND q.status='waiting' ORDER BY q.queue_number LIMIT 8");
$done_count = $db->query("SELECT COUNT(*) FROM queue WHERE queue_date='$today' AND status='done'")->fetch_row()[0];
$wait_count = $db->query("SELECT COUNT(*) FROM queue WHERE queue_date='$today' AND status='waiting'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Live Queue Display — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--gold:#c9933a;--text:#1a2e23;--gray:#5a6a62;--border:#dce8e2;}
body{font-family:'DM Sans',sans-serif;background:#0f1f16;color:#fff;min-height:100vh;display:flex;flex-direction:column;}
.q-header{background:var(--green2);padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid var(--gold);}
.q-header h1{font-family:'Playfair Display',serif;font-size:1.4rem;color:#fff;}
.q-header p{font-size:.78rem;color:rgba(255,255,255,.6);}
.hd-right{display:flex;align-items:center;gap:1.5rem;}
.live-badge{display:flex;align-items:center;gap:.4rem;background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);padding:.35rem .9rem;border-radius:20px;font-size:.78rem;font-weight:700;color:#6ee7b7;}
.dot{width:7px;height:7px;border-radius:50%;background:#10b981;animation:bl 1.4s infinite;}
@keyframes bl{0%,100%{opacity:1}50%{opacity:.2}}
.clock{font-size:1.2rem;font-weight:700;color:var(--gold);}
.q-body{flex:1;display:grid;grid-template-columns:1fr 1fr;gap:2rem;padding:2rem;max-width:1200px;margin:0 auto;width:100%;}

/* NOW SERVING */
.serving-panel h2{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--gold);margin-bottom:1rem;}
.serving-card{background:linear-gradient(135deg,#1a5c3a,#2a7d50);border:2px solid #6ee7b7;border-radius:20px;padding:2rem;text-align:center;margin-bottom:1rem;position:relative;overflow:hidden;}
.serving-card::before{content:'';position:absolute;top:-40px;right:-40px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.04);}
.sc-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.6);margin-bottom:.5rem;}
.sc-number{font-size:5rem;font-weight:900;color:#fff;line-height:1;letter-spacing:-.04em;}
.sc-name{font-size:1rem;font-weight:600;color:rgba(255,255,255,.85);margin-top:.5rem;}
.sc-service{font-size:.78rem;color:rgba(255,255,255,.55);margin-top:.2rem;}
.no-serving{background:rgba(255,255,255,.04);border:1px dashed rgba(255,255,255,.15);border-radius:16px;padding:3rem;text-align:center;color:rgba(255,255,255,.35);font-size:.88rem;}

/* WAITING LIST */
.waiting-panel h2{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.5);margin-bottom:1rem;}
.q-row{display:flex;align-items:center;gap:1rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:.85rem 1.1rem;margin-bottom:.65rem;transition:background .2s;}
.q-row:hover{background:rgba(255,255,255,.08);}
.qr-num{font-size:1.5rem;font-weight:900;color:var(--gold);min-width:50px;text-align:center;}
.qr-info p{font-weight:600;font-size:.88rem;}
.qr-info span{font-size:.72rem;color:rgba(255,255,255,.5);}
.qr-tag{margin-left:auto;padding:.2rem .65rem;background:rgba(201,147,58,.15);border:1px solid rgba(201,147,58,.3);border-radius:20px;font-size:.68rem;font-weight:700;color:#f5c97a;white-space:nowrap;}

/* STATS STRIP */
.stats-strip{background:rgba(255,255,255,.04);border-top:1px solid rgba(255,255,255,.08);padding:1rem 2rem;display:flex;justify-content:space-around;}
.ss-item{text-align:center;}
.ss-item .v{font-size:1.5rem;font-weight:900;color:var(--gold);}
.ss-item .l{font-size:.68rem;color:rgba(255,255,255,.5);font-weight:600;text-transform:uppercase;letter-spacing:.06em;}

.back-link{display:inline-flex;align-items:center;gap:.4rem;color:rgba(255,255,255,.6);font-size:.78rem;text-decoration:none;padding:.4rem .85rem;background:rgba(255,255,255,.06);border-radius:8px;}
.back-link:hover{color:#fff;background:rgba(255,255,255,.1);}
</style>
</head>
<body>
<div class="q-header">
  <div>
    <h1>🏛️ Barangay Saraet Service Center</h1>
    <p>Himamaylan City — Live Queue Display</p>
  </div>
  <div class="hd-right">
    <div class="live-badge"><div class="dot"></div>LIVE</div>
    <div class="clock" id="clock">--:-- --</div>
    <a href="dashboard.php" class="back-link">← Dashboard</a>
  </div>
</div>

<div class="q-body">
  <!-- NOW SERVING -->
  <div class="serving-panel">
    <h2>🔊 Now Serving</h2>
    <?php
    $any_serving = false;
    while($sv=$serving->fetch_assoc()):
      $any_serving = true;
    ?>
    <div class="serving-card">
      <div class="sc-label">Counter <?= $sv['queue_id'] % 2 + 1 ?></div>
      <div class="sc-number"><?= str_pad($sv['queue_number'],3,'0',STR_PAD_LEFT) ?></div>
      <div class="sc-name"><?= htmlspecialchars($sv['first_name'].' '.$sv['last_name']) ?></div>
      <div class="sc-service"><?= htmlspecialchars($sv['service_name']??'General') ?></div>
    </div>
    <?php endwhile; ?>
    <?php if(!$any_serving): ?>
    <div class="no-serving">No one is being served right now</div>
    <?php endif; ?>
  </div>

  <!-- WAITING -->
  <div class="waiting-panel">
    <h2>⏳ Up Next (<?= $wait_count ?> waiting)</h2>
    <?php while($wq=$waiting->fetch_assoc()): ?>
    <div class="q-row">
      <div class="qr-num"><?= str_pad($wq['queue_number'],3,'0',STR_PAD_LEFT) ?></div>
      <div class="qr-info">
        <p><?= htmlspecialchars($wq['first_name'].' '.$wq['last_name']) ?></p>
        <span><?= htmlspecialchars($wq['service_name']??'General') ?></span>
      </div>
      <div class="qr-tag">Waiting</div>
    </div>
    <?php endwhile; ?>
    <?php if($wait_count===0): ?>
    <div style="text-align:center;padding:2rem;color:rgba(255,255,255,.3);font-size:.88rem">Queue is empty</div>
    <?php endif; ?>
  </div>
</div>

<div class="stats-strip">
  <div class="ss-item"><div class="v"><?= $done_count ?></div><div class="l">Served today</div></div>
  <div class="ss-item"><div class="v"><?= $wait_count ?></div><div class="l">Still waiting</div></div>
  <div class="ss-item"><div class="v"><?= date('M j, Y') ?></div><div class="l">Today's date</div></div>
  <div class="ss-item"><div class="v" id="live-time">—</div><div class="l">Current time</div></div>
</div>

<script>
function tick() {
  const now = new Date();
  const t = now.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
  document.getElementById('clock').textContent = t;
  document.getElementById('live-time').textContent = now.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
}
tick();
setInterval(tick, 1000);
// Auto-refresh every 15s
setTimeout(() => location.reload(), 15000);
</script>
</body>
</html>
