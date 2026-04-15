<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Saraet Barangay Service Center — Himamaylan City</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --green:#1a5c3a; --green2:#0f3d27; --green-mid:#2a7d50; --green-light:#e6f4ec;
  --gold:#c9933a; --gold-light:#fdf3e3;
  --white:#fff; --bg:#f5f8f6; --text:#1a2e23; --gray:#5a6a62; --border:#d4e4da;
}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);}

/* NAVBAR */
nav{background:var(--green2);padding:.85rem 2rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 12px rgba(0,0,0,.2);}
.nav-brand{display:flex;align-items:center;gap:.75rem;text-decoration:none;}
.nav-logo{width:38px;height:38px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:1.1rem;}
.nav-brand-text h1{font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:#fff;line-height:1.1;}
.nav-brand-text p{font-size:.65rem;color:rgba(255,255,255,.6);}
.nav-links{display:flex;align-items:center;gap:.5rem;}
.nav-link{padding:.5rem 1rem;border-radius:8px;font-size:.82rem;font-weight:500;text-decoration:none;color:rgba(255,255,255,.8);transition:background .15s;}
.nav-link:hover{background:rgba(255,255,255,.1);color:#fff;}
.nav-btn{padding:.5rem 1.25rem;border-radius:8px;font-size:.82rem;font-weight:600;text-decoration:none;background:var(--gold);color:#fff;transition:background .15s;}
.nav-btn:hover{background:#b07c2b;}

/* HERO */
.hero{background:linear-gradient(145deg,var(--green2) 0%,var(--green) 60%,var(--green-mid) 100%);padding:5rem 2rem 4rem;text-align:center;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");}
.hero-badge{display:inline-block;padding:.35rem 1rem;background:rgba(201,147,58,.25);border:1px solid rgba(201,147,58,.5);border-radius:20px;font-size:.72rem;font-weight:600;color:#f5c97a;letter-spacing:.08em;text-transform:uppercase;margin-bottom:1.5rem;}
.hero h1{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,5vw,3rem);color:#fff;font-weight:700;line-height:1.2;max-width:700px;margin:0 auto .75rem;}
.hero p{font-size:1rem;color:rgba(255,255,255,.75);max-width:520px;margin:0 auto 2.5rem;line-height:1.7;}
.hero-cta{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;}
.btn-hero-primary{padding:.85rem 2rem;background:var(--gold);color:#fff;border-radius:12px;font-weight:700;font-size:.95rem;text-decoration:none;transition:background .2s,transform .15s;}
.btn-hero-primary:hover{background:#b07c2b;transform:translateY(-2px);}
.btn-hero-secondary{padding:.85rem 2rem;background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:12px;font-weight:600;font-size:.95rem;text-decoration:none;transition:background .2s;}
.btn-hero-secondary:hover{background:rgba(255,255,255,.2);}

/* STATS */
.stats-strip{background:var(--white);border-bottom:1px solid var(--border);padding:1.5rem 2rem;}
.stats-inner{max-width:900px;margin:0 auto;display:flex;justify-content:space-around;flex-wrap:wrap;gap:1rem;}
.stat-item{text-align:center;}
.stat-item .num{font-size:1.75rem;font-weight:700;color:var(--green);}
.stat-item .lbl{font-size:.75rem;color:var(--gray);font-weight:500;}

/* SERVICES */
.section{padding:4rem 2rem;max-width:1000px;margin:0 auto;}
.section-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gold);margin-bottom:.5rem;}
.section h2{font-family:'Playfair Display',serif;font-size:1.9rem;font-weight:700;color:var(--text);margin-bottom:.75rem;}
.section .sub{color:var(--gray);font-size:.92rem;max-width:540px;line-height:1.7;margin-bottom:2.5rem;}
.services-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.1rem;}
.svc-card{background:var(--white);border-radius:16px;padding:1.4rem;border:1px solid var(--border);transition:border-color .2s,transform .2s,box-shadow .2s;text-decoration:none;display:block;}
.svc-card:hover{border-color:var(--green-mid);transform:translateY(-3px);box-shadow:0 8px 24px rgba(26,92,58,.1);}
.svc-icon{font-size:1.75rem;margin-bottom:.75rem;}
.svc-name{font-weight:600;font-size:.92rem;color:var(--text);margin-bottom:.3rem;}
.svc-desc{font-size:.78rem;color:var(--gray);line-height:1.5;}
.svc-badge{display:inline-block;margin-top:.6rem;padding:.2rem .65rem;border-radius:20px;font-size:.68rem;font-weight:600;}
.cat-document{background:#eff6ff;color:#1d4ed8;}
.cat-health{background:#f0fdf4;color:#166534;}
.cat-social{background:#fef3c7;color:#92400e;}
.cat-legal{background:#fdf2f8;color:#9d174d;}
.cat-financial{background:#f0fdf4;color:#065f46;}

/* HOW IT WORKS */
.steps{background:var(--green2);padding:4rem 2rem;}
.steps-inner{max-width:900px;margin:0 auto;text-align:center;}
.steps-inner .section-label{color:#f5c97a;}
.steps-inner h2{font-family:'Playfair Display',serif;font-size:1.9rem;color:#fff;margin-bottom:.75rem;}
.steps-inner p{color:rgba(255,255,255,.7);max-width:480px;margin:0 auto 3rem;}
.steps-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.5rem;text-align:left;}
.step-card{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:1.5rem;}
.step-num{width:36px;height:36px;border-radius:50%;background:var(--gold);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;margin-bottom:1rem;}
.step-card h4{color:#fff;font-size:.92rem;font-weight:600;margin-bottom:.4rem;}
.step-card p{color:rgba(255,255,255,.6);font-size:.78rem;line-height:1.6;}

/* FOOTER */
footer{background:var(--text);padding:2.5rem 2rem;text-align:center;}
footer p{color:rgba(255,255,255,.5);font-size:.78rem;}
footer a{color:var(--gold);text-decoration:none;}
</style>
</head>
<body>

<nav>
  <a href="index.php" class="nav-brand">
    <div class="nav-logo">🏛️</div>
    <div class="nav-brand-text">
      <h1>Barangay Saraet</h1>
      <p>Himamaylan City</p>
    </div>
  </a>
  <div class="nav-links">
    <a href="#services" class="nav-link">Services</a>
    <a href="#how" class="nav-link">How it Works</a>
    <a href="admin/login.php" class="nav-link">Staff Login</a>
    <a href="mobile/login.php" class="nav-btn">📱 Resident Portal</a>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="hero-badge">🏛️ Official Barangay Portal</div>
  <h1>Saraet Barangay Service Center</h1>
  <p>Book appointments, skip the long queue, and access barangay services from your phone — anytime, anywhere.</p>
  <div class="hero-cta">
    <a href="mobile/register.php" class="btn-hero-primary">📱 Register as Resident</a>
    <a href="mobile/login.php" class="btn-hero-secondary">Sign In →</a>
  </div>
</div>

<!-- STATS -->
<div class="stats-strip">
  <div class="stats-inner">
    <div class="stat-item"><div class="num">12</div><div class="lbl">Available Services</div></div>
    <div class="stat-item"><div class="num">Mon–Fri</div><div class="lbl">Office Hours</div></div>
    <div class="stat-item"><div class="num">8AM–5PM</div><div class="lbl">Service Hours</div></div>
    <div class="stat-item"><div class="num">0%</div><div class="lbl">Long Lines</div></div>
  </div>
</div>

<!-- SERVICES -->
<div class="section" id="services">
  <div class="section-label">What We Offer</div>
  <h2>Barangay Services</h2>
  <p class="sub">From document requests to social assistance — all bookable online with a queue number assigned instantly.</p>
  <div class="services-grid">
    <a href="mobile/login.php" class="svc-card"><div class="svc-icon">📄</div><div class="svc-name">Barangay Clearance</div><div class="svc-desc">Official clearance for employment, travel, and legal purposes.</div><span class="svc-badge cat-document">Document</span></a>
    <a href="mobile/login.php" class="svc-card"><div class="svc-icon">📜</div><div class="svc-name">Certificate of Indigency</div><div class="svc-desc">For low-income residents needing assistance.</div><span class="svc-badge cat-document">Document</span></a>
    <a href="mobile/login.php" class="svc-card"><div class="svc-icon">🏠</div><div class="svc-name">Certificate of Residency</div><div class="svc-desc">Proof of residence in Barangay Saraet.</div><span class="svc-badge cat-document">Document</span></a>
    <a href="mobile/login.php" class="svc-card"><div class="svc-icon">🩺</div><div class="svc-name">Health Consultation</div><div class="svc-desc">Basic health assessment by barangay health worker.</div><span class="svc-badge cat-health">Health</span></a>
    <a href="mobile/login.php" class="svc-card"><div class="svc-icon">👨‍👩‍👧</div><div class="svc-name">4Ps / DSWD Assistance</div><div class="svc-desc">Conditional cash transfer and social welfare concerns.</div><span class="svc-badge cat-social">Social</span></a>
    <a href="mobile/login.php" class="svc-card"><div class="svc-icon">🧓</div><div class="svc-name">Senior Citizen Services</div><div class="svc-desc">OSCA services and senior citizen support.</div><span class="svc-badge cat-social">Social</span></a>
    <a href="mobile/login.php" class="svc-card"><div class="svc-icon">⚖️</div><div class="svc-name">Complaint / Blotter</div><div class="svc-desc">Filing of barangay blotter or formal complaints.</div><span class="svc-badge cat-legal">Legal</span></a>
    <a href="mobile/login.php" class="svc-card"><div class="svc-icon">💰</div><div class="svc-name">Financial Assistance</div><div class="svc-desc">Emergency financial aid request and processing.</div><span class="svc-badge cat-financial">Financial</span></a>
  </div>
</div>

<!-- HOW IT WORKS -->
<div class="steps" id="how">
  <div class="steps-inner">
    <div class="section-label">Easy Process</div>
    <h2>How It Works</h2>
    <p>Book your barangay appointment in 3 simple steps and track your queue from your phone.</p>
    <div class="steps-row">
      <div class="step-card"><div class="step-num">1</div><h4>Register as Resident</h4><p>Create your account using your email address. Free and quick to set up.</p></div>
      <div class="step-card"><div class="step-num">2</div><h4>Choose a Service</h4><p>Pick the barangay service you need and select an available schedule slot.</p></div>
      <div class="step-card"><div class="step-num">3</div><h4>Get Your Queue Number</h4><p>Instantly receive your queue number. Track your position live from the app.</p></div>
      <div class="step-card"><div class="step-num">4</div><h4>Come on Time</h4><p>Arrive at the barangay hall when it's nearly your turn. No more long waits!</p></div>
    </div>
  </div>
</div>

<footer>
  <p>© <?= date('Y') ?> Barangay Saraet, Himamaylan City, Negros Occidental &nbsp;|&nbsp; <a href="admin/login.php">Staff Portal</a> &nbsp;|&nbsp; <a href="mobile/login.php">Resident Portal</a></p>
</footer>

</body>
</html>
