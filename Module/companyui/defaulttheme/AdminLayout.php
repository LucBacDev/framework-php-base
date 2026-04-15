<?php

namespace CompanyUI\DefaultTheme;

use Company\MVC\Module;
use Company\MVC\Theme;

class AdminLayout extends \Company\MVC\Layout {

    function init() {
        parent::init();

        $css = [
            '/vendor/bootstrap/dist/css/bootstrap.css',
            '/vendor/perfect-scrollbar/css/perfect-scrollbar.min.css',
            '/node_modules/font-awesome/css/font-awesome.min.css',
            '/css/themify-icons.css',
            '/css/materialdesignicons.min.css',
            '/vendor/selectize/dist/css/selectize.default.css',
            '/vendor/summernote/dist/summernote-bs4.css',
            '/vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css',
			'/css/jquery.toast.min.css',
            //'/css/animate.min.css',
            '/css/app.css',
            '/css/custom.css',
            '/css/chosen.min.css'
        ];
        $module = new Module('companyui/defaulttheme');
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
			'/js/jquery.toast.min.js',
            '/vendor/selectize/dist/js/standalone/selectize.min.js',
            '/vendor/summernote/dist/summernote-bs4.min.js',
            '/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.js',
            '/js/app.min.js',
            '/js/chosen.jquery.min.js',
            '/js/jquery.ajax_upload.js',
            '/js/jquery.validate.min.js',
            '/js/babelLoader.js'
        ];
        foreach ($js as $file) {
            $this->addJS($module->getPublicURL() . $file);
        }

        $this->loadUiModule(new \CompanyUI\BaseComponent\UiLoader());
        $babel = ['autoload.json'];
        foreach ($babel as $file) {
            $this->addJS($module->getBabelURL($file));
        }

        $this->loadUiModule(new \CompanyUI\User\UiLoader());
        $this->loadUiModule(new \CompanyUI\Module\UiLoader());
        $this->loadUiModule(new \CompanyUI\Site\UiLoader());
        $this->loadUiModule(new \CompanyUI\License\UiLoader());
        $this->loadUiModule(new \CompanyUI\Setting\UiLoader());
        $this->loadUiModule(new \CompanyUI\Queue\UiLoader());
//        $this->loadUiModule(new \PacsUi\Ae\UiLoader());
//        $this->loadUiModule(new \PacsUi\Zone\UiLoader());
//        $this->loadUiModule(new \PacsUi\Storage\UiLoader());
//        $this->loadUiModule(new \PacsUi\Viewer\UiLoader());
//        $this->loadUiModule(new \PacsUi\Ris\UiLoader());
//        $this->loadUiModule(new \PacsUi\AI\UiLoader());
//        $this->loadUiModule(new \PacsUi\Study\UiLoader());
//        $this->loadUiModule(new \PacsUi\StudyLog\UiLoader());
//        $this->loadUiModule(new \PacsUi\AccessManagement\UiLoader());
//        $this->loadUiModule(new \PacsUi\PublicLink\UiLoader());
//        $this->loadUiModule(new \PacsUi\Mwl\UiLoader());
//        $this->loadUiModule(new \PacsUi\Log\UiLoader());
        $this->loadUiModule(new \CompanyUI\SystemMonitoring\UiLoader());
//        $this->loadUiModule(new \PacsUi\Queue\UiLoader());
//        $this->loadUiModule(new \PacsUi\Report\UiLoader());
//        $this->loadUiModule(new \PacsUi\Setting\UiLoader());
        $this->loadUiModule(new \CompanyUI\Service\UiLoader());
//        $this->loadUiModule(new \PacsUi\DicomTagMorphing\UiLoader());
//        $this->loadUiModule(new \CompanyUI\Dict\UiLoader());
//        $this->loadUiModule(new \PacsUi\Tool\UiLoader());
//        $this->loadUiModule(new \PacsUi\DicomServer\UiLoader());
        $this->loadUiModule(new \CompanyUI\VersionInfo\UiLoader());

//        $this->loadUiModule(new \RisUi\Setting\UiLoader());
//        $this->loadUiModule(new \RisUi\Modality\UiLoader());
//        $this->loadUiModule(new \RisUi\ModalityGroup\UiLoader());
    }

    protected function renderLayout($content) {
        $module = Module::getInstance('companyui/defaulttheme');
        ?>
        <html>
            <head>
                <meta charset="utf-8">
                <?php $this->genStyleTags() ?>
                <link rel="icon" type="image/png" href="<?php echo ($module->getPublicURL().'/images/logo_minerva.ico'); ?>"/>
            </head>
            <body>
                <script>
                    App = window.App || {};
                    App.siteUrl = <?php echo json_encode(url()) ?>;
                    App.user = <?php echo json_encode(\Company\Auth\Auth::getInstance()->getUser()); ?>;
                    App.themeUrl = <?php echo json_encode($module->getPublicURL()); ?>;
                    App.enviroment = <?php echo json_encode(app()->config['enviroment']) ?>;
                    App.production = <?php echo json_encode(app()->config['production']) ?>;
                    App.siteID = <?php echo json_encode($this->siteID) ?>;
                    App.isFullControl =  <?php echo json_encode(\Company\Auth\Auth::getInstance()->isFullControl()); ?>;
                    //App.token =  <?php //\Company\Auth\Auth::generateCrsfToken(); echo json_encode($_SESSION['token']); ?>//;
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
        return 'admin';
    }

}
