<?php
// admin/dashboard.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();

// ── Stats ──────────────────────────────────────────────────────
$today = date('Y-m-d');
function qs($db,$sql){$r=$db->query($sql);return $r?$r->fetch_row()[0]:0;}
$s['appts_today']    = qs($db,"SELECT COUNT(*) FROM appointments WHERE appointment_date='$today'");
$s['waiting']        = qs($db,"SELECT COUNT(*) FROM queue WHERE queue_date='$today' AND status='waiting'");
$s['serving']        = qs($db,"SELECT COUNT(*) FROM queue WHERE queue_date='$today' AND status='serving'");
$s['done_today']     = qs($db,"SELECT COUNT(*) FROM queue WHERE queue_date='$today' AND status='done'");
$s['total_residents']= qs($db,"SELECT COUNT(*) FROM residents");
$s['open_records']   = qs($db,"SELECT COUNT(*) FROM client_records WHERE status IN ('open','processing')");

// ── Today's queue ──────────────────────────────────────────────
$qRes = $db->query("
    SELECT q.queue_id, q.queue_number, q.status, q.called_at,
           r.first_name, r.last_name, r.phone,
           s.service_name, a.appt_id, a.appointment_time
    FROM queue q
    JOIN appointments a ON q.appt_id = a.appt_id
    JOIN residents r    ON a.resident_id = r.resident_id
    LEFT JOIN services s ON q.service_id = s.service_id
    WHERE q.queue_date = '$today'
    ORDER BY q.queue_number ASC
    LIMIT 25
");

// ── Recent appointments ────────────────────────────────────────
$aRes = $db->query("
    SELECT a.appt_id, a.appointment_date, a.appointment_time, a.status, a.queue_number,
           r.first_name, r.last_name,
           s.service_name
    FROM appointments a
    JOIN residents r    ON a.resident_id = r.resident_id
    LEFT JOIN services s ON a.service_id = s.service_id
    ORDER BY a.created_at DESC
    LIMIT 12
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — Barangay Saraet Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --green:#1a5c3a;--green2:#0f3d27;--green-mid:#2a7d50;--green-light:#e6f4ec;--green-soft:#c3e4d0;
  --gold:#c9933a;--gold-light:#fdf3e3;
  --bg:#f2f6f4;--white:#fff;--text:#1a2e23;--gray:#5a6a62;--border:#dce8e2;
  --amber:#d97706;--amber-bg:#fef3c7;--red:#dc2626;--red-bg:#fef2f2;
  --blue:#1d4ed8;--blue-bg:#eff6ff;
  --sidebar:250px;
}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}

/* ── SIDEBAR ── */
.sidebar{width:var(--sidebar);background:var(--green2);min-height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;z-index:200;}
.sb-brand{padding:1.5rem 1.25rem 1.1rem;border-bottom:1px solid rgba(255,255,255,.08);}
.sb-brand h2{font-family:'Playfair Display',serif;color:#fff;font-size:1.05rem;font-weight:700;line-height:1.3;}
.sb-brand p{color:rgba(255,255,255,.5);font-size:.68rem;margin-top:.2rem;}
.sb-menu{flex:1;padding:.9rem 0;overflow-y:auto;}
.sb-section{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.3);padding:.6rem 1.25rem .3rem;}
.sb-item{display:flex;align-items:center;gap:.65rem;padding:.62rem 1.25rem;color:rgba(255,255,255,.75);font-size:.85rem;text-decoration:none;border-left:3px solid transparent;transition:all .15s;}
.sb-item:hover,.sb-item.active{background:rgba(255,255,255,.1);color:#fff;border-left-color:var(--gold);}
.sb-item .ic{font-size:.95rem;width:18px;text-align:center;}
.sb-footer{padding:1rem 1.25rem;border-top:1px solid rgba(255,255,255,.08);}
.sb-user{display:flex;align-items:center;gap:.65rem;margin-bottom:.75rem;}
.sb-avatar{width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:.85rem;color:#fff;font-weight:700;}
.sb-user-info p{color:#fff;font-size:.82rem;font-weight:500;line-height:1.2;}
.sb-user-info span{color:rgba(255,255,255,.5);font-size:.68rem;text-transform:capitalize;}
.sb-logout{display:block;text-align:center;padding:.45rem;background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border-radius:7px;font-size:.76rem;text-decoration:none;transition:background .15s;}
.sb-logout:hover{background:rgba(255,255,255,.15);}

/* ── MAIN ── */
.main{margin-left:var(--sidebar);flex:1;padding:1.75rem 2rem;max-width:calc(100vw - var(--sidebar));}
.page-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;}
.page-hd h1{font-family:'Playfair Display',serif;font-size:1.65rem;font-weight:700;}
.hd-right{display:flex;align-items:center;gap:.75rem;}
.date-chip{padding:.4rem .9rem;background:var(--white);border:1px solid var(--border);border-radius:20px;font-size:.78rem;color:var(--gray);}
.btn-primary{padding:.55rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:.82rem;font-weight:600;font-family:inherit;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;transition:background .15s;}
.btn-primary:hover{background:var(--green2);}
.btn-gold{background:var(--gold);}
.btn-gold:hover{background:#a87a2a;}

/* ── STATS ── */
.stats{display:grid;grid-template-columns:repeat(6,1fr);gap:.9rem;margin-bottom:1.5rem;}
.stat-card{background:var(--white);border-radius:14px;padding:1.1rem 1rem;border:1px solid var(--border);}
.stat-card .lbl{font-size:.7rem;color:var(--gray);font-weight:600;text-transform:uppercase;letter-spacing:.05em;}
.stat-card .val{font-size:1.8rem;font-weight:700;color:var(--text);margin:.3rem 0 .15rem;line-height:1;}
.stat-card .sub{font-size:.7rem;}
.stat-green{background:var(--green);border-color:var(--green);}
.stat-green .lbl,.stat-green .sub{color:rgba(255,255,255,.65);}
.stat-green .val{color:#fff;}

/* ── LIVE BAR ── */
.live-bar{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:.85rem 1.25rem;display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.live-dot{width:8px;height:8px;border-radius:50%;background:#10b981;animation:blink 1.4s infinite;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.live-txt{font-size:.85rem;font-weight:600;color:var(--green);}
.spacer{flex:1;}

/* ── GRID ── */
.grid2{display:grid;grid-template-columns:1.1fr 1fr;gap:1.25rem;}
.card{background:var(--white);border-radius:16px;border:1px solid var(--border);overflow:hidden;}
.card-hd{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);}
.card-hd h3{font-size:.9rem;font-weight:600;}
.badge{padding:.2rem .6rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.bg-green{background:var(--green-light);color:var(--green);}
.bg-amber{background:var(--amber-bg);color:var(--amber);}
.bg-red{background:var(--red-bg);color:var(--red);}
.bg-blue{background:var(--blue-bg);color:var(--blue);}
.bg-gray{background:#f3f4f6;color:#374151;}

/* ── TABLE ── */
.tbl-wrap{overflow-y:auto;max-height:360px;}
table{width:100%;border-collapse:collapse;font-size:.82rem;}
th{padding:.65rem 1rem;text-align:left;font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--gray);background:var(--bg);position:sticky;top:0;z-index:1;}
td{padding:.7rem 1rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border:none;}
tr:hover td{background:#fafcfb;}
.q-num{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:7px;background:var(--green-light);color:var(--green);font-weight:700;font-size:.88rem;}
.q-num.srv{background:var(--green);color:#fff;}
.p-name{font-weight:500;}
.p-sub{font-size:.72rem;color:var(--gray);}
.btn-xs{padding:.28rem .65rem;border-radius:6px;font-size:.72rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:background .15s;}
.bx-call{background:var(--gold-light);color:var(--gold);}
.bx-serve{background:var(--green-light);color:var(--green);}
.bx-done{background:var(--blue-bg);color:var(--blue);}
.bx-skip{background:#f3f4f6;color:#374151;}
.actions{display:flex;gap:.3rem;flex-wrap:wrap;}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sb-brand">
    <h2>Barangay Saraet</h2>
    <p>Himamaylan City Admin</p>
  </div>
  <nav class="sb-menu">
    <div class="sb-section">Main</div>
    <a href="dashboard.php"    class="sb-item active"><span class="ic">🏠</span>Dashboard</a>
    <a href="queue.php"        class="sb-item"><span class="ic">🔢</span>Live Queue</a>
    <a href="appointments.php" class="sb-item"><span class="ic">📅</span>Appointments</a>
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
    <div class="sb-user">
      <div class="sb-avatar"><?= strtoupper(substr($_SESSION['admin_name'],0,1)) ?></div>
      <div class="sb-user-info">
        <p><?= htmlspecialchars($_SESSION['admin_name']) ?></p>
        <span><?= htmlspecialchars($_SESSION['admin_role']) ?></span>
      </div>
    </div>
    <a href="logout.php" class="sb-logout">Sign Out</a>
  </div>
</aside>

<!-- MAIN -->
<main class="main">
  <div class="page-hd">
    <h1>Dashboard</h1>
    <div class="hd-right">
      <span class="date-chip">📅 <?= date('l, F j, Y') ?></span>
      <a href="appointments.php?new=1" class="btn-primary btn-gold">+ Walk-in</a>
      <a href="queue.php" class="btn-primary">📺 Queue Display</a>
    </div>
  </div>

  <!-- STATS -->
  <div class="stats">
    <div class="stat-card stat-green">
      <div class="lbl">Today's Appointments</div>
      <div class="val"><?= $s['appts_today'] ?></div>
      <div class="sub">Total scheduled</div>
    </div>
    <div class="stat-card">
      <div class="lbl">Now Waiting</div>
      <div class="val" style="color:var(--amber)"><?= $s['waiting'] ?></div>
      <div class="sub" style="color:var(--amber)">In queue</div>
    </div>
    <div class="stat-card">
      <div class="lbl">Being Served</div>
      <div class="val" style="color:var(--blue)"><?= $s['serving'] ?></div>
      <div class="sub" style="color:var(--blue)">Active now</div>
    </div>
    <div class="stat-card">
      <div class="lbl">Served Today</div>
      <div class="val" style="color:var(--green-mid)"><?= $s['done_today'] ?></div>
      <div class="sub">Completed</div>
    </div>
    <div class="stat-card">
      <div class="lbl">Residents</div>
      <div class="val"><?= $s['total_residents'] ?></div>
      <div class="sub">Registered</div>
    </div>
    <div class="stat-card">
      <div class="lbl">Open Records</div>
      <div class="val" style="color:var(--red)"><?= $s['open_records'] ?></div>
      <div class="sub">Pending action</div>
    </div>
  </div>

  <!-- LIVE BAR -->
  <div class="live-bar">
    <div class="live-dot"></div>
    <span class="live-txt">Queue is LIVE</span>
    <span style="color:var(--gray);font-size:.82rem;">Auto-updates when you refresh</span>
    <div class="spacer"></div>
    <span style="font-size:.8rem;color:var(--gray);">Serving window: Counter 1 & 2</span>
  </div>

  <!-- 2-COLUMN GRID -->
  <div class="grid2">

    <!-- TODAY'S QUEUE -->
    <div class="card">
      <div class="card-hd">
        <h3>Today's Queue</h3>
        <span class="badge bg-green">Live — <?= date('h:i A') ?></span>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>#</th><th>Resident</th><th>Service</th><th>Time</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
          <?php
          $any = false;
          while ($q = $qRes->fetch_assoc()):
            $any = true;
          ?>
          <tr>
            <td><div class="q-num <?= $q['status']==='serving'?'srv':'' ?>"><?= $q['queue_number'] ?></div></td>
            <td>
              <div class="p-name"><?= htmlspecialchars($q['first_name'].' '.$q['last_name']) ?></div>
              <div class="p-sub"><?= htmlspecialchars($q['phone'] ?? '') ?></div>
            </td>
            <td class="p-sub"><?= htmlspecialchars($q['service_name'] ?? 'General') ?></td>
            <td class="p-sub"><?= date('h:i A', strtotime($q['appointment_time'])) ?></td>
            <td>
              <?php
              $bmap=['waiting'=>'bg-amber','serving'=>'bg-green','done'=>'bg-blue','skipped'=>'bg-gray'];
              echo '<span class="badge '.($bmap[$q['status']]??'bg-gray').'">'.ucfirst($q['status']).'</span>';
              ?>
            </td>
            <td><div class="actions">
              <?php if($q['status']==='waiting'): ?>
                <button class="btn-xs bx-call"  onclick="queueAction(<?=$q['queue_id']?>,'serving')">▶ Serve</button>
                <button class="btn-xs bx-skip"  onclick="queueAction(<?=$q['queue_id']?>,'skipped')">Skip</button>
              <?php elseif($q['status']==='serving'): ?>
                <button class="btn-xs bx-done"  onclick="queueAction(<?=$q['queue_id']?>,'done')">✓ Done</button>
              <?php else: ?>
                <span class="p-sub">—</span>
              <?php endif; ?>
            </div></td>
          </tr>
          <?php endwhile; ?>
          <?php if(!$any): ?>
          <tr><td colspan="6" style="text-align:center;padding:2.5rem;color:var(--gray)">No queue entries today.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- RECENT APPOINTMENTS -->
    <div class="card">
      <div class="card-hd">
        <h3>Recent Appointments</h3>
        <a href="appointments.php" style="font-size:.76rem;color:var(--green);text-decoration:none;font-weight:600;">View all →</a>
      </div>
      <div class="tbl-wrap">
        <table>
          <thead><tr><th>ID</th><th>Resident</th><th>Service</th><th>Date</th><th>Status</th></tr></thead>
          <tbody>
          <?php while($a=$aRes->fetch_assoc()):
            $smap=['pending'=>'bg-gray','confirmed'=>'bg-green','in_queue'=>'bg-amber','serving'=>'bg-green','done'=>'bg-blue','cancelled'=>'bg-red','no_show'=>'bg-red'];
          ?>
          <tr>
            <td style="font-weight:600;color:var(--green)">#<?= $a['appt_id'] ?></td>
            <td><div class="p-name"><?= htmlspecialchars($a['first_name'].' '.$a['last_name']) ?></div></td>
            <td class="p-sub"><?= htmlspecialchars($a['service_name']??'—') ?></td>
            <td class="p-sub"><?= date('M j', strtotime($a['appointment_date'])) ?></td>
            <td><span class="badge <?= $smap[$a['status']]??'bg-gray' ?>"><?= ucfirst(str_replace('_',' ',$a['status'])) ?></span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /grid2 -->
</main>

<script>
async function queueAction(id, status) {
  const r = await fetch('../includes/queue_api.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({queue_id: id, status: status})
  });
  const d = await r.json();
  if (d.success) location.reload();
  else alert('Error: ' + (d.error || 'Unknown'));
}
</script>
</body>
</html>
