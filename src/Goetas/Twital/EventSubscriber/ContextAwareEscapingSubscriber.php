<?php
namespace Goetas\Twital\EventSubscriber;

use Goetas\Twital\EventDispatcher\SourceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Goetas\Twital\EventDispatcher\TemplateEvent;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 * @author Martin Hasoň <martin.hason@gmail.com>
 *
 */
class ContextAwareEscapingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'compiler.pre_dump' => 'addEscaping',
            'compiler.post_dump' => 'addRootEscaping',
        );
    }

    public function addEscaping(TemplateEvent $event)
    {
        $doc = $event->getTemplate()->getDocument();

        $xp = new \DOMXPath($doc);

        // css escaping
        foreach ($xp->query("//style[not(@type) or @type = 'text/css']/text()") as $node) {
            $node->data = '{% autoescape \'css\' %}'.$node->data.'{% endautoescape %}';
        }

        // js escaping
        foreach ($xp->query("//script[not(@type) or @type = 'text/javascript']/text()") as $node) {
            $node->data = '{% autoescape \'js\' %}'.$node->data.'{% endautoescape %}';
        }

        // html attribute escaping
        foreach ($xp->query("//@*") as $node) {
            $node->value = '{% autoescape \'html_attr\' %}'.$node->value.'{% endautoescape %}';
        }
    }

    public function addRootEscaping(SourceEvent $event)
    {
        $source = $event->getTemplate();

        $event->setTemplate('{% autoescape \'html\' %}'.$source.'{% endautoescape %}');
    }
}
