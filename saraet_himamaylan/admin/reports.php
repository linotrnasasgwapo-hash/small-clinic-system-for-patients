<?php
// admin/reports.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();

$month = clean($db, $_GET['month'] ?? date('Y-m'));
$m_start = $month . '-01';
$m_end   = date('Y-m-t', strtotime($m_start));

function qs2($db, $sql){ $r=$db->query($sql); return $r ? $r->fetch_row()[0] : 0; }

$stats = [
    'total_appts'    => qs2($db,"SELECT COUNT(*) FROM appointments WHERE appointment_date BETWEEN '$m_start' AND '$m_end'"),
    'done'           => qs2($db,"SELECT COUNT(*) FROM appointments WHERE appointment_date BETWEEN '$m_start' AND '$m_end' AND status='done'"),
    'cancelled'      => qs2($db,"SELECT COUNT(*) FROM appointments WHERE appointment_date BETWEEN '$m_start' AND '$m_end' AND status='cancelled'"),
    'no_show'        => qs2($db,"SELECT COUNT(*) FROM appointments WHERE appointment_date BETWEEN '$m_start' AND '$m_end' AND status='no_show'"),
    'new_residents'  => qs2($db,"SELECT COUNT(*) FROM residents WHERE created_at BETWEEN '$m_start' AND '$m_end 23:59:59'"),
    'total_records'  => qs2($db,"SELECT COUNT(*) FROM client_records WHERE created_at BETWEEN '$m_start' AND '$m_end 23:59:59'"),
    'released'       => qs2($db,"SELECT COUNT(*) FROM client_records WHERE created_at BETWEEN '$m_start' AND '$m_end 23:59:59' AND status='released'"),
];

// Top services
$top_svc = $db->query("SELECT s.service_name, COUNT(*) cnt FROM appointments a LEFT JOIN services s ON a.service_id=s.service_id WHERE a.appointment_date BETWEEN '$m_start' AND '$m_end' GROUP BY a.service_id ORDER BY cnt DESC LIMIT 6");

// Daily count
$daily = $db->query("SELECT appointment_date, COUNT(*) cnt FROM appointments WHERE appointment_date BETWEEN '$m_start' AND '$m_end' GROUP BY appointment_date ORDER BY appointment_date");
$daily_data = []; while($d=$daily->fetch_assoc()) $daily_data[$d['appointment_date']] = $d['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reports — Barangay Saraet Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-light:#e6f4ec;--gold:#c9933a;--bg:#f2f6f4;--white:#fff;--text:#1a2e23;--gray:#5a6a62;--border:#dce8e2;--blue:#1d4ed8;--blue-bg:#eff6ff;--amber:#d97706;--amber-bg:#fef3c7;--red:#dc2626;--sidebar:250px;}
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
.filter-bar{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:.9rem 1.25rem;display:flex;gap:.65rem;align-items:center;margin-bottom:1.5rem;}
.filter-bar input{padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:.88rem;font-family:inherit;outline:none;}
.filter-bar input:focus{border-color:var(--green);}
.filter-bar button{padding:.5rem .9rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:var(--white);border-radius:14px;padding:1.25rem;border:1px solid var(--border);}
.stat-card .lbl{font-size:.7rem;color:var(--gray);font-weight:600;text-transform:uppercase;letter-spacing:.05em;}
.stat-card .val{font-size:2rem;font-weight:700;margin:.3rem 0 .15rem;line-height:1;}
.stat-card .sub{font-size:.72rem;color:var(--gray);}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;}
.card{background:var(--white);border-radius:16px;border:1px solid var(--border);overflow:hidden;}
.card-hd{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);}
.card-hd h3{font-size:.9rem;font-weight:600;}
.card-body{padding:1.25rem;}
.svc-bar-wrap{margin-bottom:.85rem;}
.svc-bar-label{display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:.3rem;}
.svc-bar-label span:first-child{font-weight:500;}
.svc-bar-label span:last-child{color:var(--gray);}
.bar-bg{background:#e5e7eb;border-radius:6px;height:8px;}
.bar-fill{background:var(--green);height:8px;border-radius:6px;}
.daily-chart{display:flex;align-items:flex-end;gap:.35rem;height:120px;padding:.5rem 0;}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:.25rem;}
.bar-col .bar{width:100%;background:var(--green-light);border-radius:4px 4px 0 0;border:1px solid var(--green);min-height:4px;transition:background .2s;}
.bar-col .bar:hover{background:var(--green);}
.bar-col .lbl{font-size:.58rem;color:var(--gray);transform:rotate(-45deg);white-space:nowrap;}
.bar-col .cnt{font-size:.65rem;font-weight:700;color:var(--text);}
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
    <a href="schedules.php"    class="sb-item"><span class="ic">🗓️</span>Schedules</a>
    <a href="staff.php"        class="sb-item"><span class="ic">🔐</span>Staff Accounts</a>
    <a href="reports.php"      class="sb-item active"><span class="ic">📊</span>Reports</a>
  </nav>
  <div class="sb-footer">
    <div class="sb-user"><div class="sb-avatar"><?= strtoupper(substr($_SESSION['admin_name'],0,1)) ?></div><div class="sb-user-info"><p><?= htmlspecialchars($_SESSION['admin_name']) ?></p><span><?= $_SESSION['admin_role'] ?></span></div></div>
    <a href="logout.php" class="sb-logout">Sign Out</a>
  </div>
</aside>

<main class="main">
  <div class="page-hd">
    <h1>Reports & Analytics</h1>
    <form method="GET" class="filter-bar" style="margin:0;background:transparent;border:none;padding:0">
      <input type="month" name="month" value="<?= $month ?>">
      <button type="submit">View</button>
    </form>
  </div>

  <div class="stats">
    <div class="stat-card">
      <div class="lbl">Total Appointments</div>
      <div class="val" style="color:var(--green)"><?= $stats['total_appts'] ?></div>
      <div class="sub"><?= date('F Y', strtotime($m_start)) ?></div>
    </div>
    <div class="stat-card">
      <div class="lbl">Successfully Served</div>
      <div class="val" style="color:var(--blue)"><?= $stats['done'] ?></div>
      <div class="sub"><?= $stats['total_appts']>0 ? round($stats['done']/$stats['total_appts']*100) : 0 ?>% completion rate</div>
    </div>
    <div class="stat-card">
      <div class="lbl">Cancellations</div>
      <div class="val" style="color:var(--amber)"><?= $stats['cancelled'] ?></div>
      <div class="sub"><?= $stats['no_show'] ?> no-shows</div>
    </div>
    <div class="stat-card">
      <div class="lbl">New Residents</div>
      <div class="val" style="color:var(--green)"><?= $stats['new_residents'] ?></div>
      <div class="sub"><?= $stats['total_records'] ?> records, <?= $stats['released'] ?> released</div>
    </div>
  </div>

  <div class="grid2">
    <!-- TOP SERVICES -->
    <div class="card">
      <div class="card-hd"><h3>Top Services This Month</h3></div>
      <div class="card-body">
        <?php
        $max_c = 1;
        $svc_data = [];
        while($sv=$top_svc->fetch_assoc()) { $svc_data[]=$sv; $max_c=max($max_c,$sv['cnt']); }
        foreach($svc_data as $sv):
          $pct = round($sv['cnt']/$max_c*100);
        ?>
        <div class="svc-bar-wrap">
          <div class="svc-bar-label">
            <span><?= htmlspecialchars($sv['service_name']??'Unknown') ?></span>
            <span><?= $sv['cnt'] ?> appts</span>
          </div>
          <div class="bar-bg"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($svc_data)): ?><p style="color:var(--gray);font-size:.85rem;text-align:center;padding:1rem">No data for this month.</p><?php endif; ?>
      </div>
    </div>

    <!-- DAILY CHART -->
    <div class="card">
      <div class="card-hd"><h3>Daily Appointments — <?= date('F Y', strtotime($m_start)) ?></h3></div>
      <div class="card-body">
        <?php
        $days_in_month = (int)date('t', strtotime($m_start));
        $max_daily = max(1, max(array_values($daily_data) ?: [1]));
        ?>
        <div class="daily-chart">
          <?php for($d=1;$d<=$days_in_month;$d++):
            $date = $month.'-'.str_pad($d,2,'0',STR_PAD_LEFT);
            $cnt  = $daily_data[$date] ?? 0;
            $h    = max(4, round($cnt/$max_daily*100));
          ?>
          <div class="bar-col">
            <div class="cnt"><?= $cnt ?: '' ?></div>
            <div class="bar" style="height:<?= $h ?>px" title="<?= date('M j',strtotime($date)) ?>: <?= $cnt ?> appts"></div>
            <div class="lbl"><?= $d ?></div>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>
</main>
</body>
</html>
