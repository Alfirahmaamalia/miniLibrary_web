<?php
session_start();
session_destroy();
header("Location:../users/index.php");
exit;
?>
