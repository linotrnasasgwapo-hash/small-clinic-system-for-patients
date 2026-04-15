<?php
// mobile/profile.php
require_once '../includes/auth.php';
requireResident();
require_once '../config/db.php';
$db  = getDB();
$rid = $_SESSION['resident_id'];
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fn  = clean($db,$_POST['first_name']??'');
    $ln  = clean($db,$_POST['last_name']??'');
    $mn  = clean($db,$_POST['middle_name']??'');
    $ph  = clean($db,$_POST['phone']??'');
    $add = clean($db,$_POST['address']??'');
    $bd  = clean($db,$_POST['birthdate']??'');
    $gn  = clean($db,$_POST['gender']??'');
    $cs  = clean($db,$_POST['civil_status']??'');
    $pur = clean($db,$_POST['purok']??'');
    $occ = clean($db,$_POST['occupation']??'');
    $db->query("UPDATE residents SET first_name='$fn',last_name='$ln',middle_name='$mn',phone='$ph',address='$add',birthdate='$bd',gender='$gn',civil_status='$cs',purok='$pur',occupation='$occ' WHERE resident_id=$rid");
    $msg = 'Profile updated successfully!';
}
$r   = $db->prepare("SELECT * FROM residents WHERE resident_id=?");
$r->bind_param('i',$rid); $r->execute();
$res = $r->get_result()->fetch_assoc();
$total = $db->query("SELECT COUNT(*) FROM appointments WHERE resident_id=$rid")->fetch_row()[0];
$done  = $db->query("SELECT COUNT(*) FROM appointments WHERE resident_id=$rid AND status='done'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Profile — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--green-light:#e6f4ec;--border:#dce8e2;--bg:#f5f8f6;--text:#1a2e23;--gray:#5a6a62;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);max-width:430px;margin:0 auto;padding-bottom:84px;}
.hero{background:linear-gradient(150deg,var(--green2),var(--green));padding:2rem 1.25rem 3rem;text-align:center;}
.avatar{width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.25);margin:0 auto .75rem;display:flex;align-items:center;justify-content:center;font-size:2rem;}
.hero h2{font-size:1.1rem;font-weight:800;color:#fff;}
.hero p{font-size:.76rem;color:rgba(255,255,255,.65);margin-top:.2rem;}
.stats-bar{display:flex;background:#fff;margin:-1.5rem .75rem 0;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,.12);padding:.9rem;gap:.5rem;margin-bottom:.9rem;}
.sb .v{font-size:1.35rem;font-weight:900;color:var(--green);text-align:center;}
.sb .l{font-size:.62rem;color:var(--gray);text-align:center;}
.sb{flex:1;border-right:1px solid var(--border);}
.sb:last-child{border:none;}
.pad{padding:.25rem 1rem;}
.form-card{background:#fff;border-radius:14px;padding:1.2rem;border:1px solid var(--border);margin-bottom:.75rem;}
.form-card h3{font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--green);margin-bottom:.9rem;padding-bottom:.45rem;border-bottom:1px solid var(--border);}
label{display:block;font-size:.7rem;font-weight:700;color:var(--text);margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.04em;}
input,select{width:100%;padding:.7rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.86rem;font-family:inherit;outline:none;transition:border-color .2s;background:#fff;}
input:focus,select:focus{border-color:var(--green);}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
.field{margin-bottom:.8rem;}
.btn{width:100%;padding:.85rem;background:var(--green);color:#fff;border:none;border-radius:13px;font-size:.95rem;font-weight:800;cursor:pointer;font-family:inherit;margin-top:.25rem;}
.btn:hover{background:var(--green2);}
.msg{background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:10px;padding:.65rem .9rem;font-size:.8rem;margin-bottom:.75rem;}
.btn-out{display:block;width:100%;text-align:center;padding:.8rem;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;border-radius:13px;font-weight:800;font-size:.88rem;text-decoration:none;margin-top:.5rem;}
.bnav{position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;background:#fff;border-top:1px solid var(--border);display:flex;padding:.55rem 0;}
.ni{flex:1;display:flex;flex-direction:column;align-items:center;gap:.18rem;font-size:.6rem;font-weight:700;color:var(--gray);text-decoration:none;}
.ni.on{color:var(--green);}
.ni .ic{font-size:1.25rem;}
</style>
</head>
<body>
<div class="hero">
  <div class="avatar">👤</div>
  <h2><?=htmlspecialchars($res['first_name'].' '.$res['last_name'])?></h2>
  <p><?=htmlspecialchars($res['email'])?> &middot; <?=htmlspecialchars($res['purok']??'Barangay Saraet')?></p>
</div>
<div style="padding:0 .75rem">
<div class="stats-bar">
  <div class="sb"><div class="v"><?=$total?></div><div class="l">Total Appts</div></div>
  <div class="sb"><div class="v"><?=$done?></div><div class="l">Completed</div></div>
  <div class="sb"><div class="v"><?=$total-$done?></div><div class="l">Pending</div></div>
</div>
</div>
<div class="pad">
  <?php if($msg):?><div class="msg"><?=$msg?></div><?php endif;?>
  <form method="POST">
    <div class="form-card">
      <h3>Personal Information</h3>
      <div class="row2">
        <div class="field"><label>First Name</label><input name="first_name" value="<?=htmlspecialchars($res['first_name'])?>" required></div>
        <div class="field"><label>Last Name</label><input name="last_name" value="<?=htmlspecialchars($res['last_name'])?>" required></div>
      </div>
      <div class="field"><label>Middle Name</label><input name="middle_name" value="<?=htmlspecialchars($res['middle_name']??'')?>"></div>
      <div class="row2">
        <div class="field"><label>Birthdate</label><input type="date" name="birthdate" value="<?=$res['birthdate']?>"></div>
        <div class="field"><label>Gender</label>
          <select name="gender"><option value="">Select</option>
            <option <?=$res['gender']==='Male'?'selected':''?>>Male</option>
            <option <?=$res['gender']==='Female'?'selected':''?>>Female</option>
            <option <?=$res['gender']==='Other'?'selected':''?>>Other</option>
          </select>
        </div>
      </div>
      <div class="row2">
        <div class="field"><label>Civil Status</label>
          <select name="civil_status">
            <?php foreach(['Single','Married','Widowed','Separated'] as $cs):?>
            <option <?=$res['civil_status']===$cs?'selected':''?>><?=$cs?></option>
            <?php endforeach;?>
          </select>
        </div>
        <div class="field"><label>Purok</label><input name="purok" value="<?=htmlspecialchars($res['purok']??'')?>"></div>
      </div>
      <div class="field"><label>Occupation</label><input name="occupation" value="<?=htmlspecialchars($res['occupation']??'')?>"></div>
      <div class="field"><label>Address</label><input name="address" value="<?=htmlspecialchars($res['address']??'')?>"></div>
      <div class="field"><label>Phone</label><input name="phone" value="<?=htmlspecialchars($res['phone']??'')?>"></div>
      <button class="btn" type="submit">Save Changes</button>
    </div>
  </form>
  <a class="btn-out" href="logout.php">Sign Out</a>
</div>
<nav class="bnav">
  <a href="home.php" class="ni"><span class="ic">🏠</span>Home</a>
  <a href="book.php" class="ni"><span class="ic">📅</span>Book</a>
  <a href="queue_status.php" class="ni"><span class="ic">🔢</span>Queue</a>
  <a href="appointments.php" class="ni"><span class="ic">📋</span>My Appts</a>
  <a href="profile.php" class="ni on"><span class="ic">👤</span>Profile</a>
</nav>
</body>
</html>
