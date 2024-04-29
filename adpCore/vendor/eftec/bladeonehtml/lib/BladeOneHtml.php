<?php /** @noinspection PhpUnused */

/** @noinspection UnknownInspectionInspection
 * @noinspection HtmlRequiredAltAttribute
 * @noinspection HtmlUnknownAttribute
 */
namespace eftec\bladeonehtml;

use eftec\MessageContainer;
use function array_pop;
use function end;
use function substr;
use function trim;

/**
 * trait BladeOneHtml
 * Copyright (c) 2021 Jorge Patricio Castro Castillo MIT License. Don't delete this comment, its part of the license.
 * It adds the next tags
 * <code>
 * @form()
 * @input(type="text" name="myform" value=$myvalue)
 * @button(type="submit" value="Send")
 * @endform()
 *
 * </code>
 *
 * @package  BladeOneHtml
 * @version  2.4
 * @link     https://github.com/EFTEC/BladeOneHtml
 * @author   Jorge Patricio Castro Castillo <jcastro arroba eftec dot cl>
 */
trait BladeOneHtml
{
    /** @var string=['vanilla','bootstrap3','bootstrap4','bootstrap5','material'][$i] It sets the current style  */
    public string $style='vanilla';
    /** @var string[] It stores the list of patterns used by the code */
    public array $pattern
        = [
            'input'           => '{{pre}}<input{{inner}} >{{between}}</input>{{post}}',
            'input_empty'     => '{{pre}}<input{{inner}} />{{post}}',
            'file'            => '{{pre}}<input type="file"{{inner}} >{{between}}</input>{{post}}',
            'select'          => '{{pre}}<select{{inner}} >{{between}}{{post}}',
            'select_item'     => '<option{{inner}} >{{between}}</option>',
            'select_end'      => '</select>',
            'message'           => '{{pre}}<span{{inner}} >{{between}}</span>{{post}}',
            'messages'          => '{{pre}}<ul{{inner}} >{{between}}{{post}}',
            'messages_item'     => "<li{{inner}} >{{between}}</li>\n",
            'messages_end'      => '</ul>',
            'checkbox'        => '{{pre}}<input type="checkbox" {{inner}} >{{between}}</input>{{post}}',
            'radio'           => '{{pre}}<input type="radio" {{inner}} >{{between}}</input>{{post}}',
            'textarea'        => '{{pre}}<textarea {{inner}} >{{between}}</textarea>{{post}}',
            'button'          => '{{pre}}<button{{inner}} >{{between}}</button>{{post}}',
            'link'            => '{{pre}}<a{{inner}} >{{between}}</a>{{post}}',
            'checkboxes'      => '{{pre}}<div{{inner}} >{{between}}{{post}}',
            'checkboxes_item' => '<input type="checkbox"{{inner}} >{{between}}</input>{{post}}',
            'checkboxes_end'  => '</div>',
            'radios'          => '{{pre}}<div{{inner}} >{{between}}{{post}}',
            'radios_item'     => '<input type="radio"{{inner}} >{{between}}</input>{{post}}',
            'radios_end'      => '</div>',
            'ul'              => '{{pre}}<ul{{inner}} >{{between}}{{post}}',
            'ul_item'         => '<li{{inner}} >{{between}}</li>{{post}}',
            'ul_end'          => '</ul>',
            'ol'              => '{{pre}}<ol{{inner}} >{{between}}{{post}}',
            'ol_item'         => '<li{{inner}} >{{between}}</li>{{post}}',
            'ol_end'          => '</ol>',
            'optgroup'        => '{{pre}}<optgroup{{inner}} >{{between}}{{post}}',
            'optgroup_end'    => '</optgroup>',
            'table'           => '{{pre}}<table{{inner}} >{{between}}{{post}}',
            'table_item'      => '<tr {{inner}} >{{between}}</tr>{{post}}',
            'table_end'       => '</table>',
            'tablebody'       => '<tbody {{inner}} >{{between}}{{post}}',
            'tablebody_end'   => '</tbody>',
            'tablehead'       => '<thead><tr {{inner}} >{{between}}{{post}}',
            'tablehead_end'   => '</tr></thead>',
            'tablefooter'     => '<tfoot><tr {{inner}} >{{between}}{{post}}',
            'tablefooter_end' => '</tr></tfoot>',
            'tablerows'       => '<tr {{inner}} >{{between}}{{post}}',
            'tablerows_end'   => '</tr>',
            'form'            => '{{pre}}<form {{inner}} >{{between}}{{post}}',
            'form_end'        => '</form>',
            'cell'            => '<td {{inner}} >{{between}}</td>{{post}}',
            'head'            => '<th {{inner}} >{{between}}</th>{{post}}',
            'label'           => '{{pre}}<label {{inner}} >{{between}}</label>{{post}}',
            'image'           => '{{pre}}<img {{inner}} >{{between}}</img>{{post}}',
            'alert'           => '{{pre}}<div {{inner}}>{{between}}</div>{{post}}',
            'pagination'      => '{{pre}}{{between}}{{post}}',
            'container'      => '{{pre}}<div{{inner}} >{{between}}{{post}}',
            'container_end'  => '</div>',
            'row'      => '{{pre}}<div{{inner}} >{{between}}{{post}}',
            'row_end'  => '</div>',
            'col'      => '{{pre}}<div{{inner}} >{{between}}{{post}}',
            'col_end'  => '</div>',

        ];
    /** @var string[] The class is added to the current element */
    public array $defaultClass = [];
    /** @var array It adds a custom adds that it could be used together with $this->pattern */
    public array $customAttr = [];
    public int $counterId=0;
    protected array $htmlCss = []; // indicates the type of the current tag. such as select/selectgroup/etc.
    protected array $htmlJs = []; //indicates the id of the current tag.
    protected array $htmlJsCode = [];
    protected array $htmlItem = [];
    protected bool $insideForm = false;

    private array $translationControl=['pagination'=>['first'=>'First','prev'=>'Previous','next'=>'Next','last'=>'Last']];

    /** @var MessageContainer|null */
    protected ?MessageContainer $messageContainer;

    /**
     * It is the automatic constructor. It is loaded by BladeOne.
     * @noinspection PhpUnused
     */
    public function BladeOneHtml(): void
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        if (class_exists('eftec\MessageContainer')) {
            // autowire MessageContainer if the method exists.
            $this->messageContainer = MessageContainer::instance();
        }
    }

    //<editor-fold desc="definitions function message">
    /**
     * @param MessageContainer $messageContainer
     * @return MessageContainer
     */
    public function message($messageContainer=null) : ?MessageContainer {
        if($messageContainer!==null) {
            $this->messageContainer=$messageContainer;
            return $messageContainer;
        }
        return $this->messageContainer;
    }

    /**
     * <pre>
     * @ message(id='id' default='default' level='error')
     * </pre>
     *
     * @param $expression
     * @return string|string[]
     */
    protected function compileMessage($expression) {
        $args = $this->getArgs($expression);
        $id=$args['id'];
        $level= $args['level'] ?? null;
        $default= $args['default'] ?? "''";

        $args['between'] = "(\$this->message())->get($id)->first($default,$level)";
        unset($args['value'], $args['id'],$args['level']);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'message', $result);
    }

    /**
     * Only used for testing.
     * @param $expression
     * @return array
     */
    public function getArgsProxy($expression): array
    {
        return $this->getArgs($expression);
    }

    //</editor-fold desc="definitions function message">

    //<editor-fold desc="definitions function">

    public function useBootstrap3($useCDN = false): void
    {
        // Amazing but it still highly used, and it works fine.
        $this->style='bootstrap3';
        $bs3 = [
            'button'   => 'btn',
            'input'    => 'form-control',
            'textarea' => 'form-control',
            'select'   => 'form-control',
            'file'     => 'form-control-file',
            'range'    => 'form-control-range',
            'ul'       => 'list-group',
            'ul_item'  => 'list-group-item',
            'ol'       => 'list-group',
            'ol_item'  => 'list-group-item',
            'table'    => 'table',
            'alert'    => 'alert',
            'container' => 'container-fluid',
            'row' => 'row',
            'col' => 'col',
        ];
        $this->defaultClass = array_merge($this->defaultClass, $bs3);
        if ($useCDN) {
            $this->addCss('<link rel="stylesheet" '
                . 'href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" '
                . 'integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" '
                . 'crossorigin="anonymous">', 'bootstrap');
            $this->addJs('<script src="https://code.jquery.com/jquery-3.5.0.min.js" '
                . 'integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ=" '
                . 'crossorigin="anonymous"></script>', 'jquery');
            $this->addJs('<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" '
                . 'integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" '
                . 'crossorigin="anonymous"></script>', 'bootstrap');
        }
    }

    public function useBootstrap4($useCDN = false): void
    {
        $this->style='bootstrap4';
        $bs4 = [
            'button'        => 'btn',
            'input'         => 'form-control',
            'textarea'      => 'form-control',
            'checkbox_item' => 'form-check-input',
            'select'        => 'form-control',
            'file'          => 'form-control-file',
            'range'         => 'form-control-range',
            'radio'         => 'form-check-input',
            'radio_item'    => 'form-check-input',
            'ul'            => 'list-group',
            'ul_item'       => 'list-group-item',
            'ol'            => 'list-group',
            'ol_item'       => 'list-group-item',
            'table'         => 'table',
            'alert'         => 'alert',
            'container' => 'container-fluid',
            'row' => 'row',
            'col' => 'col',
        ];
        $this->defaultClass = array_merge($this->defaultClass, $bs4);
        $this->pattern['checkbox'] = '<!--suppress XmlInvalidId -->
<div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" {{inner}}>
            <label class="custom-control-label" for={{id}} >{{between}}</label>
            </div>{{post}}';
        $this->pattern['radio'] = '<!--suppress XmlInvalidId -->
<div class="custom-control custom-radio">
            <input type="radio" class="custom-control-input" {{inner}}>
            <label class="custom-control-label" for={{id}} >{{between}}</label>
            </div>{{post}}';

        $this->pattern['checkboxes_item'] = $this->pattern['checkbox'];
        $this->pattern['radios_item'] = $this->pattern['radio'];

        if ($useCDN) {
            $this->addCss('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" 
                integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" 
                crossorigin="anonymous">', 'bootstrap');
            $this->addJs('<script
                  src="https://code.jquery.com/jquery-3.5.1.min.js"
                  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
                  crossorigin="anonymous"></script>', 'jquery');
            $this->addJs('<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" 
                integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" 
                crossorigin="anonymous"></script>
', 'bootstrap');
        }
    }


    public function useBootstrap5($useCDN = false): void
    {
        $this->style='bootstrap5';
        $bs4 = [
            'button'        => 'btn',
            'input'         => 'form-control',
            'textarea'      => 'form-control',
            'checkbox_item' => 'form-check-input',
            'select'        => 'form-control',
            'file'          => 'form-control',
            'range'         => 'form-range',
            'radio'         => 'form-check-input',
            'radio_item'    => 'form-check-input',
            'ul'            => 'list-group',
            'ul_item'       => 'list-group-item',
            'ol'            => 'list-group',
            'ol_item'       => 'list-group-item',
            'table'         => 'table',
            'alert'         => 'alert',
            'container' => 'container-fluid',
            'row' => 'row',
            'col' => 'col',
        ];
        $this->defaultClass = array_merge($this->defaultClass, $bs4);
        $this->pattern['checkbox'] = '<!--suppress XmlInvalidId -->
<div class="form-check">
            <input type="checkbox" class="form-check-input" {{inner}}>
            <label class="form-check-label" for={{id}} >{{between}}</label>
            </div>{{post}}';
        $this->pattern['radio'] = '<!--suppress XmlInvalidId -->
<div class="form-check">
            <input type="radio" class="form-check-input" {{inner}}>
            <label class="form-check-label" for={{id}} >{{between}}</label>
            </div>{{post}}';

        $this->pattern['checkboxes_item'] = $this->pattern['checkbox'];
        $this->pattern['radios_item'] = $this->pattern['radio'];

        if ($useCDN) {
            $this->addCss('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" 
                        rel="stylesheet" 
                        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" 
                        crossorigin="anonymous">', 'bootstrap');
            $this->addJs('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
                    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
                    crossorigin="anonymous"></script>', 'bootstrap');

        }
    }

    //</editor-fold>

    /**
     * Used for pagination
     * @param $newArg
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function addArgUrl($newArg): string
    {
        $get=array_merge($_GET,$newArg);
        return $this->getCurrentUrl(true).'?'.http_build_query($get);
    }



    /**
     * @return array
     */
    public function getTranslationControl(): array
    {
        return $this->translationControl;
    }

    /**
     * It merges a new translation<br>
     * Currently, only the tag <b>@ pagination</b> supports translation.<br>
     * <b>Example:</b><br>
     * <pre>
     * $this->setTranslation(['pagination'=>['first'=>'First','prev'=>'Previous','next'=>'Next','last'=>'Last']]);
     * </pre>
     *
     * @param array $translationControl
     *
     * @return $this
     */
    public function setTranslationControl($translationControl): self
    {
        foreach($translationControl as $k=>$v) {
            $this->translationControl[$k]=$v; // add or replace
        }
        return $this;
    }


    /**
     * It adds a css to the css box. It could be added the link tag, the full url or the relative url.<br>
     * The name is used to avoid to repeat the same style. If a style exists, then it is not added<br>
     * <b>example:</b><br>
     * <pre>
     * $this->addCss('<link href='...'>','bootstrap'); // <link href='...'>
     * $this->addCss('<link href='...'>','bootstrap'); // it is not added (bootstrap already exists)
     * $this->addCss('https://domain.dom/css.css'); // <link href='https://domain.dom/css.css'>
     * $this->addCss('css/css.css'); // <link href='https://domain.dom/css/css.css'> (it uses $baseurl)
     * </pre>
     *
     * @param string $css  It could be an url or a link tag.
     * @param string $name if name is empty then it is added automatically. The name is used to avoid adding duplicates
     */
    public function addCss($css, $name = ''): void
    {
        if (strpos($css, '<link') === false) {
            if(strpos($css,'//')===false) {
                $css=$this->phpTag.' echo $this->baseUrl.\'/'.$css.'\'; ?>';
            }
            $css = '<link rel="stylesheet" href="' . $css . '">';
        }
        if ($name && !isset($this->htmlCss[$name])) {
            $this->htmlCss[$name] = $css;
        } else {
            $this->htmlCss[] = $css;
        }
    }

    /**
     * It adds a js to the js box. It could be added the script tag, the full url or the relative url.<br>
     * The name is used to avoid to repeat the same script. If a script exists, then it is not added<br>
     * <b>example:</b><br>
     * <pre>
     * $this->addJs('<script src='... ','jquery'); // <script src='...'>
     * $this->addJs('<script src='... ','jquery'); // it is not added (jquery already exists)
     * $this->addJs('https://domain.dom/js.js'); // <script src='https://domain.dom/js.js'>
     * $this->addJs('js/js.js'); // <script src='https://domain.dom/js/js.js'> (it uses $baseurl)
     * </pre>
     *
     * @param string $js   It must be a link to a javscript
     * @param string $name if name is empty then it is added. The name avoid adding duplicates
     */
    public function addJs($js, $name = ''): void
    {
        if (strpos($js, '<script') === false) {
            if(strpos($js,'//')===false) {
                $js='<?php echo $this->baseUrl.\'/'.$js.'\'; ?>';
            }
            $js = '<script type="application/javascript" src="' . $js . '"></script>';
        }

        if ($name) {
            if (!isset($this->htmlJs[$name])) {
                $this->htmlJs[$name] = $js;
            }
        } else {
            $this->htmlJs[] = $js;
        }
    }


    /**
     * It adds a js to js script box.
     *
     * @param string $js   It must be a script (without the tag < script >)
     * @param string $name if name is empty then it is added. The name avoid adding duplicates
     */
    public function addJsCode($js, $name = ''): void
    {
        if ($name && !isset($this->htmlJsCode[$name])) {
            $this->htmlJsCode[$name] = $js;
        } else {
            $this->htmlJsCode[] = $js;
        }
    }

    /**
     * Its used internally to render an object.
     *
     * @param array  $args          An associative array with the arguments of the function.
     * @param string $pattern       The name of the pattern. This must be already defined in $this->pattern<br>
     *                        It could also be used to define the default class ($this->defaultClass)
     * @param array  $wrapper       =['inner','between','pre','post','text']
     * @param bool   $escapeBetween if true (default), 'between' is escape. If false, then 'between' is not escaped
     *
     * @return string|string[]
     */
    protected function render($args, $pattern, $wrapper, $escapeBetween = true)
    {
        if (isset($this->defaultClass[$pattern])) {
            $args['class'] = '"' . trim($this->stripQuotes(@$args['class']) . ' ' . $this->defaultClass[$pattern]) . '"';
        }
        $customArgs = [];
        foreach ($this->customAttr as $key => $attr) {
            if (isset($args[$key])) {
                $customArgs[$key] = $this->wrapPHP($this->stripQuotes($args[$key]), '') . '!';
                unset($args[$key]);
            }
        }
        $this->processArgs($args, $wrapper, $escapeBetween);
        $isPatternEmpty = isset($this->pattern[$pattern . '_empty']);
        $txt = ($wrapper[1] === '' && $isPatternEmpty) ? $this->pattern[$pattern . '_empty'] : $this->pattern[$pattern];
        $wrapper[4] = $this->wrapPHP(@$args['id']);
        $wrapper[5] = $this->wrapPHP(@$args['name']);
        $end = str_replace(['{{inner}}', '{{between}}', '{{pre}}', '{{post}}', '{{id}}', '{{name}}'], $wrapper, $txt);

        foreach ($this->customAttr as $key => $attr) {
            $end = str_replace('{{' . $key . '}}', $customArgs[$key] ?? $attr, $end);
        }
        return $end;
    }

    /**
     * It processes the arguments ($args) by converting (into PHP code) and returns an array with 5 values ($result).
     *
     * @param array $args          An associative array with the arguments not converted into PHP code.
     * @param array $result        =['inner','between','pre','post','text'] (it is the result array).
     * @param bool  $escapeBetween (default is true), if true, then 'between' is escaped (_e(...)), otherwise it's not.
     */
    protected function processArgs($args, &$result, $escapeBetween = true): void
    {
        if (isset($args['idname'])) {
            $args['id'] = $args['idname'];
            $args['name'] = $args['idname'];
        }
        if (array_key_exists('between',$args)) {
            $result[1] .= $this->wrapPHP($this->stripQuotes($args['between']), '', $escapeBetween);
            unset($args['between']);
        }
        if (array_key_exists('pre',$args)) {
            $result[2] .= $this->wrapPHP($this->stripQuotes($args['pre']), '', false);
            unset($args['pre']);
        }
        if (array_key_exists('post',$args)) {
            $result[3] .= $this->wrapPHP($this->stripQuotes($args['post']), '', false);
            unset($args['post']);
        }
        if (array_key_exists('text',$args)) {
            $result[1] .= $this->wrapPHP($this->stripQuotes($args['text']), '');
            unset($args['text']);
        }
        foreach ($args as $key => $arg) {
            if ($arg !== null) {
                if ($key === 'selected') {
                    $result[0] .=  $this->wrapPHP($arg, '');
                }elseif( $key === 'checked') {
                    $result[0] .=  $arg;
                    //$result[0] .= ' ' . $key . '=' . $arg;
                }  else {
                    $result[0] .= ' ' . $key . '=' . $this->wrapPHP($arg);
                }
            } else {
                $result[0] .= ' ' . $key;
            }
        }
    }


    //<editor-fold desc="compile function">

    /**
     * This controls only works for type=bootstrap3 and 4<br>
     * <b>Example:</b><br>
     * <pre>
     * @pagination(numpages=999 current=50  pagesize=5 urlparam='_page')
     * </pre>
     * @param $expression
     *
     * @return string|string[]
     * @noinspection PhpUnused
     */
    protected function compilePagination($expression) {
        if($this->style!=='bootstrap4' && $this->style!=='bootstrap3' && $this->style!=='bootstrap5' ) {
            $this->showError('@pagination', '@pagination: it only works with bootstrap3,4 or 5 ('.$this->style.'). You must 
            use useBootstrap3(), useBootstrap4() or useBootstrap5()', true);
            return '';
        }
        $args = $this->getArgs($expression);
        if(!isset($args['numpages'], $args['current'])) {
            $this->showError('@pagination', '@pagination: Missing numpages or current arguments', true);
            return '';
        }
        $_urlparam = $args['urlparam'] ?? "'_page'"; // if not urlparam then we use _page as default
        //unset($args['urlparam'])
        $_numpages = $args['numpages'];
        unset($args['numpages']);
        $_current=$args['current'];
        unset($args['current']);
        $_pagesize =$args['pagesize'];
        $_pagesize= $_pagesize ?? 5;
        unset($args['pagesize']);


        $r=$this->phpTag.' // pagination starts ici *********************************************
        $_half=floor(('.$_pagesize.'-1)/2); $_p0='.$_current.'-$_half; $_p1='.$_current.'+$_half;
        if($_p0<1) { $_p1 +=1-$_p0; $_p0=1; }
        if($_p1>'.$_numpages.') { $_p1='.$_numpages.'; }
        echo \'<ul class="pagination">\';
        $_url=$this->addArgUrl(['.$_urlparam.'=>1]);
        echo \'<li class="page-item"><a class="page-link" href="\'.$_url.\'" tabindex="-1">'
            .$this->translationControl['pagination']['first'].'</a></li>\';
        if('.$_current.' >1) {
            $_url=$this->addArgUrl(['.$_urlparam.'=>'.$_current.'-1]);
            echo \'<li class="page-item"><a class="page-link" href="\'.$_url.\'" tabindex="-1">'
            .$this->translationControl['pagination']['prev'].'</a></li>\';
        } else {
            echo \'<li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">'
            .$this->translationControl['pagination']['prev'].'</a></li>\';
        }
        for($_pag=$_p0;$_pag<=$_p1;$_pag++) {
            $_url=$this->addArgUrl(['.$_urlparam.'=>$_pag]);
            if($_pag == '.$_current.') {
                echo \'<li class="page-item active"><span class="page-link">\'.$_pag.\'</span></li>\';
            } else {
                echo \'<li class="page-item"><a class="page-link" href="\'.$_url.\'">\'.$_pag.\'</a></li>\';    
            }                
        } 
        if('.$_current.' <'.$_numpages.') {
            $_url=$this->addArgUrl(['.$_urlparam.'=>'.$_current.'+1]);
            echo \'<li class="page-item"><a class="page-link" href="\'.$_url.\'">'
            .$this->translationControl['pagination']['next'].'</a></li>\';
        } else {
            echo \'<li class="page-item disabled"><a class="page-link" href="#">'
            .$this->translationControl['pagination']['next'].'</a></li>\';
        }
        $_url=$this->addArgUrl(['.$_urlparam.'=>'.$_numpages.']);
        echo \'<li class="page-item"><a class="page-link" href="\'.$_url.\'" tabindex="-1">'
            .$this->translationControl['pagination']['last'].'</a></li>\';
        echo \'</ul>\';
        // pagination ends *********************************************
        ?>';

        $result = ['', $r, '', '']; // inner, between, pre, post
        return $this->render($args, 'pagination', $result);
    }

    /** @noinspection PhpUnused */
    protected function compileCssBox(): string
    {
        return implode("\n", $this->htmlCss);
    }

    /** @noinspection PhpUnused */
    protected function compileJsBox(): string
    {
        return implode("\n", $this->htmlJs);
    }

    /** @noinspection PhpUnused */
    protected function compilejsCodeBox($expression): string
    {
        $args = $this->getArgs($expression);

        $js = "<script>\n";
        if (isset($args['ready'])) {
            $js .= "document.addEventListener(\"DOMContentLoaded\", function(event) { \n";
        }
        $js .= implode("\n", $this->htmlJsCode);
        if (isset($args['ready'])) {
            $js .= "\n}) // function()\n";
        }
        $js .= "</script>\n";
        return $js;
    }

    /** @noinspection PhpUnused */
    protected function compileInput($expression)
    {
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'input', $result);
    }


    protected function compileSelect($expression)
    {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = [
            'type'   => 'select',
            'value'  => @$args['value'],
            'values' => @$args['values'],
            'alias'  => @$args['alias'],
            'id'     => @$args['id'],
            'name'   => null,
            'idname' => null
        ];

        $result = ['', '', '', '']; // inner, between, pre, post
        unset($args['values'], $args['alias']);

        return $this->render($args, 'select', $result);
    }

    /** @noinspection PhpUnused */
    protected function compileEndSelect()
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endselect', 'Missing @select or so many @endselect', true);
        }
        return $this->pattern[$parent['type'] . '_end'];
    }

    /** @noinspection PhpUnused */
    protected function compileItem($expression)
    {
        // we add a new attribute with the type of the current open tag
        $parent = end($this->htmlItem);
        $args = $this->getArgs($expression);
        if (!isset($args['id'])) {
            $args['id'] = $parent['id'];
        }
        if (!isset($args['name'])) {
            $args['name'] = $parent['name'];
        }
        if (!isset($args['value'])) {
            $args['value'] = 'null';
        }
        if (!isset($args['idname'])) {
            $this->counterId++;
            if (isset($args['id'])) {
                $args['idname'] =$args['id'];
            } else {
                $args['idname'] = $parent['idname'] ?? null; //'_idname'.$this->counterId;
            }

        }
        $checkedname = ($parent['type'] === 'select') ? 'selected' : 'checked';

        $args['checked'] = '{{checked}}'; //<?php if(1==1)?"checked":""; >';

        $result = ['', '', '', '']; // inner, between, pre, post
        $htmlItem= $this->render($args, $parent['type'] . '_item', $result);
        return str_replace('{{checked}}',
            $this->phpTag.' echo (' . $args['value'] . "=={$parent['value']})?'$checkedname':''; ?>", $htmlItem);
    }

    /** @noinspection PhpUnused */
    protected function compileItems($expression): string
    {
        // we add a new attribute with the type of the current open tag
        $parent = end($this->htmlItem);

        $args = $this->getArgs($expression);
        if (!isset($args['id']) && isset($parent['id'])) {
            $args['id'] = $parent['id'];
        }
        if (!isset($args['name']) && isset($parent['name'])) {
            $args['name'] = $parent['name'];
        }
        if (!isset($args['idname']) && isset($parent['idname'])) {
            $args['idname'] = $parent['idname'];
        }

        if($parent['type']==='messages') {
            $args['values']='$this->message()';
            $args['alias'] = '$_msg';

            $args['between']='$_msg';
        } else {
            if (!isset($args['alias'])) {
                $args['alias'] = @$parent['alias'];
            }
            if (!isset($args['values'])) {
                $args['values'] = @$parent['values'];
            }
            if ($args['value'] === null) {
                $this->showError('@items with missing tag value', '@items' . $expression, true);
            }
        }
        if ($args['values'] === null) {
            $this->showError('@items with missing tag values', '@items' . $expression, true);
        }
        if ($args['alias'] === null) {
            if ($this->isVariablePHP($args['values'])) {
                $args['alias'] = $args['values'] . 'Row';
            } else {
                $this->showError('@items with missing tag alias', '@items' . $expression, true);
            }
        }
        $result = ['', '', '', '']; // inner, between, pre, post


        if($parent['type']==='messages') {
            $level = $args['level'] ?? $parent['level'] ?? '';
            $id= $args['id'] ?? 'false';
            $nameOG="\$_msgs=@$id? \$this->message()->get($id)->all($level) 
            : \$this->message()->allArray($level) ;\n \$_tmp";
            $name='$_msgs';
        } else {
            $nameOG = $args['alias'] . 'Optgroup';
            $name = $args['values'];
        }
        $nameKey='$_msgk';


        $html
            = $this->phpTag.' ' . $nameOG . '=\'\';  foreach(' . $name . ' as ' . $nameKey . '=>' . $args['alias'] . ') {'
            . "\n";
        if (isset($args['optgroup'])) {
            $html .= "if({$args['optgroup']}!=" . $nameOG . ") {
                echo \"<optgroup label='\".{$args['optgroup']}.\"'>\";
                $nameOG={$args['optgroup']};
                }";
        }
        $html .= "?>\n";
        unset($args['values'], $args['alias'],$args['level'],$args['idname']);

        if ($parent['type'] === 'select') {
            $checkedname = 'selected';
        } else if ($parent['type'] === 'messages') {
            $checkedname = 'x';
        } else {
            $checkedname = 'checked';
        }

        if($parent['type']!=='messages') {
            // not checked for messages
            $args['checked'] = '{{checked}}'; //<?php if(1==1)?"checked":""; >';
        }
        $args['id'] =isset($args['id']) ? $args['id'].".'_'.". $nameKey : $nameKey;
        $htmlItem = $this->render($args, $parent['type'] . '_item', $result);
        $htmlItem = str_replace('{{checked}}',
            $this->phpTag.' echo (' . @$args['value'] . "=={$parent['value']})?'$checkedname':''; ?>", $htmlItem);
        $html .= $htmlItem;
        $html .= $this->phpTag." } // foreach  ?>\n";
        return $html;
    }


    /** @noinspection PhpUnused */
    protected function compileTextArea($expression)
    {
        $args = $this->getArgs($expression);

        $args['between'] = $this->stripQuotes(@$args['value']);
        unset($args['value']);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'textarea', $result);
    }

    /**
     * @param string $expression
     * @param string $type=['checkbox','radio'][$i]
     * @return string|string[]
     */
    protected function renderCheckBoxRadio($expression,$type) {

        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        if(isset($args['checked'])) {
            if(!$this->isVariablePHP($args['checked'])) {
                // constant or some fixed value
                $args['checked'] = $this->stripQuotes($args['checked']) ? ' checked' : '';
            } else {
                // variable
                $args['checked']=$this->wrapPHP($args['checked']."?' checked':''",'',false);
            }
        }
        return $this->render($args, $type, $result);
    }

    /**
     * @param $expression
     * @param $nameTag
     * @return string|string[]
     */
    protected function utilGenContainer($expression, $nameTag) {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = $this->constructorItem($nameTag,$args);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, $nameTag, $result);
    }
    protected function utilGenEndContainer($nameTag) {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@'.$nameTag, "Missing @$nameTag or so many @$nameTag", true);
        }
        return $this->pattern[$parent['type'] . '_end'];
    }
    /** @noinspection PhpUnused */
    protected function compileContainer($expression)
    {
        return $this->utilGenContainer($expression,'container');
    }
    /** @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function compileEndContainer($expression)
    {
        return $this->utilGenEndContainer('container');
    }
    /** @noinspection PhpUnused */
    protected function compileRow($expression)
    {
        return $this->utilGenContainer($expression,'row');
    }
    /** @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function compileEndRow($expression)
    {
        return $this->utilGenEndContainer('row');
    }
    /** @noinspection PhpUnused */
    protected function compileCol($expression)
    {
        return $this->utilGenContainer($expression,'col');
    }
    /** @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function compileEndCol($expression)
    {
        return $this->utilGenEndContainer('col');
    }

    /** @noinspection PhpUnused */
    protected function compileCheckbox($expression)
    {
        return  $this->renderCheckBoxRadio($expression,'checkbox');
    }
    /** @noinspection PhpUnused */
    protected function compileRadio($expression)
    {
        return $this->renderCheckBoxRadio($expression,'radio');
    }
    /** @noinspection PhpUnused */
    protected function compileButton($expression)
    {
        $args = $this->getArgs($expression);

        $args['between'] = $this->stripQuotes(@$args['text']);
        unset($args['text']);

        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'button', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileLink($expression)
    {
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'link', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileCheckboxes($expression)
    {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = $this->constructorItem('checkboxes',$args);
        unset($args['values'], $args['alias']);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'checkboxes', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndCheckboxes()
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endcheckboxes', 'Missing @checkboxes or so many @checkboxes', true);
        }
        return $this->pattern[$parent['type'] . '_end'];
    }
    /** @noinspection PhpUnused */
    protected function compileRadios($expression)
    {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = $this->constructorItem('radios',$args);
        unset($args['values'], $args['alias']);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'radios', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndRadios()
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endradios', 'Missing @radios or so many @radios', true);
        }
        return $this->pattern[$parent['type'] . '_end'];
    }
    /** @noinspection PhpUnused */
    protected function compileUl($expression)
    {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = $this->constructorItem('ul',$args);
        unset($args['values'], $args['alias']);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'ul', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndUl()
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endul', 'Missing @ul or so many @endul', true);
        }
        return $this->pattern[$parent['type'] . '_end'];
    }
    protected function compileMessages($expression)
    {
        $args = $this->getArgs($expression);
        $newItem= $this->constructorItem('messages',$args);
        $newItem['level']= $args['level'] ?? null;
        $this->htmlItem[] =$newItem;

        unset($args['values'], $args['alias'], $args['level']);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'messages', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndMessages()
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endmessages', 'Missing @messages or so many @endmessages', true);
        }
        return $this->pattern[$parent['type'] . '_end'];
    }
    /** @noinspection PhpUnused */
    protected function compileOl($expression)
    {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = $this->constructorItem('ol',$args);
        unset($args['values'], $args['alias']);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'ol', $result);
    }

    /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
    protected function constructorItem($type, &$args): array
    {
        return [
            'type'   => $type,
            'value'  => $args['value'] ?? null,
            'values' => $args['values'] ?? null,
            'alias'  => $args['alias'] ?? null,
            'id'     => $args['id'] ?? null,
            'name'   => $args['name'] ?? null,
            'idname' => $args['idname'] ?? null,
        ];
    }
    /** @noinspection PhpUnused */
    protected function compileEndOl()
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endol', 'Missing @ol or so many @endol', true);
        }
        return $this->pattern[$parent['type'] . '_end'];
    }
    /** @noinspection PhpUnused */
    protected function compileForm($expression)
    {
        $this->insideForm = true;
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'form', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndForm()
    {
        if (!$this->insideForm) {
            $this->showError('@endform', 'Missing @form or so many @endform', true);
        }
        return $this->pattern['form_end'];
    }
    /** @noinspection PhpUnused */
    protected function compileOptGroup($expression)
    {
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'optgroup', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndOptGroup()
    {
        return $this->pattern['optgroup_end'];
    }
    /** @noinspection PhpUnused */
    protected function compileFile($expression): string
    {
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        $post = @$args['post'];
        unset($args['post']);
        $html = $this->render($args, 'file', $result);
        $args['type'] = '"hidden"';
        if (isset($args['name'])) {
            //
            //$args['name'] = $this->addInsideQuote($args['name'], '_file');
            $args['name'] .= ".'_file'.";
        }
        $args['post'] = $post;
        unset($args['pre'], $args['between']);
        $html .= $this->render($args, 'input', $result);
        return $html;
    }

    protected function compileTable($expression)
    {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = [
            'type'   => 'table',
            'value'  => @$args['values'],
            'id'     => null,
            'name'   => null,
            'idname' => null,
            'alias'  => @$args['alias']
        ];
        unset($args['values'], $args['alias']);

        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'table', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndTable($expression)
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endselect', 'Missing @select or so many @endselect', true);
        }
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'table_end', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileTableBody($expression): string
    {
        $parent = end($this->htmlItem);
        $args = $this->getArgs($expression);
        $this->htmlItem[] = ['type' => 'tablebody'];
        $result = ['', '', '', '']; // inner, between, pre, post

        $html = $this->render($args, 'tablebody', $result);
        $html .= $this->phpTag.' foreach(' . $parent['value'] . ' as ' . $parent['alias'] . ') { ?>';
        return $html;
    }
    /** @noinspection PhpUnused */
    protected function compileEndTableBody($expression): string
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endtablebody', 'Missing @tablebody or so many @endtablebody', true);
        }
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return ' '.$this->phpTag.' } ?>' . $this->render($args, 'tablebody_end', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileTableHead($expression)
    {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = ['type' => 'tablehead'];
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'tablehead', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndTableHead($expression)
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endtablehead', 'Missing @tablehead or so many @endtablehead', true);
        }
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'tablehead_end', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileTableFooter($expression)
    {
        $args = $this->getArgs($expression);
        $this->htmlItem[] = ['type' => 'tablefooter'];
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'tablefooter', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndTableFooter($expression)
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endtablehead', 'Missing @tablehead or so many @endtablehead', true);
        }
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'tablefooter_end', $result);
    }

    protected function compileTableRows($expression)
    {
        end($this->htmlItem);
        $args = $this->getArgs($expression);
        $this->htmlItem[] = ['type' => 'tablerows'];
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'tablerows', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileEndTableRows($expression)
    {
        $parent = @array_pop($this->htmlItem);
        if ($parent === null) {
            $this->showError('@endtablerows', 'Missing @tablerows or so many @endtablerows', true);
        }
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'tablerows_end', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileCell($expression)
    {
        $parent = end($this->htmlItem);
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post

        if ($parent['type'] === 'tablehead') {
            return $this->render($args, 'head', $result);
        }

        return $this->render($args, 'cell', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileLabel($expression)
    {
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'label', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileImage($expression)
    {
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'image', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileHidden($expression)
    {
        $args = $this->getArgs($expression);
        $args['type'] = 'hidden';
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'input', $result);
    }
    /** @noinspection PhpUnused */
    protected function compileAlert($expression)
    {
        $args = $this->getArgs($expression);
        $result = ['', '', '', '']; // inner, between, pre, post
        return $this->render($args, 'alert', $result);
    }

    protected function compileTrio($expression): string
    {
        // we add a new attribute with the type of the current open tag
        $parent = end($this->htmlItem);
        $x = trim($expression);
        $x = "('$parent'," . substr($x, 1);
        return $this->phpTag . "echo \$this->trio$x; ?>";
    }
    /** @noinspection PhpUnused */
    protected function compileTrios($expression): string
    {
        // we add a new attribute with the type of the current open tag
        $parent = end($this->htmlItem);
        $x = trim($expression);
        $x = "('$parent'," . substr($x, 1);
        return $this->phpTag . "echo \$this->trios$x; ?>";
    }


    //</editor-fold>
}
