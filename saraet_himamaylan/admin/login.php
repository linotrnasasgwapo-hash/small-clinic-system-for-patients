<?php
// admin/login.php
require_once '../includes/auth.php';
require_once '../config/db.php';
if (isAdmin()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db    = getDB();
    $email = clean($db, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt  = $db->prepare("SELECT * FROM admins WHERE email=? AND is_active=1 LIMIT 1");
    $stmt->bind_param('s', $email); $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin && password_verify($pass, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_pos']  = $admin['position'];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Staff Login — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--gold:#c9933a;--border:#d4e4da;--red:#dc2626;--bg:#f5f8f6;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;}
.wrap{width:100%;max-width:420px;}
.card{background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 12px 48px rgba(0,0,0,.12);}
.card-top{background:linear-gradient(145deg,var(--green2),var(--green));padding:2.5rem 2rem 2rem;text-align:center;}
.emblem{width:68px;height:68px;border-radius:50%;background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto .9rem;}
.card-top h1{font-family:'Playfair Display',serif;color:#fff;font-size:1.35rem;font-weight:700;line-height:1.2;}
.card-top p{color:rgba(255,255,255,.65);font-size:.78rem;margin-top:.3rem;}
.card-body{padding:2rem;}
label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.04em;}
input{width:100%;padding:.8rem 1rem;border:1.5px solid var(--border);border-radius:10px;font-size:.92rem;font-family:inherit;outline:none;transition:border-color .2s;}
input:focus{border-color:var(--green);}
.field{margin-bottom:1.1rem;}
.btn{width:100%;padding:.88rem;background:var(--green);color:#fff;border:none;border-radius:11px;font-size:.95rem;font-weight:700;font-family:inherit;cursor:pointer;transition:background .2s;margin-top:.25rem;}
.btn:hover{background:var(--green2);}
.error{background:#fef2f2;color:var(--red);border:1px solid #fca5a5;border-radius:9px;padding:.7rem 1rem;font-size:.82rem;margin-bottom:1rem;}
.footer-row{text-align:center;margin-top:1.2rem;font-size:.8rem;color:#9ca3af;}
.footer-row a{color:var(--green);font-weight:600;text-decoration:none;}
.hint{background:#f0fdf4;border:1px solid #86efac;border-radius:9px;padding:.7rem 1rem;font-size:.78rem;color:#166534;margin-bottom:1rem;line-height:1.6;}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <div class="card-top">
      <div class="emblem">🏛️</div>
      <h1>Barangay Saraet</h1>
      <p>Himamaylan City — Staff Portal</p>
    </div>
    <div class="card-body">
      <div class="hint">
        <strong>Demo credentials:</strong><br>
        Email: <code>staff@saraet.gov.ph</code><br>
        Password: <code>Admin@1234</code>
      </div>
      <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="field"><label>Email Address</label><input type="email" name="email" required placeholder="staff@saraet.gov.ph" autocomplete="email"></div>
        <div class="field"><label>Password</label><input type="password" name="password" required placeholder="••••••••" autocomplete="current-password"></div>
        <button class="btn" type="submit">Sign In to Dashboard</button>
      </form>
      <div class="footer-row">Resident? <a href="../mobile/login.php">Use the Mobile Portal →</a></div>
      <div class="footer-row" style="margin-top:.5rem"><a href="../index.php">← Back to Home</a></div>
    </div>
  </div>
</div>
</body>
</html>
