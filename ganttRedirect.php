<?php
require 'authentication.php'; 
require 'conn.php';

$uid  = $_GET['user_id'] ?? '';
$role = $_GET['user_role'] ?? '';
$uname = '';

if (!empty($uid)) {
    $stmt = $conn->prepare("SELECT fullname FROM tbl_admin WHERE user_id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($uname);
    $stmt->fetch();
    $stmt->close();
}
$conn->close();

// 🔐 Add randomness (salt) so encoded string changes every time
function encodeWithSalt($value) {
    $salt = bin2hex(random_bytes(4)); // random 8-character salt
    $data = $value . "|" . $salt;     // append salt to original value
    return base64_encode($data);
}

$uidEnc   = encodeWithSalt($uid);
$roleEnc  = encodeWithSalt($role);
$unameEnc = encodeWithSalt($uname);
?>
<script>
  const uid   = "<?php echo $uidEnc; ?>";
  const role  = "<?php echo $roleEnc; ?>";
  const uname = "<?php echo $unameEnc; ?>";

  // Store in sessionStorage (encoded)
  sessionStorage.setItem("uid", uid);
  sessionStorage.setItem("role", role);
  sessionStorage.setItem("uname", uname);

  // ✅ Redirect with encoded values
  window.location.href = `https://gantt.csaappstore.com/?uid=${encodeURIComponent(uid)}&role=${encodeURIComponent(role)}&uname=${encodeURIComponent(uname)}`;
</script>
