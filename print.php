<?php
date_default_timezone_set('Africa/Algiers');
session_start();
require_once __DIR__ . '/config/db.php';

// Session guard — redirect to login if not authenticated
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// Only admins and delegates can print
$userRole = $_SESSION['user']['role'] ?? '';
if ($userRole !== 'admin' && $userRole !== 'delegate') {
    http_response_code(403);
    exit('<p style="font-family:sans-serif;text-align:center;margin-top:100px;">⛔ غير مصرح لك بالوصول إلى صفحة الطباعة.</p>');
}

// ── Input parameters ────────────────────────────────────────────────
$date        = $_GET['date']     ?? date('Y-m-d');
$jurType     = $_GET['jur_type'] ?? 'court';
$jurName     = $_GET['jur_name'] ?? '';
$jurSub      = $_GET['jur_sub']  ?? '';

// Basic sanity-check date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

// ── Fetch requests from DB ───────────────────────────────────────────
$rows = dbFetchAll(
    "SELECT * FROM requests
     WHERE is_archived = 0
       AND jurisdiction_type  = ?
       AND jurisdiction_name  = ?
       AND jurisdiction_sub_entity = ?
       AND session_date       = ?
     ORDER BY created_at ASC",
    [$jurType, $jurName, $jurSub, $date]
);

// ── Replication of JS sorting + deduplication logic ─────────────────
// getSeniorityScore: syndicate member = 0, else oath year (lower = older = higher priority)
function getSeniorityScore(array $r): int {
    if ($r['is_syndicate_member']) return 0;
    $year = (int) $r['oath_date'];
    return ($year > 1900 && $year < 2100) ? $year : 9999;
}

usort($rows, function ($a, $b) {
    // 1. delay before advance
    if ($a['purpose'] !== $b['purpose']) {
        return $a['purpose'] === 'delay' ? -1 : 1;
    }
    // 2. seniority (lower score = more senior)
    $sa = getSeniorityScore($a);
    $sb = getSeniorityScore($b);
    if ($sa !== $sb) return $sa - $sb;
    // 3. first submitted first
    return $a['created_at'] - $b['created_at'];
});

// Deduplicate: one entry per caseNumber+purpose (keep most senior = first after sort)
$unique = [];
$seen   = [];
foreach ($rows as $r) {
    $key = $r['case_number'] . '_' . $r['purpose'];
    if (!isset($seen[$key])) {
        $unique[] = $r;
        $seen[$key] = true;
    }
}

// ── Helpers ─────────────────────────────────────────────────────────
$displayDate = (new DateTime($date))->format('Y/m/d');
$dayNames = ['الأحد','الإثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
$dayOfWeek = $dayNames[(int)(new DateTime($date))->format('w')];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>طباعة القائمة — <?= htmlspecialchars($jurName) ?> — <?= $displayDate ?></title>

  <!-- Cairo Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">

  <style>
    /* ── Reset & base ───────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Cairo', sans-serif;
      background: #e5e7eb;
      color: #000;
      direction: rtl;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* ── Screen: print button toolbar ──────────────── */
    .print-toolbar {
      background: #1a1a2e;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 28px;
      position: sticky;
      top: 0;
      z-index: 100;
      gap: 16px;
    }
    .print-toolbar h1 {
      font-size: 15px;
      font-weight: 700;
      opacity: .85;
    }
    .print-toolbar .toolbar-actions { display: flex; gap: 10px; }
    .btn-print {
      background: #059669;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 9px 22px;
      font-family: 'Cairo', sans-serif;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background .2s;
    }
    .btn-print:hover { background: #047857; }
    .btn-close {
      background: transparent;
      color: #aaa;
      border: 1px solid #444;
      border-radius: 8px;
      padding: 9px 18px;
      font-family: 'Cairo', sans-serif;
      font-size: 14px;
      cursor: pointer;
      transition: all .2s;
    }
    .btn-close:hover { background: #333; color: #fff; }

    /* ── Screen: A4 preview wrapper ────────────────── */
    .page-wrapper {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 32px 20px 60px;
      min-height: calc(100vh - 60px);
    }

    /* ── A4 page ────────────────────────────────────── */
    .a4-page {
      background: #fff;
      width: 210mm;
      min-height: 297mm;
      padding: 12mm 10mm;
      box-shadow: 0 4px 40px rgba(0,0,0,.25);
      position: relative;
    }

    /* ── Double-border frame ───────────────────────── */
    .page-frame {
      border: 1.5px solid #000;
      padding: 3px;
      min-height: calc(297mm - 24mm);
    }
    .page-inner {
      /* border: 2px solid #000; */
      padding: 14px 18px;
      min-height: calc(297mm - 36mm);
      display: flex;
      flex-direction: column;
    }

    /* ── Header grid ────────────────────────────────── */
    .header-grid {
      display: grid;
      grid-template-columns: 1fr 1.3fr 1fr;
      align-items: start;
      width: 100%;
      margin-bottom: 14px;
    }
    .header-side {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    .header-center {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding-top: 8px;
    }
    .header-center .org-title {
      font-size: 13.5pt;
      font-weight: 900;
      line-height: 1.35;
      margin-bottom: 4px;
    }
    .header-center .court-title {
      font-size: 11pt;
      font-weight: 700;
    }
    .stamp-box {
      width: 88px;
      height: 88px;
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 6px;
    }
    .stamp-box svg { width: 100%; height: 100%; }
    .side-label {
      font-size: 10pt;
      font-weight: 700;
      margin-top: 2px;
    }
    .side-date {
      font-size: 10.5pt;
      font-weight: 700;
      border-bottom: 1.5px solid #000;
      padding-bottom: 2px;
      margin-top: 4px;
    }

    /* ── List title ─────────────────────────────────── */
    .list-title {
      text-align: center;
      font-size: 14pt;
      font-weight: 900;
      margin: 16px 0 20px;
    }

    /* ── Print table ────────────────────────────────── */
    .print-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      flex: 1;
    }
    .print-table th,
    .print-table td {
      border: 1.5px solid #000;
      padding: 7px 5px;
      font-size: 10pt;
      text-align: center;
      vertical-align: middle;
    }
    .print-table th {
      font-weight: 900;
      background: #f3f4f6;
    }
    .print-table td { font-weight: 700; }
    .print-table td.mono { font-family: 'Courier New', monospace; font-size: 10.5pt; }
    .print-table tbody tr:nth-child(even) td { background: #fafafa; }

    /* Section break row */
    .section-row td {
      background: #e5e7eb !important;
      font-weight: 900;
      font-size: 9.5pt;
      letter-spacing: .5px;
      border-top: 2px solid #000;
      border-bottom: 2px solid #000;
    }

    /* ── Footer ─────────────────────────────────────── */
    .page-footer {
      margin-top: auto;
      padding-top: 14px;
      text-align: center;
      font-size: 8pt;
      color: #555;
      border-top: 1px solid #ccc;
    }

    /* ── Empty state ────────────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #6b7280;
      font-size: 12pt;
    }

    /* ── @media print ──────────────────────────────── */
    @media print {
      @page {
        size: A4 portrait;
        margin: 0;
      }
      body { background: #fff !important; }
      .print-toolbar { display: none !important; }
      .page-wrapper {
        padding: 0;
        background: #fff;
        display: block;
      }
      .a4-page {
        width: 210mm;
        min-height: 297mm;
        padding: 10mm 8mm;
        box-shadow: none;
        margin: 0 auto;
      }
    }
  </style>
</head>
<body>

  <!-- Toolbar (hidden on print) -->
  <div class="print-toolbar">
    <h1>
      🖨️ معاينة الطباعة — <?= htmlspecialchars($jurName) ?>
      <?php if ($jurSub): ?> / <?= htmlspecialchars($jurSub) ?><?php endif; ?>
      — <?= $dayOfWeek ?> <?= $displayDate ?>
    </h1>
    <div class="toolbar-actions">
      <button class="btn-close" onclick="window.close()">✕ إغلاق</button>
      <button class="btn-print" onclick="window.print()">
        🖨️ طباعة / حفظ PDF
      </button>
    </div>
  </div>

  <!-- A4 preview -->
  <div class="page-wrapper">
    <div class="a4-page">
      <div class="page-frame">
        <div class="page-inner">

          <!-- Header -->
          <div class="header-grid">

            <!-- Right: Delegate stamp + date -->
            <div class="header-side">
              <div class="stamp-box">
                <svg viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="48" fill="none" stroke="#000" stroke-width="1.5"/>
                  <circle cx="50" cy="50" r="44" fill="none" stroke="#000" stroke-width="1" stroke-dasharray="1.5 1.5"/>
                  <circle cx="50" cy="50" r="34" fill="none" stroke="#000" stroke-width="1"/>
                  <text x="50" y="24" font-size="6" font-family="Cairo,sans-serif" font-weight="bold" fill="#000" text-anchor="middle">منظمة محامي البليدة</text>
                  <line x1="20" y1="30" x2="80" y2="30" stroke="#000" stroke-width=".75"/>
                  <text x="50" y="46" font-size="8" font-family="Cairo,sans-serif" font-weight="900" fill="#000" text-anchor="middle">مندوب</text>
                  <text x="50" y="58" font-size="8" font-family="Cairo,sans-serif" font-weight="900" fill="#000" text-anchor="middle">النقيب</text>
                  <line x1="20" y1="66" x2="80" y2="66" stroke="#000" stroke-width=".75"/>
                  <text x="50" y="78" font-size="5" font-family="Cairo,sans-serif" fill="#000" text-anchor="middle">31 نهج كريتلي مختار</text>
                  <text x="50" y="86" font-size="5" font-family="Cairo,sans-serif" fill="#000" text-anchor="middle">البليدة</text>
                </svg>
              </div>
              <div class="side-date">جلسة: <?= $displayDate ?></div>
            </div>

            <!-- Center: organization titles -->
            <div class="header-center">
              <div class="org-title">**** نقابة المحامين البليدة ****</div>
              <div class="org-title">*** مندوبية <?= htmlspecialchars($jurName) ?> ***</div>
            </div>

            <!-- Left: Bar association stamp + court info -->
            <div class="header-side">
              <div class="stamp-box">
                <svg viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="48" fill="none" stroke="#000" stroke-width="1.5"/>
                  <circle cx="50" cy="50" r="44" fill="none" stroke="#000" stroke-width="1" stroke-dasharray="1.5 1.5"/>
                  <circle cx="50" cy="50" r="34" fill="none" stroke="#000" stroke-width="1"/>
                  <text x="50" y="24" font-size="6.5" font-family="Cairo,sans-serif" font-weight="bold" fill="#000" text-anchor="middle">منظمة محامي البليدة</text>
                  <line x1="20" y1="30" x2="80" y2="30" stroke="#000" stroke-width=".75"/>
                  <g transform="translate(38,36) scale(.18)" stroke="#000" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 25L54 25" stroke-width="5"/>
                    <path d="M32 12L32 55" stroke-width="5"/>
                    <path d="M22 55L42 55" stroke-width="5"/>
                    <path d="M10 25L3 43L17 43Z"/>
                    <path d="M54 25L47 43L61 43Z"/>
                  </g>
                  <line x1="20" y1="68" x2="80" y2="68" stroke="#000" stroke-width=".75"/>
                  <text x="50" y="78" font-size="6.5" font-family="Cairo,sans-serif" font-weight="bold" fill="#000" text-anchor="middle">مندوبية البليدة</text>
                  <text x="50" y="86" font-size="5" font-family="Cairo,sans-serif" fill="#000" text-anchor="middle">مجلس قضاء البليدة</text>
                </svg>
              </div>
              <div class="side-label"><?= htmlspecialchars($jurName) ?></div>
              <div class="side-label"><?= htmlspecialchars($jurSub) ?></div>
            </div>

          </div><!-- /header-grid -->

          <!-- List title -->
          <div class="list-title">*** قائمة أسبقية المحامين ***</div>

          <!-- Table -->
          <?php if (empty($unique)): ?>
            <div class="empty-state">
              لا توجد طلبات مسجلة لهذه الجلسة
            </div>
          <?php else: ?>
          <table class="print-table">
            <thead>
              <tr>
                <th style="width:6%">الرقم</th>
                <th style="width:16%">رقم القضية</th>
                <th style="width:37%">الأطراف</th>
                <th style="width:26%">الأستاذ (ة)</th>
                <th style="width:15%">أداء اليمين</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ($unique as $idx => $req):
              ?>
              <tr>
                <td><?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></td>
                <td class="mono"><?= htmlspecialchars($req['case_number']) ?></td>
                <td><?= htmlspecialchars($req['parties']) ?></td>
                <td><?= htmlspecialchars($req['lawyer_name']) ?></td>
                <td>
                  <?php if ($req['purpose'] === 'delay'): ?>
                    تأجيل
                  <?php elseif ($req['is_syndicate_member']): ?>
                    <strong>عضو نقابة</strong>
                  <?php else: ?>
                    <?= htmlspecialchars($req['oath_date']) ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>

          <!-- Footer -->
          <div class="page-footer">
            <p>منظمة محامي البليدة — النظام الرقمي لإدارة الجلسات © <?= date('Y') ?></p>
            <p>تمت الطباعة: <?= date('Y/m/d H:i') ?> | الجلسة: <?= $dayOfWeek ?> <?= $displayDate ?></p>
          </div>

        </div><!-- /page-inner -->
      </div><!-- /page-frame -->
    </div><!-- /a4-page -->
  </div><!-- /page-wrapper -->

</body>
</html>
