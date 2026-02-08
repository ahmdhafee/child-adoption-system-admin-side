<?php
session_start();
if (!isset($_SESSION['pending_officer_id'])) {
    header("Location: officeLogin.html");
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Verify OTP | Chief Officer</title>
  <link rel="stylesheet" href="/chief_officer/css/index.css">
</head>
<style>
  :root{
    --primary:#2C3E50;
    --secondary:#34495E;
    --accent:#3498DB;
    --info:#2980B9;
    --success:#27AE60;
    --danger:#E74C3C;
    --dark:#1A252F;
    --gray:#7F8C8D;
    --light-gray:#F8F9FA;
    --shadow: 0 10px 40px rgba(0,0,0,0.15);
    --radius:16px;
  }

  /* ✅ FORCE vertical layout */
  body{
    margin:0 !important;
    min-height:100vh !important;
    background: linear-gradient(135deg, var(--primary), var(--secondary)) !important;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;

    display:flex !important;
    flex-direction:column !important;  /* ✅ important */
    align-items:center !important;
    justify-content:center !important;

    padding:40px 16px !important;
    overflow-x:hidden !important;
  }

  /* All blocks same width & centered */
  h2, p, form#otpForm, #msg{
    width:100% !important;
    max-width:520px !important;
    margin:0 !important;
  }

  /* Header */
  h2{
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color:#fff;
    padding:26px 28px;
    border-radius: var(--radius) var(--radius) 0 0;
    box-shadow: var(--shadow);
    position:relative;
  }
  h2::before{
    content:"";
    position:absolute;
    top:0; left:0; right:0;
    height:4px;
    background: linear-gradient(to right, var(--accent), var(--success));
  }

  /* Email strip */
  p{
    background:#fff;
    color: var(--gray);
    padding:14px 28px;
    box-shadow: var(--shadow);
    border-top: 1px solid rgba(0,0,0,0.06);
  }
  p b{ color: var(--dark); }

  /* Form block */
  form#otpForm{
    background:#fff;
    padding:22px 28px 28px;
    box-shadow: var(--shadow);
    border-radius: 0 0 var(--radius) var(--radius);
  }

  /* OTP Input */
  #otp{
    width:90%;
    margin: 10px;
    padding:14px 16px;
    border:2px solid var(--light-gray);
    border-radius:12px;
    font-size:16px;
    text-align:center;
    font-weight:700;
    letter-spacing:0.25em;
    outline:none;
  }
  #otp:focus{
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(52,152,219,0.12);
  }

  /* Button */
  button[type="submit"]{
    width:100%;
    margin-top:14px;
    padding:14px 16px;
    border:none;
    border-radius:12px;
    background: linear-gradient(135deg, var(--accent), var(--info));
    color:#fff;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    box-shadow: 0 4px 15px rgba(52,152,219,0.30);
    transition: .2s ease;
  }
  button[type="submit"]:hover{
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52,152,219,0.40);
  }

  /* ✅ Message: hide when empty */
  #msg:empty{ display:none; }

  #msg{
    margin-top:14px !important;
    padding:12px 14px;
    border-radius:12px;
    background: rgba(52,152,219,0.10);
    border-left:4px solid var(--info);
    color: var(--dark);
    max-width:520px !important;
  }

  @media (max-width:560px){
    h2{ padding:22px 18px; }
    p, form#otpForm{ padding-left:18px; padding-right:18px; }
  }
</style>



<body style="font-family:Arial; padding:30px;">
  <h2>Chief Officer OTP Verification</h2>
  <p>We sent an OTP to: <b><?php echo htmlspecialchars($_SESSION['pending_officer_email']); ?>
</b></p>

  <form id="otpForm">
    <input type="text" id="otp" placeholder="Enter 6-digit OTP" maxlength="6" required />
    <button type="submit">Verify</button>
  </form>

  <div id="msg" style="margin-top:15px;"></div>

  <script>
    document.getElementById('otpForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const otp = document.getElementById('otp').value.trim();

      const form = new FormData();
      form.append('otp', otp);

      const res = await fetch('verify_officer_otp_handler.php', { method: 'POST', body: form });
      const data = await res.json();

      document.getElementById('msg').textContent = data.message || '';

      if (data.success && data.redirect) {
        window.location.href = data.redirect;
      }
    });
  </script>
</body>
</html>
