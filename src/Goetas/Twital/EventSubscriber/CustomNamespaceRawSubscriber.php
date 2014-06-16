<?php
namespace Goetas\Twital\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Goetas\Twital\EventDispatcher\SourceEvent;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 * @author Martin Hasoň <martin.hason@gmail.com>
 *
 */
class CustomNamespaceRawSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'compiler.pre_load' => 'addCustomNamespace',
            'compiler.post_dump' => 'removeCustomNamespace',
        );
    }

    /**
     *
     * @var array
     */
    protected $customNamespaces = array();

    protected $rootTag;

    public function __construct(array $customNamespaces, $rootTag = 'root')
    {
        $this->customNamespaces = $customNamespaces;
        $this->rootTag = $rootTag;
    }

    public function addCustomNamespace(SourceEvent $event)
    {
        $xml = '<' . $this->rootTag;
        foreach ($this->customNamespaces as $prefix => $ns) {
            $xml .= ' xmlns:' . $prefix . '="' . $ns . '"';
        }
        $xml .= '>' . $event->getTemplate() . '</' . $this->rootTag . '>';

        $event->setTemplate($xml);
    }

    public function removeCustomNamespace(SourceEvent $event)
    {
        $event->setTemplate(preg_replace('#<' . $this->rootTag .'.*?>#m', '', $event->getTemplate()));
        $event->setTemplate(preg_replace('#</' . $this->rootTag .'>#m', '', $event->getTemplate()));
    }
}
