<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\MandangoBundle\Form\DataTransformer;

use Mandango\Group\ReferenceGroup;
use Mandango\MandangoBundle\Form\ChoiceList\MandangoDocumentChoiceList;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * MandangoDocumentToArrayTransformer.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoDocumentsToArrayTransformer implements DataTransformerInterface
{
    private $choiceList;

    public function __construct(MandangoDocumentChoiceList $choiceList)
    {
        $this->choiceList = $choiceList;
    }

    public function transform($group)
    {
        if (null === $group) {
            return array();
        }

        if (!is_array($group) && !$group instanceof ReferenceGroup) {
            throw new TransformationFailedException('Expected an array or ReferenceGroup.');
        }

        if ($group instanceof ReferenceGroup) {
            $group = $group->getIterator()->getArrayCopy();
        }

        return $group;
    }

    public function reverseTransform($group)
    {

        if (null === $group) {
            return array();
        }

        if (!is_array($group) && !$group instanceof ReferenceGroup) {
            throw new TransformationFailedException('Expected an array or ReferenceGroup.');
        }

        if ($group instanceof ReferenceGroup) {
            $group = $group->getIterator()->getArrayCopy();
        }

        return $group;
    }
}
