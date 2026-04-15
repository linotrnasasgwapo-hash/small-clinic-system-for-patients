<?php
// mobile/register.php
require_once '../includes/auth.php';
require_once '../config/db.php';
$error = $success = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $db  = getDB();
    $fn  = clean($db,$_POST['first_name']??'');
    $ln  = clean($db,$_POST['last_name']??'');
    $mn  = clean($db,$_POST['middle_name']??'');
    $em  = clean($db,$_POST['email']??'');
    $ph  = clean($db,$_POST['phone']??'');
    $bd  = clean($db,$_POST['birthdate']??'');
    $gn  = clean($db,$_POST['gender']??'');
    $cs  = clean($db,$_POST['civil_status']??'Single');
    $pur = clean($db,$_POST['purok']??'');
    $add = clean($db,$_POST['address']??'');
    $pw  = $_POST['password']??'';
    $pw2 = $_POST['password2']??'';
    if ($pw !== $pw2) $error = 'Passwords do not match.';
    elseif (strlen($pw)<6) $error = 'Password must be at least 6 characters.';
    else {
        $hash = password_hash($pw, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO residents (first_name,last_name,middle_name,email,password,phone,address,birthdate,gender,civil_status,purok) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssssssss',$fn,$ln,$mn,$em,$hash,$ph,$add,$bd,$gn,$cs,$pur);
        $stmt->execute() ? $success='Account created! You can now log in.' : $error='Email already registered or an error occurred.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>Register — Barangay Saraet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--green:#1a5c3a;--green2:#0f3d27;--border:#d4e4da;--red:#dc2626;--bg:#f5f8f6;--text:#1a2e23;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);min-height:100vh;padding:1rem;display:flex;flex-direction:column;align-items:center;}
.wrap{width:100%;max-width:390px;margin:auto;}
.hero{background:linear-gradient(150deg,var(--green2),var(--green));border-radius:24px 24px 0 0;padding:2rem 2rem 1.5rem;text-align:center;}
.hero h1{font-size:1.25rem;font-weight:800;color:#fff;}
.hero p{font-size:.75rem;color:rgba(255,255,255,.65);margin-top:.3rem;}
.form-card{background:#fff;border-radius:0 0 24px 24px;padding:1.75rem;box-shadow:0 12px 40px rgba(0,0,0,.1);}
label{display:block;font-size:.72rem;font-weight:700;color:var(--text);margin-bottom:.32rem;text-transform:uppercase;letter-spacing:.04em;}
input,select{width:100%;padding:.72rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.88rem;font-family:inherit;outline:none;transition:border-color .2s;background:#fff;}
input:focus,select:focus{border-color:var(--green);}
.field{margin-bottom:.9rem;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:.65rem;}
.btn{width:100%;padding:.87rem;background:var(--green);color:#fff;border:none;border-radius:13px;font-size:.98rem;font-weight:800;cursor:pointer;margin-top:.4rem;font-family:inherit;}
.btn:hover{background:var(--green2);}
.error{background:#fef2f2;color:var(--red);border:1px solid #fca5a5;border-radius:10px;padding:.7rem;font-size:.82rem;margin-bottom:.9rem;}
.success{background:#f0fdf4;color:#166534;border:1px solid #86efac;border-radius:10px;padding:.7rem;font-size:.82rem;margin-bottom:.9rem;}
.links{text-align:center;margin-top:1rem;font-size:.8rem;color:#9ca3af;}
.links a{color:var(--green);font-weight:700;text-decoration:none;}
.section-hd{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--green);margin:.9rem 0 .6rem;padding-bottom:.4rem;border-bottom:1px solid #e5e7eb;}
</style>
</head>
<body>
<div class="wrap">
  <div class="hero">
    <h1>Create Account</h1>
    <p>Register as a Barangay Saraet resident to book appointments</p>
  </div>
  <div class="form-card">
    <?php if($error):?><div class="error"><?=htmlspecialchars($error)?></div><?php endif;?>
    <?php if($success):?><div class="success"><?=$success?> <a href="login.php">Login →</a></div><?php endif;?>
    <?php if(!$success):?>
    <form method="POST">
      <div class="section-hd">Personal Information</div>
      <div class="row2">
        <div class="field"><label>First Name *</label><input name="first_name" required placeholder="Juan"></div>
        <div class="field"><label>Last Name *</label><input name="last_name" required placeholder="Cruz"></div>
      </div>
      <div class="field"><label>Middle Name</label><input name="middle_name" placeholder="Optional"></div>
      <div class="row2">
        <div class="field"><label>Birthdate</label><input type="date" name="birthdate"></div>
        <div class="field"><label>Gender</label>
          <select name="gender"><option value="">Select</option><option>Male</option><option>Female</option><option>Other</option></select>
        </div>
      </div>
      <div class="row2">
        <div class="field"><label>Civil Status</label>
          <select name="civil_status"><option>Single</option><option>Married</option><option>Widowed</option><option>Separated</option></select>
        </div>
        <div class="field"><label>Purok</label><input name="purok" placeholder="Purok 1"></div>
      </div>
      <div class="field"><label>Full Address</label><input name="address" placeholder="Barangay Saraet, Himamaylan"></div>

      <div class="section-hd">Contact & Login</div>
      <div class="field"><label>Email Address *</label><input type="email" name="email" required placeholder="juan@email.com"></div>
      <div class="field"><label>Phone Number</label><input type="tel" name="phone" placeholder="09171234567"></div>
      <div class="row2">
        <div class="field"><label>Password *</label><input type="password" name="password" required placeholder="Min 6 chars"></div>
        <div class="field"><label>Confirm *</label><input type="password" name="password2" required placeholder="Repeat"></div>
      </div>
      <button class="btn" type="submit">Create Account</button>
    </form>
    <?php endif;?>
    <div class="links">Already registered? <a href="login.php">Sign in</a></div>
  </div>
</div>
</body>
</html>
