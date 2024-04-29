<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection HtmlDeprecatedAttribute */
/** @noinspection HtmlExtraClosingTag */
/** @noinspection HtmlRequiredAltAttribute */
/** @noinspection HtmlUnknownAttribute */
/** @noinspection HtmlUnknownTarget */

/** @noinspection CheckTagEmptyBody */

namespace eftec\tests;

use eftec\bladeone\BladeOne;

use eftec\bladeonehtml\BladeOneHtml;
use eftec\MessageContainer;
use PHPUnit\Framework\TestCase;

class myBlade extends  BladeOne {
    use BladeOneHtml;
}


/**
 * Class clientTest
 * @package eftec\tests
 * @copyright Jorge Castro Castillo
 * 2020-05-02
 */
class clientTest extends TestCase
{
	/**
	 * @var myBlade
	 */
	private $myBlade;

	public function assertEqualsNR($expected, $v): void
    {
	    $expected=str_replace(["\r\n","\n"],["",""],$expected);
        $v=str_replace(["\r\n","\n"],["",""],$v);
        self::assertEquals($expected,$v);
    }


	public function setUp() : void
	{
		$this->myBlade=new myBlade();
		$this->myBlade->setMode(BladeOne::MODE_DEBUG);

	}

	public function testBox(): void
    {
	    $this->myBlade->addJs('<script src="domain.dom/js.js">','alert');
        $this->myBlade->addJs('<script src="domain.dom/js.js">','alert');
        $this->myBlade->addJsCode('alert(2);');
        $this->myBlade->addCss('style123');
        $template='@jsbox
@jscodebox(ready)
@cssbox';

        $html='<script src="domain.dom/js.js">
<script>
alert(2);</script>

<link rel="stylesheet" href="./style123">';
        $this->assertEqualsNR( $html,$this->myBlade->runString($template));
    }


    public function testarguments(): void
    {


        $r = 'a1=1 a2="hello world" a2b="hello=world" a3=\'hello world\' a4=$a5  ';
        self::assertEquals(
            ['a1' => 1, 'a2' => '"hello world"', 'a2b' => '"hello=world"', 'a3' => '\'hello world\'', 'a4' => '$a5']
            , $this->myBlade->getArgsProxy($r));
        $r = '1 "hello world" "hello=world" \'hello world\' $a5';
        self::assertEquals(
            ['1' => null, '"hello world"' => null, '"hello=world"' => null, '\'hello world\'' => null, '$a5' => null]
            , $this->myBlade->getArgsProxy($r));


    }


    public function testBasic1(): void
    {
        self::assertEquals('abc:<input type="text" idname="hi" id="hi" name="hi" />'
            ,$this->myBlade->runString('@input(type="text" idname="hi" pre="abc:")'));
		self::assertEquals('<input type="text" />'
            ,$this->myBlade->runString('@input(type="text")'));
        self::assertEquals('<input type="text" abc="123" cde=\'123\' efg hij="" />'
            ,$this->myBlade->runString('@input(type="text" abc="123" cde=\'123\' efg hij="")'));
    }
    public function testCheckbox(): void
    {
	    $template='@checkbox(id="idsimple" value="1" checked="1" post="it is a selection")';
	    $html='<input type="checkbox"  id="idsimple" value="1" checked ></input>it is a selection';
        self::assertEquals($html,$this->myBlade->runString($template));

        $template='@checkboxes(id="checkbox1" value=$selection alias=$country)
@item(id="aa1" value=\'aaa\' text=\'hello world\' post="<br>")
@item(id="aa2" value=\'aaa\' text=\'hello world2\' post="<br>")
@items(values=$countries value=\'id\' text=\'name\' post="<br>")
@endcheckboxes';
        $html='<div id="checkbox1" value="" >
<input type="checkbox" id="aa1" value=\'aaa\' name="aa1" idname="aa1" >hello world</input><br>
<input type="checkbox" id="aa2" value=\'aaa\' name="aa2" idname="aa2" >hello world2</input><br>

</div>';
        self::assertEquals($html,$this->myBlade->runString($template));
    }
    public function testRadio(): void
    {
        $template='@radio(id="idsimple" value="1" checked="1" post="it is a selection")';
        $html='<input type="radio"  id="idsimple" value="1" checked ></input>it is a selection';
        self::assertEquals($html,$this->myBlade->runString($template));

        $template='@radios(id="radios1" name="aaa" value=$selection  alias=$country)
@item(value=\'aaa\' text=\'hello world\' post="<br>")
@item(value=\'aaa\' text=\'hello world2\' post="<br>")
@items(values=$countries value=\'id\' text=\'name\' post="<br>")
@endradios';
        $html='<div id="radios1" name="aaa" value="" >
<input type="radio" value=\'aaa\' id="radios1" name="radios1" idname="radios1" >hello world</input><br>
<input type="radio" value=\'aaa\' id="radios1" name="radios1" idname="radios1" >hello world2</input><br>

</div>';
        self::assertEquals($html,$this->myBlade->runString($template));
    }
    public function testMisc(): void
    {
        $template='@ul(id="aaa" value=$selection values=$countries alias=$country)
@item(value=\'aaa\' text=\'hello world\')
@items(value=$country->id text=$country->name)
@endul
@image(src="https://via.placeholder.com/350x150")
@label(for="id1" text="hello world:")
@hidden(name="id1" value="hello world$somevar" )
@alert(text="hi there" class="alert-danger" customtag="it is a custom tag")<br>';
        $html='<ul id="aaa" value="" >
<li value=\'aaa\' id="aaa" name="aaa" idname="aaa" >hello world</li>

</ul>
<img  src="https://via.placeholder.com/350x150" ></img>
<label  for="id1" >hello world:</label>
<input name="id1" value="hello world" type=hidden />
<div  class="alert-danger" customtag="it is a custom tag">hi there</div><br>';
        self::assertEquals($html,$this->myBlade->runString($template));
    }
    public function testTextArea(): void
    {
        $template='@textarea(id="aaa" value="3333 3333 aaa3333")';
        $html='<textarea  id="aaa" >3333 3333 aaa3333</textarea>';
        self::assertEquals($html,$this->myBlade->runString($template));
    }
    public function testTable(): void
    {
        $template='@table(class="table" values=$countries alias=$country border="1")
@tablehead  
@cell(text="id")
@cell(text="cod")
@cell(text="name")
@endtablehead
@tablebody(id=\'hello world\'  )
@tablerows(style="background-color:azure")
@cell(text=$country->id style="background-color:orange")
@cell(text=$country->cod )
@cell(text=$country->name)
@endtablerows
@endtablebody
@tablefooter
@cell(text="id" colspan="3")
@endtablefooter
@endtable';
        $html='<table class="table" border="1" >
<thead><tr  >
<th  >id</th>
<th  >cod</th>
<th  >name</th>
</tr></thead>
<tbody  id=\'hello world\' ></tbody>
<tfoot><tr  >
<td  colspan="3" >id</td>
</tr></tfoot>
</table>';
        self::assertEquals($html,$this->myBlade->runString($template));
    }
    public function testButton(): void
    {
        $template='<body>
@form()
@input(type="text" name="myform" value=$myvalue)
@button(type="submit" text="Send")
@link(href="https://www.google.cl" text="context")
@endform()
</body>';
        $html='<body>
<form  >
<input type="text" name="myform" value="" />
<button type="submit" >Send</button>
<a href="https://www.google.cl" >context</a>
</form>
</body>';
        self::assertEquals($html,$this->myBlade->runString($template));
    }

    public function testNewVarSelect(): void
    {
        self::assertEquals('<select id="aaa" value="" >
            <option value=\'aaa\' id="aaa" name="aaa" idname="aaa" >hello world</option>
<option value=\'aaa\' id="aaa" name="aaa" idname="aaa" >hello world</option>
<option value=\'aaa\' id="aaa" name="aaa" idname="aaa" >hello world</option>

</select>
',$this->myBlade->runString('@select(id="aaa" value=$selection values=$countries alias=$country)
            @item(value=\'aaa\' text=\'hello world\')
@item(value=\'aaa\' text=\'hello world\')
@item(value=\'aaa\' text=\'hello world\')
@items( id="chkx" value=$country->id text=$country->name)
@endselect
'));
    }
    public function testNewVarSelect2(): void
    {

        self::assertEquals('<select name="frm_Cham__idSensorxyz" label="Sensorxyz" id="frm_Cham__idSensorxyz" value="" >
            <option value=\'aaa\' id="frm_Cham__idSensorxyz" name="frm_Cham__idSensorxyz" idname="frm_Cham__idSensorxyz" >hello world</option>
<option value=\'aaa\' id="frm_Cham__idSensorxyz" name="frm_Cham__idSensorxyz" idname="frm_Cham__idSensorxyz" >hello world</option>
<option value=\'aaa\' id="frm_Cham__idSensorxyz" name="frm_Cham__idSensorxyz" idname="frm_Cham__idSensorxyz" >hello world</option>

</select>
',$this->myBlade->runString('@select(name="frm_Cham__idSensor$x" label="Sensor$x" id="frm_Cham__idSensor$x"
 value=$selection values=$countries alias=$country)
            @item(value=\'aaa\' text=\'hello world\')
@item(value=\'aaa\' text=\'hello world\')
@item(value=\'aaa\' text=\'hello world\')
@items( id="chkx" value=$country->id text=$country->name)
@endselect
',['x'=>'xyz']));
    }
    public function testNewVar3(): void
    {
        $this->myBlade->useBootstrap3();
        $this->myBlade->useBootstrap3(true);
        self::assertEquals('<input type="text" class="form-control" />'
            ,$this->myBlade->runString('@input(type="text")'));
        self::assertEquals('<input type="text" abc="123" cde=\'123\' efg hij="" class="form-control" />'
            ,$this->myBlade->runString('@input(type="text" abc="123" cde=\'123\' efg hij="")'));
    }

    public function testNewVar4(): void
    {
        $this->myBlade->useBootstrap4();
        $this->myBlade->useBootstrap4(true);
        self::assertEquals('<input type="text" class="form-control" />'
            ,$this->myBlade->runString('@input(type="text")'));
        self::assertEquals('<input type="text" abc="123" cde=\'123\' efg hij="" class="form-control" />'
            ,$this->myBlade->runString('@input(type="text" abc="123" cde=\'123\' efg hij="")'));
    }
    public function testtc(): void
    {
	    $pagarray=array('pagination'=>array(        'first' => 'First',
            'prev' => 'Previous',
            'next' => 'Next',
            'last' => 'Last'));
	    self::assertEquals($pagarray,$this->myBlade->getTranslationControl());
        $this->myBlade->setTranslationControl($pagarray);
        self::assertEquals($pagarray,$this->myBlade->getTranslationControl());


    }
    public function testmsg(): void
    {
	    $mc=new MessageContainer();
	    $mc->addItem('msg1','there is an error');
	    $this->myBlade->message($mc);
	    self::assertEquals("<span default='' >there is an error</span>",$this->myBlade->runString("@message(id='msg1' level='error' default='')"));
    }

}
