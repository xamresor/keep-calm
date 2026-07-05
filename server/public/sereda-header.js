/* ============================================================
   sereda.lv — единый переключатель языков EN/LV/RU (для общего header'а).
   Канонический источник: lab/shared/sereda-header.js
   Копия кладётся в public/ каждого сайта: <script src="/sereda-header.js" defer></script>

   Язык хранится в cookie `plang` на домене .sereda.lv → выбор следует за
   пользователем по ВСЕМ поддоменам. Порядок выбора: ?lang= → cookie → localStorage
   → язык браузера → 'en'.

   Перевод контента: сайт задаёт window.I18N = { en:{key:val,...}, lv:{...}, ru:{...} }
   ДО подключения этого файла. Элементы помечаются:
     data-i18n="key"        → textContent
     data-i18n-html="key"   → innerHTML
     data-i18n-attr="attr:key,attr2:key2" → атрибуты
   Хук: window.__onLang(lang) вызывается после каждого применения (для динамики).
   Событие: document 'sereda:lang' (detail = lang).
   API: window.SeredaLang.get() / .set(lang).
   ============================================================ */
(function () {
  var LANGS = ['en', 'lv', 'ru'];

  function readCookie(n) {
    var m = document.cookie.match('(?:^|; )' + n + '=([^;]*)');
    return m ? decodeURIComponent(m[1]) : null;
  }
  function writeCookie(n, v) {
    document.cookie = n + '=' + encodeURIComponent(v) +
      ';path=/;domain=.sereda.lv;max-age=31536000;SameSite=Lax' +
      (location.protocol === 'https:' ? ';Secure' : '');
  }
  function pick() {
    try { var u = new URLSearchParams(location.search).get('lang'); if (LANGS.indexOf(u) >= 0) return u; } catch (e) {}
    var c = readCookie('plang'); if (LANGS.indexOf(c) >= 0) return c;
    try { var s = localStorage.getItem('plang'); if (LANGS.indexOf(s) >= 0) return s; } catch (e) {}
    var d = (navigator.language || 'en').slice(0, 2).toLowerCase();
    return LANGS.indexOf(d) >= 0 ? d : 'en';
  }

  var LANG = pick();

  function apply(l) {
    if (LANGS.indexOf(l) < 0) return;
    LANG = l;
    try { localStorage.setItem('plang', l); } catch (e) {}
    writeCookie('plang', l);
    document.documentElement.lang = l;

    var T = (window.I18N && window.I18N[l]) || null;
    if (T) {
      document.querySelectorAll('[data-i18n]').forEach(function (el) {
        var v = T[el.getAttribute('data-i18n')]; if (v != null) el.textContent = v;
      });
      document.querySelectorAll('[data-i18n-html]').forEach(function (el) {
        var v = T[el.getAttribute('data-i18n-html')]; if (v != null) el.innerHTML = v;
      });
      document.querySelectorAll('[data-i18n-attr]').forEach(function (el) {
        el.getAttribute('data-i18n-attr').split(',').forEach(function (pair) {
          var p = pair.split(':'); var v = T[p[1]]; if (v != null && p[0]) el.setAttribute(p[0], v);
        });
      });
    }

    var box = document.getElementById('sereda-langs');
    if (box) box.querySelectorAll('a').forEach(function (a) {
      a.classList.toggle('on', a.getAttribute('data-lang') === l);
    });

    if (typeof window.__onLang === 'function') { try { window.__onLang(l); } catch (e) {} }
    try { document.dispatchEvent(new CustomEvent('sereda:lang', { detail: l })); } catch (e) {}
  }

  window.SeredaLang = { get: function () { return LANG; }, set: apply };

  function init() {
    var box = document.getElementById('sereda-langs');
    if (box) box.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('[data-lang]') : e.target;
      var l = a && a.getAttribute('data-lang'); if (l) { e.preventDefault(); apply(l); }
    });
    apply(LANG);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
