<?php
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: ../index.php");
  exit;
}
$user_json = json_encode($_SESSION['user'], JSON_UNESCAPED_UNICODE);
?>
<?php include_once 'includes/head.php'; ?>

  <!-- ==================== AUTHENTICATED APP VIEWS CONTAINER ==================== -->
  <div id="appContainer" class="fade-in">

    <?php include_once 'includes/header.php'; ?>
    <?php include_once 'includes/nav.php'; ?>

    <!-- MAIN CONTENT VIEW WRAPPER -->
    <main class="max-w-5xl mx-auto px-4 py-4">
      <!-- Disclaimer Alert -->
      <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-start gap-2 text-start print-hidden" role="alert" style="background-color: var(--amber-50); color: var(--amber-warning-text); border: 1px solid var(--amber-200) !important;">
        <i data-lucide="alert-triangle" class="flex-shrink-0 mt-0.5" style="width: 20px; height: 20px; color: var(--amber-warning-text) !important;"></i>
        <div>
          <strong class="d-block mb-1" style="font-size: 0.95rem; color: var(--amber-warning-text) !important;">تنبيه هام:</strong>
          <span style="font-size: 0.85rem; line-height: 1.5; display: block; color: var(--amber-warning-text) !important;">
            هذا التطبيق <strong>تجريبي</strong> وليس له أي صلة رسمية أو قانونية بـ <strong>منظمة محامي البليدة</strong>.
          </span>
        </div>
      </div>

      <?php include_once 'includes/tab_requests.php'; ?>
      <?php include_once 'includes/tab_announcements.php'; ?>
      <?php include_once 'includes/tab_calendar.php'; ?>
      <?php include_once 'includes/tab_stats.php'; ?>
      <?php include_once 'includes/tab_users.php'; ?>
      <?php include_once 'includes/tab_settings.php'; ?>
      <?php include_once 'includes/tab_profile.php'; ?>
    </main>

  </div>

  <?php include_once 'includes/modals.php'; ?>
  <?php include_once 'includes/toast.php'; ?>
  <?php include_once 'includes/scripts.php'; ?>
</body>

</html>
