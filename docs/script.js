window.$docsify = {
    enableSidebarCollapse: true,
    logo: '/_media/icon.svg',
    search: 'auto',
    repo: 'https://github.com/clicalmani/Tonka',
    coverpage: true,
    onlyCover: true,
    name: "Tonka - Documentation", // Must configure when publishing
    hideSidebar: false, // Configurable when publishing
    subMaxLevel: 2, // Configurable when publishing
    basePath: "/", // Configurable when publishing
    homepage: "./README.md", // Configurable when publishing
    alias: {
      '/.*/_sidebar.md': '/_sidebar.md',
      '/.*/README.md': '/README.md',
    },
    loadSidebar: true,
    subMaxLevel: 4,
    sidebarDisplayLevel: 1,
    name: '',
    search: {
      noData: {
        '/': 'No results!'
      },
      paths: 'auto',
      placeholder: {
        '/': 'Search'
      }
    },
    plugins: [
      // DocsifyCarbon.create('CE7I52QU', 'xmakeio'),
      function(hook) {
        var footer = [
          '<footer style="text-align: center;margin-top: 50px;">',
          '<span> TONKA Framework  &copy; 2024 </span>',
          '</footer>'
        ].join('');

        hook.afterEach(function(html) {
          return html + footer;
        });
      }
    ],
    darklightTheme: {
        siteFont : 'PT Sans',
        defaultTheme : 'light',
        codeFontFamily : 'Roboto Mono, Monaco, courier, monospace',
        dark: {
          coverBackground: '#FFF',
          textColor: '#3A3D44',
          accent: '#EA453A',
        },
        light: {
          coverBackground: '#FFF',
          background: '#FFF',
          textColor: '#3A3D44',
          accent: '#EA453A',
          codeBackgroundColor: '#FFF7F6'
        }
    },
    // docsify-copy-code (defaults)
    copyCode: {
      buttonText: 'Copy',
      errorText: 'Error',
      successText: 'Copied',
    },
    routerMode: 'history',
};