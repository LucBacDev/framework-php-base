App.toolbarConfig = [
    //tab
    {
        'id': 'home',
        'label': 'Home',
        //when this tab is visible
        'visible': function () {
            var mode = App.Component.getEventState('viewer.mode') || { mode: '2d' }
            return mode.mode == '2d'
        },
        'controls': [
            //col
            [
                {
                    'class': 'Viewer.ToolbarDropdownSeperated',
                    'label': 'Window',
                    'icon': 'i-window',
                    'command': { 'cmd': 'wl' },
                    'active': 'wl',
                    'dropdown': []
                },
                {
                    'class': 'Viewer.ToolbarDropdownSeperated',
                    'label': 'Zoom',
                    'icon': 'i-zoom',
                    'command': { 'cmd': 'zoom' },
                    'active': 'zoom',
                    'dropdown': [
                        { 'label': 'Manual', 'command': { 'cmd': 'zoom', 'mode': 'manual' } },
                        { 'label': 'True size', 'command': { 'cmd': 'zoom', 'mode': 'trueSize' } },
                        { 'label': 'Fit screen', 'command': { 'cmd': 'zoom', 'mode': 'fitScreen' } }
                    ]
                },
                {
                    'class': 'Viewer.ToolbarBtn',
                    'label': 'Scroll',
                    'icon': 'i-scroll',
                    'command': { 'cmd': 'scroll' },
                    'active': 'scroll'
                }
            ],
            //col
            [
                {
                    'class': 'Viewer.ToolbarDropdownSeperated',
                    'label': 'Cine',
                    'icon': 'i-cine',
                    'command': { 'cmd': 'cine', 'fps': 10, 'step': 1 },
                    'dropdown': [
                        { 'label': 'Slow speed', 'command': { 'cmd': 'cine', 'fps': 5, 'step': 1 } },
                        { 'label': 'Normal speed', 'command': { 'cmd': 'cine', 'fps': 10, 'step': 1 } },
                        { 'label': 'Fast speed', 'command': { 'cmd': 'cine', 'fps': 20, 'step': 1 } }
                    ]
                },
                {
                    'class': 'Viewer.ToolbarDropdownSeperated',
                    'label': 'Sync',
                    'icon': 'i-sync',
                    'command': { 'cmd': 'sync' },
                    'dropdown': [
                        { 'label': 'Sync by position', 'command': { 'cmd': 'Viewer.sync', 'type': 'position' } },
                        { 'label': 'Sync by index', 'command': { 'cmd': 'Viewer.sync', 'type': 'index' } }
                    ]
                }
            ],
            //empty col = seperator
            [],
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Pan',
                    'icon': 'i-pan',
                    'command': { 'cmd': 'pan' },
                    'active': 'pan'
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Reset',
                    'icon': 'i-reset',
                    'command': { 'cmd': 'reset' }
                }

            ],
            [], //seperator
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Line',
                    'icon': 'i-line',
                    'command': { 'cmd': 'line' },
                    'active': 'line'
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Ellipse',
                    'icon': 'i-ellipse',
                    'command': { 'cmd': 'ellipse' },
                    'active': 'ellipse'
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Delete all',
                    'icon': 'i-delete-all',
                    'command': { 'cmd': 'deleteAnno' }
                },
            ],
            [], //seperator
            [
                {
                    'class': 'Viewer.ToolbarLayoutSelect', 'label': 'Se layout',
                    'icon': 'i-layout',
                    'command': { 'cmd': 'seriesLayout' }
                },
                {
                    'class': 'Viewer.ToolbarLayoutSelect', 'label': 'Im layout',
                    'icon': 'i-layout',
                    'command': { 'cmd': 'imageLayout' }
                }
            ],
            [],
            [
                {
                    'class': 'Viewer.ToolbarLayoutSelect', 'label': 'Se layout',
                    'icon': 'i-layout',
                    'command': { 'cmd': 'seriesLayout' }
                },
                {
                    'class': 'Viewer.ToolbarLayoutSelect', 'label': 'Im layout',
                    'icon': 'i-layout',
                    'command': { 'cmd': 'imageLayout' }
                }
            ],
            [
                {
                    'class': 'Viewer.ToolbarLayoutSelect', 'label': 'Se layout',
                    'icon': 'i-layout',
                    'command': { 'cmd': 'seriesLayout' }
                },
                {
                    'class': 'Viewer.ToolbarLayoutSelect', 'label': 'Im layout',
                    'icon': 'i-layout',
                    'command': { 'cmd': 'imageLayout' }
                }
            ]
        ]
    },
    {
        //home on MPR mode
        'id': 'home',
        'label': 'Home',
        //when this tab is visible
        'visible': function () {
            var mode = App.Component.getEventState('viewer.mode') || { mode: '2d' }
            return mode.mode == 'mpr'
        },
        'controls': [
            //col
            [
                {
                    'class': 'Viewer.ToolbarDropdownSeperated', 'label': 'Window',
                    'icon': 'i-window',
                    'command': { 'cmd': 'wl' },
                    'active': 'wl',
                    'dropdown': []
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Zoom',
                    'icon': 'i-zoom',
                    'command': { 'cmd': 'zoom' },
                    'active': 'zoom'
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Scroll',
                    'icon': 'i-scroll',
                    'command': { 'cmd': 'scroll' },
                    'active': 'scroll'
                }
            ],
            [],
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Line',
                    'icon': 'i-line',
                    'command': { 'cmd': 'line' },
                    'active': 'line'
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Ellipse',
                    'icon': 'i-ellipse',
                    'command': { 'cmd': 'ellipse' },
                    'active': 'ellipse'
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Delete all',
                    'icon': 'i-delete-all',
                    'command': { 'cmd': 'deleteAnno' }
                },
            ]
        ]
    },
    {
        //home on 3D/MIP mode
        'id': 'home',
        'label': 'Home',
        //when this tab is visible
        'visible': function () {
            var mode = App.Component.getEventState('viewer.mode') || { mode: '2d' }
            return ((mode.mode == '3d') || (mode.mode == 'mip'))
        },
        'controls': [
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Rotate',
                    'icon': 'i-rotate-right',
                    'command': { 'cmd': 'rotate3d' },
                    'active': 'rotate'
                },
                {
                    'class': 'Viewer.ToolbarDropdownSeperated', 'label': 'Window',
                    'icon': 'i-window',
                    'command': { 'cmd': 'wl' },
                    'active': 'wl',
                    'dropdown': []
                },
                {
                    'class': 'Viewer.ToolbarDropdown', 'label': '3D Preset',
                    'icon': 'i-preset',
                    'dropdown': [
                        { 'label': 'Default', 'command': { 'cmd': 'preset3d', 'preset': 'default' } },
                        { 'label': 'Bone', 'command': { 'cmd': 'preset3d', 'preset': 'bone' } },
                        { 'label': 'Skin', 'command': { 'cmd': 'preset3d', 'preset': 'Skin' } },
                        { 'label': 'Lung Transparent', 'command': { 'cmd': 'preset3d', 'preset': 'Lung_Trans' } }
                    ]
                },
            ],
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Zoom',
                    'icon': 'i-zoom',
                    'command': { 'cmd': 'zoom' },
                    'active': 'zoom'
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Pan',
                    'icon': 'i-pan',
                    'command': { 'cmd': 'pan' },
                    'active': 'pan'
                }
            ],
            [],
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': '3D Cut',
                    'icon': 'i-cut',
                    'command': { 'cmd': 'cut' },
                    'active': 'cut'
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Cut Reset',
                    'icon': 'i-reset',
                    'command': { 'cmd': 'cutReset' }
                }
            ]
        ]
    },
    {
        //home on Endo mode
        'id': 'home',
        'label': 'Home',
        //when this tab is visible
        'visible': function () {
            var mode = App.Component.getEventState('viewer.mode') || { mode: '2d' }
            return mode.mode == 'endo'
        },
        'controls': [
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Forward',
                    'icon': 'fa fa-forward',
                    'command': { 'cmd': 'flyThrough', 'step': 1 }
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Backward',
                    'icon': 'fa fa-backward',
                    'command': { 'cmd': 'flyThrough', 'step': -1 }
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Revert',
                    'icon': 'i-reset',
                    'command': { 'cmd': 'flyThroughRevert' }
                }
            ],
            [],
            [
                {
                    'class': 'Viewer.ToolbarDropdown', 'label': 'VE Preset',
                    'icon': 'i-preset',
                    'dropdown': [
                        { 'label': 'Colon', 'command': { 'cmd': 'presetEndo', 'preset': 'VE_Colon1' } },
                        { 'label': 'Bone', 'command': { 'cmd': 'presetEndo', 'preset': 'Bone' } },
                    ]
                }
            ]
        ]
    },
    {
        'id': 'sync', 'label': 'Sync',
        'visible': function () {
            var mode = App.Component.getEventState('viewer.mode') || { 'mode': '2d' }
            return mode.mode == '2d'
        },
        'controls': [

        ]
    },
    {
        'id': 'image', 'label': 'Image',
        'visible': function () {
            var mode = App.Component.getEventState('viewer.mode') || { 'mode': '2d' }
            return mode.mode == '2d'
        },
        'controls': [
            //col
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Rotate left',
                    'icon': 'i-rotate-left',
                    'command': { 'cmd': 'rotate', 'angle': -90 }
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Rotate right',
                    'icon': 'i-rotate-right',
                    'command': { 'cmd': 'rotate', 'angle': 90 }
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Invert',
                    'icon': 'i-invert',
                    'command': { 'cmd': 'invert' }
                }
            ],
            //col
            [
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Flip horizontal',
                    'icon': 'i-flip-h',
                    'command': { 'cmd': 'flipH' }
                },
                {
                    'class': 'Viewer.ToolbarBtn', 'label': 'Flip vertical',
                    'icon': 'i-flip-v',
                    'command': { 'cmd': 'flipV' }
                }
            ]
        ]
    }
];