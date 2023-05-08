<?php
// tabbar.php
?>
<div class="tabbar">
  <a href="index.php" class="tabbar-item">
    <i class="fas fa-home"></i>
    <span>Home</span>
  </a>
  <a href="scanner.php" class="tabbar-item">
    <i class="fas fa-qrcode"></i>
    <span>Check-in</span>
  </a>
  <a href="tickets.php" class="tabbar-item">
    <i class="fas fa-ticket"></i>
    <span>Tickets</span>
  </a>
  <a href="account.php" class="tabbar-item">
    <i class="fas fa-user"></i>
    <span>Profile</span>
  </a>
</div>

<style>
    .tabbar {
  display: flex;
  justify-content: space-around;
  align-items: center;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: #ffffff;
  border-top: 1px solid #cccccc;
  height: 60px;
  z-index: 100;
}

.tabbar-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  color: #000000;
  font-size: 12px;
}

.tabbar-item i {
  font-size: 20px;
  margin-bottom: 4px;
}

@media (min-width: 769px) {
  .tabbar {
    display: none;
  }
}

</style>