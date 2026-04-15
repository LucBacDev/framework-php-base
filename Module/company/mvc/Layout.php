<?php

namespace Company\MVC;

abstract class Layout extends View {

    static protected $layouts = [];
    protected $js = [];
    protected $css = [];
    protected $siteID = 0;
            
    function __construct() {
        parent::__construct();
    }

    /**
     *
     * @var array Mảng các babel hoặc JS ES6 
     */
    protected $babel = [];

    static function registerLayout(Layout $layout) {
        static::$layouts[$layout->getName()] = $layout;
    }

    /**
     * 
     * @param type $path
     * @param callable $afterCheck
     * @return $this
     */
    function addJS($path, $afterCheck = null) {
        if (!$afterCheck) {
            $this->js[] = $path;
        } else {
            foreach ($this->js as $i => $file) {
                if ($afterCheck($i, $file) == true) {
                    //nếu hàm check return true thì chèn js vào
                    array_splice($this->js, $i + 1, 0, []);
                    break;
                }
            }
        }
        return $this;
    }

    function getJS() {
        return $this->js;
    }

    /**
     * 
     * @param type $path
     * @param callable $afterCheck
     * @return $this
     */
    function addCSS($path, $afterCheck = null) {
        if (!$afterCheck) {
            $this->css[] = $path;
        } else {
            foreach ($this->css as $i => $file) {
                if ($afterCheck($i, $file) == true) {
                    //nếu hàm check return true thì chèn js vào
                    array_splice($this->css, $i + 1, 0, []);
                    break;
                }
            }
        }
        return $this;
    }

    function getCSS() {
        return $this->css;
    }


    function genStyleTags() {
        echo "\n<!--Auto output style tags-->";
        foreach ($this->css as $css) {
            echo "\n<link rel='stylesheet' type='text/css' href='$css'/>";
        }
        echo "\n";
    }

    function genScriptTags() {
        echo "\n<!--Auto output script tags-->";
        foreach ($this->js as $js) {
            echo "\n<script src='$js'></script>";
        }
        echo '<script type="text/javascript">
            if (console && console.time)
                console.time("performance");
        </script>'; //performance counter
        //  if (app()->config['production']) {
        echo "<!--Using cache JSX in local storage -->";
        foreach ($this->babel as $ts) {
            echo "\n<babel type='text/babel' src='$ts'></babel>";
        }
        //  } 
//        else {
//            echo "<!--Not use cache JSX in local storage -->";
//            foreach ($this->babel as $ts) {
//                echo "\n<script type='text/babel' src='$ts'></script>";
//            }
//        }
    }

    /**
     * 
     * @param type $name
     * @return Layout
     * @throws \Exception
     */
    static function getLayout($name) {
        if (!isset(static::$layouts[$name])) {
            throw new \Exception("Layout not found: $name");
        }

        return static::$layouts[$name];
    }

    /**
     * 
     * @param string $template template của PHP, React có thể chỉ truyền null
     * @param type $data
     */
    function render($template = null, $data = array()) {
        $content = $template ? parent::getOutput($template, $data) : null;
        $this->renderLayout($content);
    }

    /**
     * React phải có PageContent mới chạy
     * @param type $componentName tên component
     */
    function renderReact($componentName, $data = null) {
        $data = json_encode($data);
        $this->renderLayout("<script type=\"text/javascript\">
                var pageData = $data;
                $(document).ready(() => {
                    console.info('begin rendering');
                    setTimeout(function(){
                        ReactDOM.render(React.createElement($componentName), document.getElementById('root'));
                    });
                });
            </script>
            ");
    }
    
    function setSiteID($siteID){
        $this->siteID = $siteID;
        return $this;
    }

    /**
     * Autoload module to layout
     * @param UiLoadable $loader
     */
    function loadUiModule(UiLoadable $loader) {
        $loader->load($this);
        return $this;
    }

    /**
     * @return Theme
     */
    abstract function getTheme();

    abstract function getName();

    abstract protected function renderLayout($content);
}
