<?php
namespace Goetas\Twital\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Goetas\Twital\EventDispatcher\SourceEvent;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
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

    public function __construct(array $customNamespaces)
    {
        $this->customNamespaces = $customNamespaces;
    }

    public function addCustomNamespace(SourceEvent $event)
    {
        $source = $event->getTemplate();
        /*$mch = null;
        if (preg_match('~<(([a-z0-9\-_]+):)?([a-z0-9\-_]+)~i', $xml, $mch, PREG_OFFSET_CAPTURE)) {
            $addPos = $mch[0][1] + strlen($mch[0][0]);
            foreach ($this->customNamespaces as $prefix => $ns) {
                if (! preg_match('/\sxmlns:([a-z0-9\-]+)="' . preg_quote($ns, '/') . '"/', $xml) && ! preg_match('/\sxmlns:([a-z0-9\-]+)=".*?"/', $xml)) {
                    $xml = substr_replace($xml, ' xmlns:' . $prefix . '="' . $ns . '"', $addPos, 0);
                }
            }

            $event->setTemplate($xml);
        }*/
        $xml = '<twital';
        foreach ($this->customNamespaces as $prefix => $ns) {
            $xml .= ' xmlns:' . $prefix . ' = "' . $ns . '"';
        }
        $xml .= '>' . $source . '</twital>';
        $event->setTemplate($xml);
    }

    public function removeCustomNamespace(SourceEvent $event)
    {
        $source = $event->getTemplate();

        foreach ($this->customNamespaces as $prefix => $ns) {
            $xml .= ' xmlns:' . $prefix . ' = "' . $ns . '"';
        }
        $source = preg_replace('#<(.*) xmlns:[a-zA-Z0-9]+=("|\')' . Twital::NS . '("|\')(.*)>#m', "<\\1\\4>", $source);

        $event->setTemplate($source);
    }
}
