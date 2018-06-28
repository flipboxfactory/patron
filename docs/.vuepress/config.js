module.exports = {
    title: 'HubSpot',
    description: 'An OAuth2 Client for Craft CMS',
    base: '/',
    themeConfig: {
        docsRepo: 'flipboxfactory/patron',
        docsDir: 'docs',
        docsBranch: 'master',
        editLinks: true,
        search: true,
        searchMaxSuggestions: 10,
        nav: [
            {text: 'Details', link: 'https://flipboxdigital.com/software/hubspot'},
            {text: 'Changelog', link: 'https://github.com/flipboxfactory/patron/blob/develop/CHANGELOG'},
            {text: 'Documentation', link: '/'}
        ],
        sidebar: {
            '/': [
                {
                    title: 'Getting Started',
                    collapsable: false,
                    children: [
                        ['/', 'Introduction'],
                        ['installation', 'Installation / Upgrading'],
                        'support'
                    ]
                }
            ]
        }
    },
    markdown: {
        anchor: {
            level: [2, 3, 4]
        },
        toc: {
            includeLevel: [3]
        }
    }
}