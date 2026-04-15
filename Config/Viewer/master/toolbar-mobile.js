App.toolbarMobileConfig = [
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
            {
                'class': 'Viewer.ToolbarBtnMobile',
                'label': 'Window',
                'icon': 'i-window',
                'command': { 'cmd': 'wl' },
                'active': 'wl'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile',
                'label': 'Zoom',
                'icon': 'i-zoom',
                'command': { 'cmd': 'zoom' },
                'active': 'zoom'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile',
                'label': 'Scroll',
                'icon': 'i-scroll',
                'command': { 'cmd': 'scroll' },
                'active': 'scroll'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Pan',
                'icon': 'i-pan',
                'command': { 'cmd': 'pan' },
                'active': 'pan'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Reset',
                'icon': 'i-reset',
                'command': { 'cmd': 'resetAll' }
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Line',
                'icon': 'i-line',
                'command': { 'cmd': 'line' },
                'active': 'line'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Ellipse',
                'icon': 'i-ellipse',
                'command': { 'cmd': 'ellipse' },
                'active': 'ellipse'
            }
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
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'ORI',
                'command': { 'cmd': 'mprOrientation' },
                'active': 'wl'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile',
                'label': 'Window',
                'icon': 'i-window',
                'command': { 'cmd': 'wl' },
                'active': 'wl'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile',
                'label': 'Zoom',
                'icon': 'i-zoom',
                'command': { 'cmd': 'zoom' },
                'active': 'zoom'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile',
                'label': 'Scroll',
                'icon': 'i-scroll',
                'command': { 'cmd': 'scroll' },
                'active': 'scroll'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Pan',
                'icon': 'i-pan',
                'command': { 'cmd': 'pan' },
                'active': 'pan'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Reset',
                'icon': 'i-reset',
                'command': { 'cmd': 'resetAll' }
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Line',
                'icon': 'i-line',
                'command': { 'cmd': 'line' },
                'active': 'line'
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Ellipse',
                'icon': 'i-ellipse',
                'command': { 'cmd': 'ellipse' },
                'active': 'ellipse'
            }
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
                    'class': 'Viewer.ToolbarBtnMobile', 'label': 'Rotate',
                    'icon': 'i-rotate-right',
                    'command': { 'cmd': 'rotate3d' },
                    'active': 'rotate'
                },
                {
                    'class': 'Viewer.ToolbarBtnMobile', 'label': 'Window',
                    'icon': 'i-window',
                    'command': { 'cmd': 'wl' },
                    'active': 'wl',
                    'dropdown': []
                },
                {
                    'class': 'Viewer.ToolbarBtnMobileOption',
                    'label': '3D Preset',
                    'icon': 'i-preset',
                    'dropdown': []
                },
                {
                    'class': 'Viewer.ToolbarBtnMobile', 'label': 'Zoom',
                    'icon': 'i-zoom',
                    'command': { 'cmd': 'zoom' },
                    'active': 'zoom'
                },
                {
                    'class': 'Viewer.ToolbarBtnMobile', 'label': 'Pan',
                    'icon': 'i-pan',
                    'command': { 'cmd': 'pan' },
                    'active': 'pan'
                },
                {
                    'class': 'Viewer.ToolbarBtnMobile', 'label': '3D Cut',
                    'icon': 'i-cut',
                    'command': { 'cmd': 'cut' },
                    'active': 'cut'
                },
                {
                    'class': 'Viewer.ToolbarBtnMobile', 'label': 'Cut Reset',
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
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Forward',
                'icon': 'fa fa-forward',
                'command': { 'cmd': 'flyThrough', 'step': 1 }
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Backward',
                'icon': 'fa fa-backward',
                'command': { 'cmd': 'flyThrough', 'step': -1 }
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Revert',
                'icon': 'i-reset',
                'command': { 'cmd': 'flyThroughRevert' }
            },
            {
                'class': 'Viewer.ToolbarBtnMobileOption', 'label': 'VE Preset',
                'icon': 'i-preset',
                'dropdown': [
                    { 'label': 'Colon', 'command': { 'cmd': 'presetEndo', 'preset': 'VE_Colon1' } },
                    { 'label': 'Bone', 'command': { 'cmd': 'presetEndo', 'preset': 'Bone' } },
                ]
            }
        ]
    },
    {
        'id': 'image', 'label': 'Image',
        'visible': function () {
            var mode = App.Component.getEventState('viewer.mode') || { 'mode': '2d' }
            return mode.mode == '2d'
        },
        'controls': [
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Rotate left',
                'icon': 'i-rotate-left',
                'command': { 'cmd': 'rotate', 'angle': -90 }
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Rotate right',
                'icon': 'i-rotate-right',
                'command': { 'cmd': 'rotate', 'angle': 90 }
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Invert',
                'icon': 'i-invert',
                'command': { 'cmd': 'invert' }
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Flip horizontal',
                'icon': 'i-flip-h',
                'command': { 'cmd': 'flipH' }
            },
            {
                'class': 'Viewer.ToolbarBtnMobile', 'label': 'Flip vertical',
                'icon': 'i-flip-v',
                'command': { 'cmd': 'flipV' }
            }
        ]
    }
];