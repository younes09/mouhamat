  <!-- ==================== OFFICIAL PRINT TEMPLATE (HIDDEN ON SCREEN, SHOWN ON PRINT) ==================== -->
  <!-- Off-screen but always rendered so html2canvas can capture it for PDF -->
  <div id="officialPrintTemplate" class="print-page-frame" style="position: fixed; left: -9999px; top: 0; width: 794px; z-index: -1; background: #ffffff;">
    <div class="print-page-inner-frame">
      
      <!-- Top header layout -->
      <div class="print-header-grid">
        
        <!-- Left Side: Date + Stamp -->
        <div class="print-header-side">
          <div class="print-stamp-container">
            <!-- Left SVG Stamp: مندوب النقيب -->
            <svg width="100" height="100" viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="48" fill="none" stroke="#000000" stroke-width="1.5" />
              <circle cx="50" cy="50" r="44" fill="none" stroke="#000000" stroke-width="1" stroke-dasharray="1.5 1.5" />
              <circle cx="50" cy="50" r="34" fill="none" stroke="#000000" stroke-width="1" />
              
              <text x="50" y="24" font-size="6" font-family="'Cairo', sans-serif" font-weight="bold" fill="#000000" text-anchor="middle">منظمة محامي البليدة</text>
              <line x1="20" y1="30" x2="80" y2="30" stroke="#000000" stroke-width="0.75" />
              
              <text x="50" y="46" font-size="8" font-family="'Cairo', sans-serif" font-weight="black" fill="#000000" text-anchor="middle">مندوب</text>
              <text x="50" y="58" font-size="8" font-family="'Cairo', sans-serif" font-weight="black" fill="#000000" text-anchor="middle">النقيب</text>
              
              <line x1="20" y1="66" x2="80" y2="66" stroke="#000000" stroke-width="0.75" />
              <text x="50" y="78" font-size="5" font-family="'Cairo', sans-serif" fill="#000000" text-anchor="middle">31 نهج كريتلي مختار</text>
              <text x="50" y="86" font-size="5" font-family="'Cairo', sans-serif" fill="#000000" text-anchor="middle">البليدة</text>
            </svg>
          </div>
          <!-- Dynamic session date -->
          <div class="print-header-date-box">
            جلسة: <span id="printSessionDate">2026/06/15</span>
          </div>
        </div>

        <!-- Center Header Title Lines -->
        <div class="print-header-center">
          <h4>**** نقابة المحامين البليدة ***</h4>
          <h5 id="printCenterSubTitle">*** مندوبية محكمة البليدة ***</h5>
        </div>

        <!-- Right Side: Stamp + Dynamic Court/Sub-entity -->
        <div class="print-header-side">
          <div class="print-stamp-container">
            <!-- Right SVG Stamp: منظمة محامي البليدة / مندوبية البليدة -->
            <svg width="100" height="100" viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="48" fill="none" stroke="#000000" stroke-width="1.5" />
              <circle cx="50" cy="50" r="44" fill="none" stroke="#000000" stroke-width="1" stroke-dasharray="1.5 1.5" />
              <circle cx="50" cy="50" r="34" fill="none" stroke="#000000" stroke-width="1" />
              
              <text x="50" y="24" font-size="6.5" font-family="'Cairo', sans-serif" font-weight="bold" fill="#000000" text-anchor="middle">منظمة محامي البليدة</text>
              <line x1="20" y1="30" x2="80" y2="30" stroke="#000000" stroke-width="0.75" />
              
              <g transform="translate(38, 36) scale(0.18)" stroke="#000000" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M 10 25 L 54 25" stroke-width="5" />
                <path d="M 32 12 L 32 55" stroke-width="5" />
                <path d="M 22 55 L 42 55" stroke-width="5" />
                <path d="M 10 25 L 3 43 L 17 43 Z" />
                <path d="M 54 25 L 47 43 L 61 43 Z" />
              </g>
              
              <line x1="20" y1="68" x2="80" y2="68" stroke="#000000" stroke-width="0.75" />
              <text x="50" y="78" font-size="6.5" font-family="'Cairo', sans-serif" font-weight="bold" fill="#000000" text-anchor="middle">مندوبية البليدة</text>
              <text x="50" y="86" font-size="5" font-family="'Cairo', sans-serif" fill="#000000" text-anchor="middle">مجلس قضاء البليدة</text>
            </svg>
          </div>
          <!-- Dynamic Jurisdiction and sub-entity -->
          <div class="print-header-subtext" id="printJurisdictionName">محكمة البليدة</div>
          <div class="print-header-subtext" id="printJurisdictionSubEntity">قسم الجنح</div>
        </div>

      </div>

      <!-- Main Title -->
      <div class="print-list-title">*** قائمة أسبقية المحامين ***</div>

      <!-- Main Print Table -->
      <table class="print-table">
        <thead>
          <tr>
            <th style="width: 7%;">الرقم</th>
            <th style="width: 15%;">رقم القضية</th>
            <th style="width: 38%;">الأطراف</th>
            <th style="width: 25%;">الأستاذ (ة)</th>
            <th style="width: 15%;">أداء اليمين</th>
          </tr>
        </thead>
        <tbody id="printTableBody">
          <!-- Rows will be injected here dynamically by script.js -->
        </tbody>
      </table>

    </div>
  </div>
