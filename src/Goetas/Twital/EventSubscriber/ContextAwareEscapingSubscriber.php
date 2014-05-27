<?php
namespace Goetas\Twital\EventSubscriber;

use Goetas\Twital\EventDispatcher\TemplateEvent;
use Goetas\Twital\Helper\RecursiveDOMIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 *
 */
class ContextAwareEscapingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'compiler.pre_dump' => 'escape',
        );
    }

    public function escape(TemplateEvent $event)
    {
        $dom = $event->getTemplate()->getDocument();
        $iterator = new \RecursiveIteratorIterator(new RecursiveDOMIterator($dom), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $node) {
            if ($node instanceof \DOMElement) {
                foreach ($node->attributes as $attribute) {
                    $attribute->value = '{% autoescape \'html_attr\' %}'.$attribute->value.'{% endautoescape %}';
                }
            } else {
            }
        }
    }
}
