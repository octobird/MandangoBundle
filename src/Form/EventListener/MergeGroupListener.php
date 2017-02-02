<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\MandangoBundle\Form\EventListener;

use Mandango\Group\ReferenceGroup;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\EventListener\MergeCollectionListener;

/**
 * MergeGroupListener.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MergeGroupListener implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface  */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return array(FormEvents::SUBMIT => array('onSubmit', 10));
    }

    public function onSubmit(FormEvent $event)
    {
        /** @var ReferenceGroup $group */
        $group = $event->getForm()->getData();
        $data = $event->getData();
        if ($group instanceof ReferenceGroup) {
            $group->replace($data);
            $event->setData($group);
        }
    }
}
