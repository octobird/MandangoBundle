<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\MandangoBundle\Form;

use Mandango\Mandango;
use Mandango\MandangoBundle\Form\Type\MandangoDocumentType;
use Symfony\Component\Form\AbstractExtension;

/**
 * MandangoExtension.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoExtension extends AbstractExtension
{
    /** @var Mandango */
    private $mandango;

    public function __construct(Mandango $mandango)
    {
        $this->mandango = $mandango;
    }

    protected function loadTypes()
    {
        return array(
            new MandangoDocumentType($this->mandango),
        );
    }

    protected function loadTypeGuesser()
    {
        return new MandangoTypeGuesser($this->mandango->getMetadataFactory());
    }
}
