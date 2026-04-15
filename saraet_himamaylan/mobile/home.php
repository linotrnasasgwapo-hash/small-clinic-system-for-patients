<?php
// mobile/home.php
require_once '../includes/auth.php';
requireResident();
require_once '../config/db.php';
$db  = getDB();
$rid = $_SESSION['resident_id'];

$r   = $db->prepare("SELECT * FROM residents WHERE resident_id=?");
$r->bind_param('i',$rid); $r->execute();
$res = $r->get_result()->fetch_assoc();

// Active queue today
$qr = $db->prepare("
    SELECT q.queue_number, q.status, s.service_name,
        (SELECT COUNT(*) FROM queue WHERE queue_date=CURDATE() AND status='waiting' AND queue_number<q.queue_number) ahead,
        (SELECT queue_number FROM queue WHERE queue_date=CURDATE() AND status='serving' LIMIT 1) now_serving
    FROM queue q JOIN appointments a ON q.appt_id=a.appt_id
    LEFT JOIN services s ON q.service_id=s.service_id
    WHERE a.resident_id=? AND q.queue_date=CURDATE() AND q.status IN ('waiting','serving')
    LIMIT 1
");
$qr->bind_param('i',$rid); $qr->execute();
$myq = $qr->get_result()->fetch_assoc();

// Upcoming appointments
$ur = $db->prepare("
    SELECT a.appt_id,a.appointment_date,a.appointment_time,a.status,a.queue_number,s.service_name
    FROM appointments a LEFT JOIN services s ON a.service_id=s.service_id
    WHERE a.resident_id=? AND a.appointment_date>=CURDATE() AND a.status NOT IN ('done','cancelled')
    ORDER BY a.appointment_date,a.appointment_time LIMIT 4
");
$ur->bind_param('i',$rid); $ur->execute();
$upcom = $ur->get_result();

// Services
$svcs = $db->query("SELECT * FROM services WHERE is_active=1 ORDER BY service_name");

// Unread notifs
$nr = $db->prepare("SELECT COUNT(*) c FROM notifications WHERE resident_id=? AND is_read=0");
$nr->bind_param('i',$rid); $nr->execute();
$unread = $nr->get_result()->fetch_row()[0];

$icons=['Barangay Clearance'=>'📄','Certificate of Indigency'=>'📜','Certificate of Residency'=>'🏠','Business Permit Clearance'=>'🏪','Certificate of Good Moral'=>'✅','Health Consultation'=>'🩺','Philhealth Assistance'=>'💊','4Ps / DSWD Assistance'=>'👨‍👩‍👧','Senior Citizen Services'=>'🧓','Complaint / Blotter Report'=>'⚖️','Lupon / Mediation'=>'🤝','Financial Assistance Request'=>'💰'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Home — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-mid:#2a7d50;--green-light:#e6f4ec;--gold:#c9933a;--bg:#f5f8f6;--white:#fff;--text:#1a2e23;--gray:#5a6a62;--border:#dce8e2;--amber:#d97706;--amber-bg:#fef3c7;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);max-width:430px;margin:0 auto;min-height:100vh;padding-bottom:84px;}

.app-header{background:linear-gradient(150deg,var(--green2) 0%,var(--green) 65%,var(--green-mid) 100%);padding:1.35rem 1.25rem .6rem;position:sticky;top:0;z-index:50;}
.hdr-row{display:flex;align-items:center;justify-content:space-between;}
.hdr-greet p{font-size:.72rem;color:rgba(255,255,255,.65);}
.hdr-greet h2{font-size:1.05rem;font-weight:800;color:#fff;}
.notif-btn{position:relative;width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;text-decoration:none;font-size:1.1rem;}
.notif-dot{position:absolute;top:5px;right:5px;width:8px;height:8px;border-radius:50%;background:#ef4444;border:2px solid var(--green);}
.curve{height:22px;background:var(--bg);border-radius:22px 22px 0 0;margin-top:-2px;}
.pad{padding:0 1rem;}

.section-lbl{font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--gray);margin-bottom:.7rem;}

/* QUEUE CARD */
.queue-card{background:var(--white);border-radius:20px;padding:1.5rem;border:1px solid var(--border);margin-bottom:1.25rem;text-align:center;}
.queue-card.live{border-color:var(--green);background:linear-gradient(135deg,#edfaf4,#e0f5ec);}
.q-badge{display:inline-block;padding:.28rem .85rem;border-radius:20px;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.7rem;}
.qb-wait{background:var(--amber-bg);color:var(--amber);}
.qb-srv{background:var(--green);color:#fff;}
.q-big{font-size:4.5rem;font-weight:900;color:var(--green);line-height:1;letter-spacing:-.04em;}
.q-lbl{font-size:.8rem;color:var(--gray);margin-top:.25rem;}
.q-stats{display:flex;justify-content:center;gap:2rem;margin-top:1.1rem;padding-top:1rem;border-top:1px solid var(--border);}
.q-stat .v{font-size:1.3rem;font-weight:800;color:var(--text);}
.q-stat .l{font-size:.67rem;color:var(--gray);}
.btn-book-lg{display:inline-block;padding:.78rem 2rem;background:var(--green);color:#fff;border-radius:12px;font-weight:800;font-size:.9rem;text-decoration:none;transition:background .2s;}
.btn-book-lg:hover{background:var(--green2);}

/* SERVICES */
.svc-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;margin-bottom:1.4rem;}
.svc-item{background:var(--white);border-radius:13px;padding:.8rem .4rem;text-align:center;border:1px solid var(--border);cursor:pointer;text-decoration:none;display:block;transition:border-color .2s,transform .15s;}
.svc-item:hover{border-color:var(--green-mid);transform:translateY(-2px);}
.svc-ic{font-size:1.35rem;margin-bottom:.3rem;}
.svc-nm{font-size:.6rem;font-weight:700;color:var(--text);line-height:1.25;}

/* APPOINTMENTS */
.appt-card{background:var(--white);border-radius:13px;padding:.95rem 1rem;border:1px solid var(--border);margin-bottom:.65rem;display:flex;align-items:center;gap:.85rem;}
.appt-date{background:var(--green-light);border-radius:9px;padding:.45rem .6rem;text-align:center;min-width:44px;}
.appt-date .d{font-size:1.15rem;font-weight:900;color:var(--green);line-height:1;}
.appt-date .m{font-size:.58rem;font-weight:800;text-transform:uppercase;color:var(--green-mid);letter-spacing:.05em;}
.appt-svc{font-weight:700;font-size:.86rem;}
.appt-tm{font-size:.73rem;color:var(--gray);margin-top:.12rem;}
.appt-st{margin-left:auto;padding:.2rem .6rem;border-radius:20px;font-size:.67rem;font-weight:800;white-space:nowrap;}
.as-p{background:#f3f4f6;color:#374151;} .as-c{background:var(--green-light);color:var(--green);}
.as-q{background:#ede9fe;color:#7c3aed;} .as-d{background:#eff6ff;color:#1d4ed8;}

/* BOTTOM NAV */
.bnav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;background:var(--white);border-top:1px solid var(--border);display:flex;padding:.55rem 0 calc(.55rem + env(safe-area-inset-bottom));}
.ni{flex:1;display:flex;flex-direction:column;align-items:center;gap:.18rem;font-size:.6rem;font-weight:700;color:var(--gray);text-decoration:none;}
.ni.on{color:var(--green);}
.ni .ic{font-size:1.25rem;}
</style>
</head>
<body>
<div class="app-header">
  <div class="hdr-row">
    <div class="hdr-greet">
      <?php $hour=(int)date('H'); $greet=$hour<12?'Good morning':($hour<17?'Good afternoon':'Good evening'); ?>
      <p><?= $greet ?>,</p>
      <h2><?=htmlspecialchars($res['first_name']).' '.htmlspecialchars($res['last_name'])?> 👋</h2>
    </div>
    <a href="notifications.php" class="notif-btn">🔔<?php if($unread):?><div class="notif-dot"></div><?php endif;?></a>
  </div>
</div>
<div class="curve"></div>

<div class="pad">

  <!-- QUEUE STATUS CARD -->
  <?php if($myq): ?>
  <div class="queue-card live" style="margin-top:1rem">
    <div class="q-badge <?=$myq['status']==='serving'?'qb-srv':'qb-wait'?>">
      <?=$myq['status']==='serving'?'🟢 Now Being Served':'⏳ In Queue'?>
    </div>
    <div class="q-big"><?=str_pad($myq['queue_number'],3,'0',STR_PAD_LEFT)?></div>
    <div class="q-lbl"><?=htmlspecialchars($myq['service_name']??'Your Queue Number')?></div>
    <div class="q-stats">
      <div class="q-stat"><div class="v"><?=$myq['ahead']??0?></div><div class="l">Ahead</div></div>
      <div class="q-stat"><div class="v"><?=$myq['now_serving']?str_pad($myq['now_serving'],3,'0',STR_PAD_LEFT):'—'?></div><div class="l">Serving</div></div>
      <div class="q-stat"><div class="v"><?=max(0,($myq['ahead']??0)*15)?>m</div><div class="l">Est. wait</div></div>
    </div>
  </div>
  <?php else: ?>
  <div class="queue-card" style="margin-top:1rem;padding:2rem">
    <div style="font-size:2.5rem;margin-bottom:.75rem">📋</div>
    <p style="color:var(--gray);font-size:.85rem;margin-bottom:1rem">No active queue for today.</p>
    <a class="btn-book-lg" href="book.php">Book an Appointment</a>
  </div>
  <?php endif; ?>

  <!-- SERVICES -->
  <div class="section-lbl" style="margin-top:1.25rem">Our Services</div>
  <div class="svc-grid">
    <?php while($sv=$svcs->fetch_assoc()):
      $ic=$icons[$sv['service_name']]??'⚕️';
    ?>
    <a href="book.php?service_id=<?=$sv['service_id']?>" class="svc-item">
      <div class="svc-ic"><?=$ic?></div>
      <div class="svc-nm"><?=htmlspecialchars(wordwrap($sv['service_name'],10,"\n",true))?></div>
    </a>
    <?php endwhile;?>
  </div>

  <!-- UPCOMING APPOINTMENTS -->
  <div class="section-lbl">Upcoming Appointments</div>
  <?php $cnt=0; while($ap=$upcom->fetch_assoc()): $cnt++;
    $stmap=['pending'=>'as-p','confirmed'=>'as-c','in_queue'=>'as-q','serving'=>'as-c'];
  ?>
  <div class="appt-card">
    <div class="appt-date">
      <div class="d"><?=date('d',strtotime($ap['appointment_date']))?></div>
      <div class="m"><?=date('M',strtotime($ap['appointment_date']))?></div>
    </div>
    <div>
      <div class="appt-svc"><?=htmlspecialchars($ap['service_name']??'General')?></div>
      <div class="appt-tm">🕐 <?=date('h:i A',strtotime($ap['appointment_time']))?></div>
    </div>
    <div class="appt-st <?=$stmap[$ap['status']]??'as-p'?>"><?=ucfirst(str_replace('_',' ',$ap['status']))?></div>
  </div>
  <?php endwhile;?>
  <?php if($cnt===0):?><p style="color:var(--gray);font-size:.83rem;text-align:center;padding:.75rem 0">No upcoming appointments.</p><?php endif;?>
  <?php if($cnt>0):?><a href="appointments.php" style="display:block;text-align:center;color:var(--green);font-size:.8rem;font-weight:700;text-decoration:none;padding:.5rem 0 1rem">View all →</a><?php endif;?>

</div>

<nav class="bnav">
  <a href="home.php"         class="ni on"><span class="ic">🏠</span>Home</a>
  <a href="book.php"         class="ni"><span class="ic">📅</span>Book</a>
  <a href="queue_status.php" class="ni"><span class="ic">🔢</span>Queue</a>
  <a href="appointments.php" class="ni"><span class="ic">📋</span>My Appts</a>
  <a href="profile.php"      class="ni"><span class="ic">👤</span>Profile</a>
</nav>
</body>
</html>
