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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * MergeGroupListener.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MergeGroupListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(FormEvents::SUBMIT => 'onSubmit');
    }

    public function onSubmit(FormEvent $event)
    {
        /** @var ReferenceGroup $group */
        $data = $event->getForm()->getData();
        $group = $event->getData();

        $group->replace($data);

        $event->setData($group);
    }
}
