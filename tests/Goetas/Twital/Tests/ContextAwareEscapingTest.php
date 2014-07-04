<?php
namespace Goetas\Twital\Tests;

use Goetas\Twital\EventSubscriber\ContextAwareEscapingSubscriber;
use Goetas\Twital\SourceAdapter\XHTMLAdapter;
use Goetas\Twital\SourceAdapter\XMLAdapter;
use Goetas\Twital\Twital;
use Goetas\Twital\SourceAdapter\HTML5Adapter;


class ContextAwareEscapingTest extends \PHPUnit_Framework_TestCase
{
    private $twital;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->twital = new Twital();
        $this->twital->getEventDispatcher()->addSubscriber(new ContextAwareEscapingSubscriber());

    }

    /**
     * @dataProvider getData
     */
    public function testHTML5SourceAdapter($source, $expected)
    {
        $sourceAdapter = new HTML5Adapter();

        $compiled = $this->twital->compile($sourceAdapter, $source);
        $this->assertEquals($expected, $compiled);
    }

    /**
     * @dataProvider getData
     */
    public function testXHTMLSourceAdapter($source, $expected)
    {
        $sourceAdapter = new XHTMLAdapter();

        $compiled = $this->twital->compile($sourceAdapter, $source);
        $this->assertEquals($expected, $compiled);
    }

    /**
     * @dataProvider getData
     */
    public function testXMLSourceAdapter($source, $expected)
    {
        $sourceAdapter = new XMLAdapter();

        $compiled = $this->twital->compile($sourceAdapter, $source);
        $this->assertEquals($expected, $compiled);
    }

    public function getData()
    {
        return array(
            array(
                '<div class="bar"><span name="{{ foo }}" attr="{{ bar }}">foo</span>foo</div>',
                '<div class="{% autoescape \'html_attr\' %}bar{% endautoescape %}"><span name="{% autoescape \'html_attr\' %}{{ foo }}{% endautoescape %}" attr="{% autoescape \'html_attr\' %}{{ bar }}{% endautoescape %}">foo</span>foo</div>',
            ),
        );
    }
}
