<!--to support dark mode meta tags-->
<style type="text/css">
  :root {
    color-scheme: light dark;
    supported-color-schemes: light dark;
  }
</style>

<style>
  body {
    margin: 0;
    padding: 0;
    }
  table {
    border-collapse:collapse;
    mso-table-lspace:0;
    mso-table-rspace:0; 
    }
  h1 {
    margin:0.67em 0;
    font-size:2em;
    }
  h2 {
    margin:0.83em 0;
    font-size:1.5em;
    }
  html[dir] h3, h3 {
    margin:1em 0;
    font-size:1.17em;
    }
  
  span.MsoHyperlinkFollowed {
    color: inherit !important;
    mso-style-priority: 99 !important;
        }
      #root [x-apple-data-detectors=true],
      a[x-apple-data-detectors=true]{
        color: inherit !important;
        text-decoration: inherit !important;
      }
  [x-apple-data-detectors-type="calendar-event"] {
        color: inherit !important;
        -webkit-text-decoration-color: inherit !important;
  }
  u + .body a {
    color: inherit;
    text-decoration: none;
    font-size: inherit;
    font-weight: inherit;
    line-height: inherit;
    }
  .body {
    word-wrap: normal;
    word-spacing:normal;
    }
  div[style*="margin: 16px 0"] {
    margin: 0!important;
    }
  #message *{
    all:revert
  }
</style>


<style>
  body {
    height: 100% !important;
    width: 100% !important;
    -webkit-text-size-adjust: 100%;
    -ms-text-size-adjust: 100%;
  }
  
  table,
  td {
    -webkit-text-size-adjust: 100%;
    -ms-text-size-adjust: 100%;
  }
  
  img {
    border: 0;
    line-height: 100%;
    outline: none;
    text-decoration: none;
    display: block;
  }
  
  p, h1, h2, h3, h4 { padding: 0; margin: 0;}
  p, h1, h2, h3, img, ul li span { color:#0a080b; }
  p, h1, h2, h3, a, ul, img, ul li span{ font-family: 'Trebuchet MS', Arial, sans-serif; }
      
      ul li { font-size: 28px !important; line-height: 28px !important; }
      ol li { font-size: 18px !important; }
      
      h1 { font-size:36px; line-height:46px; }
      h1.jumbo { font-size:60px; line-height:70px; }
      h2 { font-size:30px; line-height:40px; }
      h3 { font-size:24px; line-height:34px; }
      h4 { font-size: 18px; line-height: 24px; }
      p, img { font-size:18px; line-height:28px; }
      p.subhead { font-size:30px; line-height:40px; }
      p.sm_subhead { font-size:24px; line-height:34px; }
      ul li span { font-size:18px; line-height:28px; }
  
  h1 a, h2 a, h3 a, h4 a { color: #0a080b !important; text-decoration: none !important; } 
  
  a, .link { color: <?php echo $view['translator']->trans('nyrocms.email.highlightColor'); ?> !important; text-decoration: underline !important; }
  
  .dark a { color: #ffffff !important; }
  
  a:hover, .link:hover {
    text-decoration: none !important;
  }
  
  .fadeimg {
    transition: 0.3s !important;
    opacity: 1 !important;
  }
  
  .fadeimg:hover {
    transition: 0.3s !important;
    opacity: 0.5 !important;
  }
  
  /* start CTA HOVER EFFECTS */
  .cta { transition: 0.3s !important; }
  .cta span { transition: 0.3s !important; color: #ffffff; }
  .cta:hover {
        transition: 0.5s !important;
        background-color: #004265 !important;
        transform: scale(1.05);
  }
  .cta:hover span { transition: 0.3s !important; }
  .cta-border:hover { border-bottom: 3px solid transparent !important; }
  /* end CTA HOVER EFFECTS */
  
  .footer p { font-size: 14px; line-height: 24px; color:#4B525D; }
  .footer a { color: #4B525D !important; }
  .footer-dark p { font-size: 14px; line-height: 24px; color:#fefefe; } 
  .footer-dark a { color: #fefefe !important; }
  
  .mobile {
    display: none !important;
  }
    .mob-inline {
    display: none !important;
  }
  
  .dark-img { display: none !important; }
  
  .blueLinks a { 
          color:inherit !important; 
          text-decoration: none !important; 
  }    
</style>
<style>
  u + .body .gmail-screen { background:#000; mix-blend-mode:screen; display: block; }
  u + .body .gmail-difference { background:#000; mix-blend-mode:difference; display: block; }
  u + .body .cta:hover .gmail-screen { background: transparent; mix-blend-mode:normal; }
  u + .body .cta:hover .gmail-difference { background: transparent; mix-blend-mode:normal; }
</style>
<!--mobile styles-->
<style>
  @media screen and (max-width:600px) {
      .wMobile { width: 95% !important; }
      .wInner {  width: 85% !important; }
      .wFull { width: 100% !important; }
      
      .desktop { width: 0 !important; display: none !important; }
      .mobile { display: block !important; }
      .mob-inline { display: inline-block !important; }
    
    h1 { font-size: 36px !important; line-height: 46px !important; }
    .subhead { font-size: 24px !important; line-height: 34px !important; }
  }
</style>
<style>
  @media (prefers-color-scheme: dark) {
  
    .dark-img {
      display: block !important;
    }
  
    .light-img {
      display: none;
      display: none !important;
    }
  
    .darkmode {
      background-color: #262524 !important; background: #262524 !important;
    }
    .darkmode2 {
      background-color: #0e0e0e !important; background: #0e0e0e !important;
    }
  
    h1, h2, h3, p, span, .plainTxt li, h1 a, h2 a, h3 a, .header a , img, strong {
      color: #EDEEEF !important;
    }
      
    a, .link { color: <?php echo $view['translator']->trans('nyrocms.email.highlightColor'); ?> !important; }
    .footer .link, .footer a { color: #fdfdfd !important; }
  }
  
  [data-ogsc] .dark-img {
    display: block !important;
  }
  
  [data-ogsc] .light-img {
    display: none;
    display: none !important;
  }
  
  [data-ogsb] .darkmode {
    background-color: #272623 !important;
  }
  [data-ogsb] .darkmode2, [data-ogsb] .callout {
    background-color: #0e0e0e !important;
  }
  
  [data-ogsc] h1, [data-ogsc] h2, [data-ogsc] h3, [data-ogsc] p, [data-ogsc] span, [data-ogsc] .plainTxt li,  [data-ogsc] h1 a, [data-ogsc] h2 a, [data-ogsc] h3 a, [data-ogsc] .header a, [data-ogsc] .footer a, [data-ogsc] img, [data-ogsc] strong  {
    color: #EDEEEF !important;
  }      
    
  [data-ogsc] .link, [data-ogsc] p a { color: <?php echo $view['translator']->trans('nyrocms.email.highlightColor'); ?> !important; } 
  [data-ogsc] .footer a { color: #fdfdfd !important; }
</style>

<!--fix for Outlook bullet points-->
<style>
  u + .body .glist { margin-left: 15px !important; }
  
  @media only screen and (max-width: 640px) {
    u + .body .glist { margin-left: 25px !important; }
  }
</style>

<!--[if (gte mso 9)|(IE)]>
<style>
li { 
margin-left:27px !important;
mso-special-format: bullet;
}
.forDumbOutlooks {
margin-left: -25px !important;
}
</style>
<![endif]-->