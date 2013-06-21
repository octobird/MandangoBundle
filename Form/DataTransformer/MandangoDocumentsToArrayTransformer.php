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
    private $class;

    public function __construct(MandangoDocumentChoiceList $choiceList, $class)
    {
        $this->choiceList = $choiceList;
        $this->class = $class;
    }

    public function transform($group)
    {
        if (null === $group) {
            return array();
        }

//        if (!$group instanceof ReferenceGroup) {
//            throw new UnexpectedTypeException($group, 'Mandango\Group\ReferenceGroup');
//        }

        $array = array();
        foreach ($group as $document) {
            $array[] = (string) $document->getId();
        }

        return $array;
    }

    public function reverseTransform($array)
    {
//        $documents = $this->choiceList->getDocuments();
//
//        $array = array();
//        foreach ($keys as $key) {
//            if (!isset($documents[(string) $key])) {
//                throw new TransformationFailedException('Some Mandango document does not exist.');
//            }
//            $array[] = $documents[(string) $key];
//        }
//
//        return $array;

        if ('' === $array || null === $array) {
            $array = array();
        } elseif($array instanceof ReferenceGroup) {
            $array = $array->getIterator()->getArrayCopy();
        } else {
            $array = (array) $array;
        }

//        $group = new ReferenceGroup($this->class);

        return $array;
    }
}
