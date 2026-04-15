<?php

namespace CompanyUI\DefaultTheme;

use Company\MVC\Module;
use Company\MVC\Theme;

class RisLayout extends \Company\MVC\Layout {
 
    function init() {
        parent::init();

        $css = [
            '/vendor/bootstrap/dist/css/bootstrap.css',
//          '/charisma/css/bootstrap-cerulean.min.css',
            //'/vendor/perfect-scrollbar/css/perfect-scrollbar.min.css',
            '/css/font-awesome.min.css',
            //'/css/themify-icons.css',
            //'/css/materialdesignicons.min.css',
            //'/css/animate.min.css',
            '/charisma/css/style.css',
            '/charisma/css/style-dark-theme.css'
//            '/charisma/css/charisma-app.css',
//            '/charisma/css/custom.css'
        ];
        $module = Module::getInstance('companyui/defaulttheme');
        foreach ($css as $file) {
            $this->addCSS($module->getPublicURL() . $file);
        }

        $js = [
            //nếu chế độ opimize thì dùng production build
            app()->config['production'] ? '/js/react.production.min.js' : '/js/react.development.js',
            app()->config['production'] ? '/js/react-dom.production.min.js' : '/js/react-dom.development.js',
            // '/js/browser.min.js',
            '/js/vendor.js',
            //    '/js/jquery-2.2.4.min.js',
            //  '/js/bootstrap.min.js',
            '/vendor/moment/min/moment.min.js',
            '/vendor/selectize/dist/js/standalone/selectize.min.js',
            '/vendor/summernote/dist/summernote-bs4.min.js',
            '/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
            '/js/app.min.js',
            '/js/chosen.jquery.min.js',
            '/js/jquery.ajax_upload.js',
            '/js/jquery.validate.min.js'
        ];
        foreach ($js as $file) {
            $this->addJS($module->getPublicURL() . $file);
        }

        $this->loadUiModule(new \CompanyUI\BaseComponent\UiLoader());
        $babel = ['autoload.json'];
        foreach ($babel as $file) {
            $this->addJS($file);
        }
        
    }

    protected function renderLayout($content) {
        ?>
        <html>
            <head>
                <meta charset="utf-8">
                <?php $this->genStyleTags() ?>
            </head>
            <body class="theme-dark">
                <script>
                    App = window.App || {};
                    App.siteUrl = <?php echo json_encode(url()) ?>;
                    App.user = <?php echo json_encode(\Company\Auth\Auth::getInstance()->getUser()); ?>;
                    App.themeUrl = <?php echo json_encode($this->getTheme()->url()); ?>;
                    App.enviroment = <?php echo json_encode(app()->config['enviroment']) ?>;
                    App.production = <?php echo json_encode(app()->config['production']) ?>;
                    App.siteID = <?php echo json_encode($this->siteID) ?>
                </script>
                <?php $this->genScriptTags() ?>
                <div id="root">
                    <h1 style="text-align: center; padding-top: 20px;">Đang tải...</h1>
                </div>
                <?php echo $content ?>

            </body>
        </html>
        <?php
    }

    public function getTheme() {
        return Theme::getTheme('default');
    }

    public function getName() {
        return 'ris';
    }

}
