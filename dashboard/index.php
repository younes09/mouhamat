<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: ../index.php");
  exit;
}
$user_json = json_encode($_SESSION['user'], JSON_UNESCAPED_UNICODE);
?>
<?php include_once 'head.php'; ?>

  <!-- ==================== AUTHENTICATED APP VIEWS CONTAINER ==================== -->
  <div id="appContainer" class="fade-in">

    <?php include_once 'header.php'; ?>
    <?php include_once 'nav.php'; ?>

    <!-- MAIN CONTENT VIEW WRAPPER -->
    <main class="max-w-5xl mx-auto px-4 py-4">
      <?php include_once 'tab_requests.php'; ?>
      <?php include_once 'tab_announcements.php'; ?>
      <?php include_once 'tab_calendar.php'; ?>
      <?php include_once 'tab_stats.php'; ?>
      <?php include_once 'tab_users.php'; ?>
      <?php include_once 'tab_settings.php'; ?>
      <?php include_once 'tab_profile.php'; ?>
    </main>

  </div>

  <?php include_once 'modals.php'; ?>
  <?php include_once 'toast.php'; ?>
  <?php include_once 'scripts.php'; ?>
</body>

</html>
