<?php
$uid = $_GET['uid'] ?? '';
$uname = $_GET['uname'] ?? '';
$role = $_GET['role'] ?? '';
?>
<script>
  const uid = "<?php echo $uid; ?>";
  const uname = "<?php echo $uname; ?>";
  const role = "<?php echo $role; ?>";

  // Store in sessionStorage
  sessionStorage.setItem("uid", uid);
  sessionStorage.setItem("uname", uname);
  sessionStorage.setItem("role", role);

  
  // Redirect with query params
  window.location.href = `http://localhost:3000/?uid=${uid}&uname=${uname}&role=${role}`;
</script>
