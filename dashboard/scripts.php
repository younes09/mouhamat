  <!-- ==================== EXTERNAL JS LIBRARIES ==================== -->

  <!-- Pre-inject state variables from session -->
  <script>
    window.preinjectedUser = <?php echo $user_json; ?>;
  </script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Flatpickr (Calendar component replacement) -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

  <!-- html2pdf.js for exporting lists -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

  <!-- QRCode.js for PDF list verifications -->
  <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Local logic controllers -->
  <script src="../script.js"></script>
  <script>
    if (window.preinjectedUser) {
      currentUser = window.preinjectedUser;
    }
  </script>
