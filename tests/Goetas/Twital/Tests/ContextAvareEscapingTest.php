<?php
namespace Goetas\Twital\Tests;

use Goetas\Twital\EventSubscriber\ContextAwareEscapingSubscriber;
use Goetas\Twital\EventSubscriber\FixTwigExpressionSubscriber;
use Goetas\Twital\SourceAdapter\XHTMLAdapter;
use Goetas\Twital\SourceAdapter\XMLAdapter;
use Goetas\Twital\Twital;
use Goetas\Twital\SourceAdapter\HTML5Adapter;
use Goetas\Twital\TwitalLoader;

class ContextAvareEscapingTest extends \PHPUnit_Framework_TestCase
{
    private $twital;

    private $twig;

    private $loader;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->twital = new Twital();
        $this->twital->getEventDispatcher()->addSubscriber(new ContextAwareEscapingSubscriber());

        $this->loader = new TwitalLoader(new \Twig_Loader_String(), $this->twital);
        $this->twig = new \Twig_Environment($this->loader, array('autoescape' => false));
    }

    /**
     * @dataProvider getData
     */
    public function testHTML5SourceAdapter($source, $expected, $renderedExpected = null, $vars = array())
    {
        $this->compareResults(new HTML5Adapter(), $source, $expected, $renderedExpected, $vars);
    }

    /**
     * @dataProvider getData
     */
    public function testXHTMLSourceAdapter($source, $expected = null, $renderedExpected = null, $vars = array())
    {
        $this->compareResults(new XHTMLAdapter(), $source, $expected, $renderedExpected, $vars);
    }

    /**
     * @dataProvider getData
     */
    public function testXMLSourceAdapter($source, $expected, $renderedExpected = null, $vars = array())
    {
        $this->compareResults(new XMLAdapter(), $source, $expected, $renderedExpected, $vars);
    }

    /**
     * @dataProvider getData
     */
    public function testHTML5SourceAdapterTwig($source, $expected, $renderedExpected = null, $vars = array())
    {
        $this->twital->getEventDispatcher()->addSubscriber(new FixTwigExpressionSubscriber());
        $this->compareResults(new HTML5Adapter(), $source, $expected, $renderedExpected, $vars);
    }

    /**
     * @dataProvider getData
     */
    public function testXHTMLSourceAdapterTwig($source, $expected = null, $renderedExpected = null, $vars = array())
    {
        $this->twital->getEventDispatcher()->addSubscriber(new FixTwigExpressionSubscriber());
        $this->compareResults(new XHTMLAdapter(), $source, $expected, $renderedExpected, $vars);
    }

    /**
     * @dataProvider getData
     */
    public function testXMLSourceAdapterTwig($source, $expected, $renderedExpected = null, $vars = array())
    {
        $this->twital->getEventDispatcher()->addSubscriber(new FixTwigExpressionSubscriber());
        $this->compareResults(new XMLAdapter(), $source, $expected, $renderedExpected, $vars);
    }

    private function compareResults($sourceAdapter, $source, $expected, $renderedExpected = null, $vars = array())
    {
        $compiled = $this->twital->compile($sourceAdapter, $source);
        $this->assertEquals($expected, $compiled);

        if ($renderedExpected) {
            $rendered = $this->twig->render($compiled, $vars);
            $this->assertEquals($renderedExpected, $rendered);
        }
    }

    public function getData()
    {
        return array(
            array(
                '<span>{{ foo }}<a href="{{ url|raw }}" title="{{ bar ~ "foo" }}">{{ bar }}</a></span>',
                '{% autoescape \'html\' %}<span>{{ foo }}<a href="{% autoescape \'html_attr\' %}{{ url|raw }}{% endautoescape %}" title="{% autoescape \'html_attr\' %}{{ bar ~ "foo" }}{% endautoescape %}">{{ bar }}</a></span>{% endautoescape %}',
                '<span>x &gt; y &amp; x &lt; y<a href="http://www.example.com" title="f&#x20;&gt;&quot;&#x20;&#x27;&lt;oofoo">f &gt;&quot; &#039;&lt;oo</a></span>',
                array(
                    'foo' =>'x > y & x < y',
                    'url' =>'http://www.example.com',
                    'bar' =>'f >" \'<oo',
                )
            ),
            array(
                '<script type="text/javascript">alert(\'{{ foo }}\')</script>',
                '{% autoescape \'html\' %}<script type="{% autoescape \'html_attr\' %}text/javascript{% endautoescape %}">{% autoescape \'js\' %}alert(\'{{ foo }}\'){% endautoescape %}</script>{% endautoescape %}',
                '<script type="text/javascript">alert(\'fo\x20\x27\x20\x22\x20o\')</script>',
                array(
                    'foo' => 'fo \' " o'
                )
            ),
            array(
                '<script>alert(\'{{ foo }}\')</script>',
                '{% autoescape \'html\' %}<script>{% autoescape \'js\' %}alert(\'{{ foo }}\'){% endautoescape %}</script>{% endautoescape %}',
            ),

            array(
                '<style type="text/css">p { font-family: "{{ foo }}"; background: url({{ foo }}); }</style>',
                '{% autoescape \'html\' %}<style type="{% autoescape \'html_attr\' %}text/css{% endautoescape %}">{% autoescape \'css\' %}p { font-family: "{{ foo }}"; background: url({{ foo }}); }{% endautoescape %}</style>{% endautoescape %}',
                '<style type="text/css">p { font-family: "\3C \2F style\3E \20 \A \20 foo"; background: url(\3C \2F style\3E \20 \A \20 foo); }</style>',
                array(
                    'foo' => "</style> \n foo"
                )
            ),
            array(
                '<style>p { font-family: "{{ foo }}"; }</style>',
                '{% autoescape \'html\' %}<style>{% autoescape \'css\' %}p { font-family: "{{ foo }}"; }{% endautoescape %}</style>{% endautoescape %}',
            ),

            array(
                '<a href="{{ foo }}"><foo src="foo?q={{ bar }}">bar</foo></a>',
                '{% autoescape \'html\' %}<a href="{% autoescape \'html_attr\' %}{{ foo }}{% endautoescape %}"><foo src="{% autoescape \'html_attr\' %}foo?q={{ bar }}{% endautoescape %}">bar</foo></a>{% endautoescape %}',
                '<a href="http&#x3A;&#x2F;&#x2F;www.example.com"><foo src="foo?q=f&#x20;&gt;&lt;oo">bar</foo></a>',
                array(
                    'foo' =>'http://www.example.com',
                    'bar' =>'f ><oo',
                )
            ),
        )
        ;
    }
}
