<?php
session_start();
session_unset();
session_destroy();
header("Location: ../landing-login/index.html?logout=success");
exit();
?>
