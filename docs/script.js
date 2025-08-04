window.$docsify = {
    enableSidebarCollapse: true,
    logo: '/logo-dark.png',
    search: 'auto',
    repo: 'https://github.com/clicalmani/tonka',
    coverpage: true,
    onlyCover: true,
    name: "Tonka Framework - Documentation", // Must configure when publishing
    hideSidebar: false, // Configurable when publishing
    subMaxLevel: 2, // Configurable when publishing
    basePath: "/tonka/", // Configurable when publishing
    homepage: "README.md", // Configurable when publishing
    alias: {
      '/.*/_sidebar.md': '/tonka/_sidebar.md',
      '/.*/README.md': '/tonka/README.md',
    },
    loadSidebar: true,
    // loadNavbar: true,
    // autoHeader: true,
    mergeNavbar: true,
    mergeSidebar: true,
    subMaxLevel: 4,
    sidebarDisplayLevel: 0,
    search: {
      noData: {
        '/': 'No results!'
      },
      paths: 'auto',
      placeholder: {
        '/': 'Search'
      }
    },
    progress: {
        position: "top",
        color: "var(--theme-color,#EA453A)",
        height: "3px",
    },
    // topBanner: {
    //   position: 'fixed',
    //   content: "",
    //   backgroundColor: "var(--bannerBackgroundColor)",
    //   defaultTag: 'div',
    //   zIndex: 1000
    // },
    ads: [
      {
        img: './_assets/_img/brown-aesthetic-hijab-giveaway-feed-ads.png',
        href: 'https://boolan-erp.com'
      },
      {
        img: './_assets/_img/brown-aesthetic-hijab-giveaway-feed-ads.png',
        href: 'https://boolan-erp.com'
      },
      {
        img: './_assets/_img/blue-professional-modern-gradient-internet-security-ads-instagram-post.png',
        href: 'https://boolan-erp.com'
      },
      {
        img: './_assets/_img/blue-professional-modern-gradient-internet-security-ads-instagram-post.png',
        href: 'https://boolan-erp.com'
      },
      {
        img: 'https://dn-lego-static.qbox.me/cps/1638268217-480x300.jpg',
        href: 'https://boolan-erp.com'
      },
      {
        img: 'https://dn-lego-static.qbox.me/cps/1638267917-480x300.jpg',
        href: 'https://boolan-erp.com'
      },
    ],
    markdown: {        
        renderer: {
          code: function (code, lang) {
            let cc = document.createElement('code');
            cc.textContent = code;
            cc.setAttribute('class', 'language-' + lang);
            return '<pre data-lang="' + lang + '" class="line-numbers">' + cc.outerHTML + '</pre>';
          },
          heading(text, depth) {
            const escapedText = text.toLowerCase().replace(/[^\w]+/g, '-');
            return `
                    <h${depth} class="heading">
                      <a id="${escapedText}" class="anchor" href="${location.hash}?id=${escapedText}">
                        <span class="header-link">#</span>
                      </a>
                      ${text}
                    </h${depth}>`;
          }
        }
    },
    plugins: [
      function (hook, vm) {
          hook.doneEach(function (html) {              
            Prism.highlightAll();
          })
      },
      function(hook) {
        var footer = [
          '<footer style="text-align: center;margin-top: 50px;">',
          '<span> TONKA Framework  &copy; 2025 </span>',
          '</footer>'
        ].join('');

        hook.afterEach(function(html) {
          return html + footer;
        });
      },
    ],
    darklightTheme: {
        siteFont : 'PT Sans',
        defaultTheme : 'dark',
        codeFontFamily : 'Roboto Mono, Monaco, courier, monospace',
        themeColor: '#EA453A',
        bannerBackgroundColor: '#6ea8fe',
        dark: {
          sidebarSublink: '#7f8185ff',
          coverBackground: '#091a28',
          textColor: '#a8abb4ff',
          accent: '#EA453A',
          codeBackgroundColor: '#031420ff',
          highlightColor: '#6b8faaff',
          sidebarInputBackgroundColor: '#081c2c',
          sidebarInputBorderColor: '#173044'
        },
        light: {
          coverBackground: '#FFF',
          background: '#FFF',
          textColor: '#3A3D44',
          accent: '#EA453A',
          codeBackgroundColor: '#FFF7F6',
          sidebarInputBackgroundColor: '#FFFFFF',
          sidebarInputBorderColor: '#e7edf3ff'
        }
    },
    // docsify-copy-code (defaults)
    copyCode: {
      buttonText: 'Copy',
      errorText: 'Error',
      successText: 'Copied',
    },
    routerMode: 'hash',
};