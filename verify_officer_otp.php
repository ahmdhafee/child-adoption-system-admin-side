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
</head>
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
