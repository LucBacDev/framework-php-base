App.toolbarConfig = [
    //tab
    {
        'label': 'Home', 'controls': [
            //col
            [
                {
                    'class': 'Viewer.ToolbarDropdownSeperated', 'label': 'Window',
                    'command': { 'cmd': 'Viewer.setWindow' },
                    'dropdown': [
                        { 'label': 'Lung', 'command': { 'cmd': 'Viewer.setWindow', 'ww': 100, 'wc': 200 } },
                        { 'label': 'Abdomen', 'command': { 'cmd': 'Viewer.setWindow', 'ww': 100, 'wc': 200 } }
                    ]
                },
                {
                    'class': 'Viewer.ToolbarDropdownSeperated', 'label': 'Zoom',
                    'command': { 'cmd': 'Viewer.setZoom' },
                    'dropdown': [
                        { 'label': 'Manual', 'command': { 'cmd': 'Viewer.setZoom', 'mode': 'manual' } },
                        { 'label': 'True size', 'command': { 'cmd': 'Viewer.setZoom', 'mode': 'trueSize' } },
                        { 'label': 'Fit screen', 'command': { 'cmd': 'Viewer.setZoom', 'mode': 'fitScreen' } }
                    ]
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Scroll',
                    'command': { 'Viewer.sendCommand': ['scroll'] }
                }
            ],
            //col
            [
                {
                    'class': 'Viewer.ToolbarDropdownSeperated', 'label': 'Cine',
                    'command': { 'cmd': 'Viewer.cine' },
                    'dropdown': [
                        { 'label': 'Slow speed', 'command': { 'cmd': 'Viewer.cine', 'fps': 'slow' } },
                        { 'label': 'Normal speed', 'command': { 'cmd': 'Viewer.cine', 'fps': 'normal' } },
                        { 'label': 'Fast speed', 'command': { 'cmd': 'Viewer.cine', 'fps': 'fast' } }
                    ]
                },
                {
                    'class': 'Viewer.ToolbarDropdownSeperated', 'label': 'Sync',
                    'command': { 'cmd': 'Viewer.sync' },
                    'dropdown': [
                        { 'label': 'Sync by position', 'command': { 'cmd': 'Viewer.sync', 'type': 'position' } },
                        { 'label': 'Sync by index', 'command': { 'cmd': 'Viewer.sync', 'type': 'index' } },
                        { 'label': 'Sync by position', 'command': { 'cmd': 'Viewer.sync', 'type': 'disable' } }
                    ]
                },
            ]
        ]
    }
]