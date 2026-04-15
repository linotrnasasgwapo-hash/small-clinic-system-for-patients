<?php
// mobile/login.php
require_once '../includes/auth.php';
require_once '../config/db.php';
if (isResident()) { header('Location: home.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $db    = getDB();
    $email = clean($db, $_POST['email']??'');
    $pass  = $_POST['password']??'';
    $stmt  = $db->prepare("SELECT * FROM residents WHERE email=? AND is_verified=1 LIMIT 1");
    $stmt->bind_param('s',$email); $stmt->execute();
    $res   = $stmt->get_result()->fetch_assoc();
    if ($res && password_verify($pass, $res['password'])) {
        $_SESSION['resident_id']   = $res['resident_id'];
        $_SESSION['resident_name'] = $res['first_name'];
        header('Location: home.php'); exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Resident Login — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--gold:#c9933a;--border:#d4e4da;--red:#dc2626;--bg:#f5f8f6;--text:#1a2e23;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1rem;}
.wrap{width:100%;max-width:390px;}
.hero{background:linear-gradient(150deg,var(--green2) 0%,var(--green) 60%,#2a7d50 100%);border-radius:24px 24px 0 0;padding:2.5rem 2rem 2rem;text-align:center;}
.emblem{width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.2);margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;font-size:1.8rem;}
.hero h1{font-size:1.35rem;font-weight:800;color:#fff;line-height:1.2;}
.hero p{font-size:.78rem;color:rgba(255,255,255,.65);margin-top:.35rem;}
.form-card{background:#fff;border-radius:0 0 24px 24px;padding:2rem;box-shadow:0 16px 48px rgba(0,0,0,.12);}
label{display:block;font-size:.75rem;font-weight:700;color:var(--text);margin-bottom:.38rem;text-transform:uppercase;letter-spacing:.05em;}
input{width:100%;padding:.82rem 1rem;border:1.5px solid var(--border);border-radius:12px;font-size:.92rem;font-family:inherit;outline:none;transition:border-color .2s;}
input:focus{border-color:var(--green);}
.field{margin-bottom:1.1rem;}
.btn{width:100%;padding:.9rem;background:var(--green);color:#fff;border:none;border-radius:13px;font-size:1rem;font-weight:800;font-family:inherit;cursor:pointer;transition:background .2s;letter-spacing:.01em;}
.btn:hover{background:var(--green2);}
.error{background:#fef2f2;color:var(--red);border:1px solid #fca5a5;border-radius:10px;padding:.7rem 1rem;font-size:.82rem;margin-bottom:1rem;}
.hint{background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:.7rem 1rem;font-size:.76rem;color:#166534;margin-bottom:1rem;}
.links{text-align:center;margin-top:1.25rem;font-size:.8rem;color:#9ca3af;}
.links a{color:var(--green);font-weight:700;text-decoration:none;}
.divider{display:flex;align-items:center;gap:.6rem;color:#d1d5db;font-size:.75rem;margin:.9rem 0;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
</style>
</head>
<body>
<div class="wrap">
  <div class="hero">
    <div class="emblem">🏛️</div>
    <h1>Barangay Saraet</h1>
    <p>Himamaylan City — Resident Portal</p>
  </div>
  <div class="form-card">
    <div class="hint">Demo: <strong>juan@email.com</strong> / <strong>User@1234</strong></div>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="field"><label>Email Address</label><input type="email" name="email" required autocomplete="email" placeholder="yourname@email.com"></div>
      <div class="field"><label>Password</label><input type="password" name="password" required autocomplete="current-password" placeholder="••••••••"></div>
      <button class="btn" type="submit">Sign In</button>
    </form>
    <div class="divider">or</div>
    <div class="links">Don't have an account? <a href="register.php">Register here</a></div>
    <div class="links" style="margin-top:.6rem"><a href="../index.php">← Back to Home</a></div>
  </div>
</div>
</body>
</html>
