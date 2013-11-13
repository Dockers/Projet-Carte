<?php
session_start();
if(isset($_SESSION['user']['id'])):
?>
Bonjour, <?php echo $_SESSION['user']['login']; ?>
<?php else : ?>
<a href="connect.php">Se connecter avec Facebook</a>
<?php endif; ?>