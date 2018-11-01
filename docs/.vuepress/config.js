module.exports = {
    title: 'Patron',
    description: 'An OAuth2 Client manager for Craft CMS',
    base: '/',
    themeConfig: {
        docsRepo: 'flipboxfactory/patron',
        docsDir: 'docs',
        docsBranch: 'master',
        editLinks: true,
        search: true,
        searchMaxSuggestions: 10,
        codeLanguages: {
            twig: 'Twig',
            php: 'PHP',
            json: 'JSON',
            // any other languages you want to include in code toggles...
        },
        nav: [
            {text: 'Details', link: 'https://flipboxdigital.com/software/patron'},
            {text: 'Documentation', link: 'https://patron.flipboxfactory.com'},
            {text: 'Changelog', link: 'https://github.com/flipboxfactory/patron/blob/master/CHANGELOG.md'},
            {text: 'Repo', link: 'https://github.com/flipboxfactory/patron'}
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
                },
                {
                    title: 'Configure',
                    collapsable: false,
                    children: [
                        ['/configure/', 'Settings'],
                        ['/configure/provider/', 'Providers'],
                        ['/configure/provider/instance', 'Provider Instances'],
                        ['/configure/provider/token', 'Provider Tokens']
                    ]
                },
                {
                    title: 'Services',
                    collapsable: false,
                    children: [
                        ['/services/manage-providers', 'Manage Providers'],
                        ['/services/manage-tokens', 'Manage Tokens'],
                        ['/services/providers', 'Providers'],
                        ['/services/tokens', 'Tokens']
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