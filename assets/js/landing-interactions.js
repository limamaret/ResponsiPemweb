// Always start the landing page at the top after a reload/navigation restore.
if ('scrollRestoration' in history) {
  history.scrollRestoration = 'manual';
}
window.addEventListener('load', () => {
  window.scrollTo(0, 0);
  // Some browsers apply their saved scroll position just after load.
  requestAnimationFrame(() => window.scrollTo(0, 0));
});

document.addEventListener('DOMContentLoaded', () => {
  // Smooth anchor navigation with an explicit easing animation.
  // This intentionally keeps the requested smooth behavior even if the OS
  // has "reduce motion" enabled, because the landing-page navigation is user-triggered.
  let scrollAnimationFrame = null;
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', event => {
      const href = link.getAttribute('href');
      if (!href || href === '#') return;
      const target = document.getElementById(decodeURIComponent(href.slice(1)));
      if (!target) return;

      event.preventDefault();
      if (scrollAnimationFrame !== null) cancelAnimationFrame(scrollAnimationFrame);

      const header = document.querySelector('.landing-header, .landing-nav, header');
      const headerOffset = header ? Math.min(header.getBoundingClientRect().height, 110) + 14 : 18;
      const startY = window.pageYOffset;
      const targetY = Math.max(0, target.getBoundingClientRect().top + startY - headerOffset);
      const distance = targetY - startY;
      const duration = Math.min(1250, Math.max(650, Math.abs(distance) * 0.65));
      let startTime = null;

      const animateScroll = timestamp => {
        if (startTime === null) startTime = timestamp;
        const progress = Math.min((timestamp - startTime) / duration, 1);
        // Ease-in-out cubic: gradual start, smooth middle, gentle stop.
        const eased = progress < 0.5
          ? 4 * progress * progress * progress
          : 1 - Math.pow(-2 * progress + 2, 3) / 2;
        window.scrollTo(0, startY + distance * eased);

        if (progress < 1) {
          scrollAnimationFrame = requestAnimationFrame(animateScroll);
        } else {
          scrollAnimationFrame = null;
          window.scrollTo(0, targetY);
        }
      };

      scrollAnimationFrame = requestAnimationFrame(animateScroll);
      if (history.replaceState) history.replaceState(null, '', href);
      document.body.classList.remove('menu-open');
      document.querySelector('.mobile-menu')?.setAttribute('aria-expanded', 'false');
    });
  });
  const root=document.documentElement, storageKey='rangkul-accessibility';
  const defaults={theme:'light',fontScale:1,contrast:false,language:'id',font:'jakarta'};
  let prefs={...defaults};
  try{prefs={...defaults,...JSON.parse(localStorage.getItem(storageKey)||'{}')}}catch(e){}
  const translations={
    'Tentang':'About','Cara kerja':'How it works','Fitur':'Features','Aksesibilitas':'Accessibility','Masuk':'Log in',
    'Lewati ke konten utama':'Skip to main content','KARIER YANG LEBIH TERBUKA UNTUK SEMUA':'A MORE OPEN CAREER WORLD FOR EVERYONE',
    'Setiap potensi':'Every potential','berhak mendapat':'deserves the','kesempatan.':'opportunity.',
    'Jelajahi Rangkul':'Explore Rangkul','Lihat cara kerjanya':'See how it works',
    'LANGKAH BERIKUTNYA DIMULAI DARI SINI':'YOUR NEXT STEP STARTS HERE','Siap membuka':'Ready to unlock','kesempatan bersama?':'opportunities together?',
    'Pilih jalur yang sesuai dengan tujuanmu dan mulai gunakan Rangkul.':'Choose the path that fits your goals and start using Rangkul.',
    'Daftar sebagai pencari kerja':'Register as a job seeker','Daftar sebagai perusahaan':'Register as a company',
    'Sudah memiliki akun?':'Already have an account?','Masuk ke Rangkul':'Log in to Rangkul',
    'Atur kenyamananmu':'Make yourself comfortable','PREFERENSI TAMPILAN':'DISPLAY PREFERENCES',
    'Mode tampilan':'Display mode','Pilih tema terang atau gelap':'Choose light or dark theme','Ukuran teks':'Text size',
    'Sesuaikan besar tulisan':'Adjust text size','Kontras tinggi':'High contrast','Pertegas perbedaan warna':'Make colors easier to distinguish',
    'Bahasa halaman':'Page language','Pilih bahasa yang nyaman dibaca':'Choose your preferred language','Jenis huruf':'Font family',
    'Pilih bentuk huruf yang mudah dibaca':'Choose an easy-to-read font','Contoh tampilan':'Display preview',
    'Kembalikan pengaturan awal':'Reset preferences','AKSES YANG BISA DISESUAIKAN':'PERSONALIZED ACCESS',
    'Pengalaman digital yang bisa':'A digital experience that','menyesuaikan kebutuhanmu.':'adapts to your needs.',
    'YANG SERING DITANYAKAN':'FREQUENTLY ASKED QUESTIONS','Masih ingin tahu lebih jauh?':'Want to know more?'
  };
  const originalText=new Map();
  const walker=document.createTreeWalker(document.body,NodeFilter.SHOW_TEXT);
  while(walker.nextNode()){const n=walker.currentNode;if(n.nodeValue.trim())originalText.set(n,n.nodeValue)}
  function setLanguage(lang){prefs.language=lang;document.documentElement.lang=lang;for(const [node,original] of originalText){let value=original;if(lang==='en'){for(const [id,en] of Object.entries(translations)){if(value.includes(id))value=value.replace(id,en)}}node.nodeValue=value;}const sel=document.getElementById('language-select');if(sel)sel.value=lang;}
  function apply(){
    root.classList.toggle('theme-dark',prefs.theme==='dark');root.classList.toggle('high-contrast',!!prefs.contrast);
    root.style.setProperty('--access-font-scale',String(prefs.fontScale));root.style.setProperty('--access-font-family',prefs.font==='atkinson'?'Atkinson Hyperlegible, Arial, sans-serif':prefs.font==='arial'?'Arial, sans-serif':'Plus Jakarta Sans, sans-serif');
    document.getElementById('theme-light')?.setAttribute('aria-pressed',String(prefs.theme==='light'));document.getElementById('theme-dark')?.setAttribute('aria-pressed',String(prefs.theme==='dark'));
    const lang=document.getElementById('language-select');if(lang)lang.value=prefs.language;const font=document.getElementById('font-select');if(font)font.value=prefs.font;
    try{localStorage.setItem(storageKey,JSON.stringify(prefs))}catch(e){}
  }
  document.getElementById('theme-light')?.addEventListener('click',()=>{prefs.theme='light';apply()});document.getElementById('theme-dark')?.addEventListener('click',()=>{prefs.theme='dark';apply()});
  document.getElementById('font-decrease')?.addEventListener('click',()=>{prefs.fontScale=Math.max(.9,+(prefs.fontScale-.05).toFixed(2));apply()});document.getElementById('font-increase')?.addEventListener('click',()=>{prefs.fontScale=Math.min(1.35,+(prefs.fontScale+.05).toFixed(2));apply()});
  document.getElementById('contrast-toggle')?.addEventListener('click',e=>{prefs.contrast=!prefs.contrast;e.currentTarget.setAttribute('aria-pressed',String(prefs.contrast));e.currentTarget.querySelector('em').textContent=prefs.contrast?'Aktif':'Tidak aktif';apply()});
  document.getElementById('language-select')?.addEventListener('change',e=>{setLanguage(e.target.value);apply()});document.getElementById('font-select')?.addEventListener('change',e=>{prefs.font=e.target.value;apply()});
  document.getElementById('reset-access')?.addEventListener('click',()=>{prefs={...defaults};setLanguage('id');apply();const t=document.getElementById('contrast-toggle');if(t){t.setAttribute('aria-pressed','false');t.querySelector('em').textContent='Tidak aktif'}});
  apply();setLanguage(prefs.language);
  document.querySelectorAll('.role-tab').forEach(tab=>tab.addEventListener('click',()=>{document.querySelectorAll('.role-tab').forEach(item=>{const active=item===tab;item.classList.toggle('is-active',active);item.setAttribute('aria-selected',String(active));const panel=document.getElementById(item.getAttribute('aria-controls'));panel.hidden=!active;panel.classList.toggle('is-active',active)})}));
  document.querySelectorAll('.faq-item > h3 > button').forEach(button=>button.addEventListener('click',()=>{
    const expanded=button.getAttribute('aria-expanded')==='true';
    const answer=button.closest('.faq-item').querySelector('.faq-answer');
    button.setAttribute('aria-expanded',String(!expanded));
    button.querySelector('span').textContent=expanded?'＋':'−';
    if(!expanded){answer.hidden=false;answer.style.maxHeight='0px';answer.style.opacity='0';requestAnimationFrame(()=>{answer.style.maxHeight=answer.scrollHeight+'px';answer.style.opacity='1'});}
    else{answer.style.maxHeight=answer.scrollHeight+'px';requestAnimationFrame(()=>{answer.style.maxHeight='0px';answer.style.opacity='0'});answer.addEventListener('transitionend',()=>{if(button.getAttribute('aria-expanded')==='false')answer.hidden=true},{once:true});}
  }));
  // Consistent micro-interactions for every interactive element, including links and controls.
  document.querySelectorAll('a,button,select,[role="tab"]').forEach(el=>{
    el.addEventListener('pointerdown',()=>el.classList.add('is-pressed'));
    ['pointerup','pointercancel','pointerleave','blur'].forEach(evt=>el.addEventListener(evt,()=>el.classList.remove('is-pressed')));
  });
  const menu=document.querySelector('.mobile-menu');menu?.addEventListener('click',()=>{const open=document.body.classList.toggle('menu-open');menu.setAttribute('aria-expanded',String(open))});document.querySelectorAll('.landing-links a').forEach(a=>a.addEventListener('click',()=>{document.body.classList.remove('menu-open');menu?.setAttribute('aria-expanded','false')}));
  // Scroll reveal and reading progress: subtle, keyboard-safe, and disabled by reduced-motion preference.
  const bar=document.createElement('div');bar.className='reading-progress';bar.setAttribute('aria-hidden','true');document.body.appendChild(bar);
  const reveal=document.querySelectorAll('.overview-grid,.overview-stats article,.highlight-card,.workflow-grid article,.role-panel,.inclusive-copy,.access-demo,.faq-item,.closing-cta');
  reveal.forEach(el=>el.classList.add('reveal-on-scroll'));
  if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{entry.target.classList.toggle('is-visible',entry.isIntersecting)}),{threshold:.12,rootMargin:'0px 0px -4% 0px'});reveal.forEach(el=>observer.observe(el))}else reveal.forEach(el=>el.classList.add('is-visible'));
  const updateProgress=()=>{const max=document.documentElement.scrollHeight-innerHeight;bar.style.transform=`scaleX(${max>0?scrollY/max:0})`};window.addEventListener('scroll',updateProgress,{passive:true});updateProgress();
});