{{-- Fondo animado: un auto que cruza por un camino con líneas de velocidad (solo CSS/SVG, sin video). --}}
<div class="auto-movimiento" aria-hidden="true">
  <span class="am-estela" style="top:18%;animation-duration:1.6s"></span>
  <span class="am-estela" style="top:34%;animation-duration:1.1s;animation-delay:-.4s"></span>
  <span class="am-estela" style="top:52%;animation-duration:1.9s;animation-delay:-.9s"></span>
  <span class="am-estela" style="top:66%;animation-duration:1.3s;animation-delay:-.2s"></span>
  <div class="am-camino"></div>
  <svg class="am-auto" viewBox="0 0 230 80">
    <polygon class="am-haz" points="212,44 230,32 230,62 212,50" fill="#fff6c2" opacity=".45"/>
    <path d="M12 60 L12 47 Q14 40 26 38 L64 34 Q82 17 104 13 L142 13 Q162 15 180 31 L204 35 Q214 37 214 48 L214 60 Z" fill="#d90718"/>
    <path d="M26 38 L204 35 L206 40 L20 43 Z" fill="#ff4d58" opacity=".55"/>
    <path d="M74 34 Q88 21 104 19 L120 19 L120 34 Z" fill="#1b1c25" opacity=".85"/>
    <path d="M126 19 L141 19 Q156 21 168 34 L126 34 Z" fill="#1b1c25" opacity=".85"/>
    <rect x="207" y="41" width="7" height="5" rx="2" fill="#fff6c2"/>
    <rect x="12" y="42" width="6" height="5" rx="2" fill="#ffd0d3"/>
    <g class="am-rueda"><circle cx="56" cy="60" r="14" fill="#111"/><circle cx="56" cy="60" r="7" fill="#9ca3af"/><path d="M56 53 V67 M49 60 H63" stroke="#111" stroke-width="2"/></g>
    <g class="am-rueda"><circle cx="176" cy="60" r="14" fill="#111"/><circle cx="176" cy="60" r="7" fill="#9ca3af"/><path d="M176 53 V67 M169 60 H183" stroke="#111" stroke-width="2"/></g>
  </svg>
</div>
