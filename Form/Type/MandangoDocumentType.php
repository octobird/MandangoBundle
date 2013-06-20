<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\MandangoBundle\Form\Type;

use Mandango\MandangoBundle\Form\ChoiceList\MandangoDocumentChoiceList;
use Mandango\MandangoBundle\Form\DataTransformer\MandangoDocumentToIdTransformer;
use Mandango\MandangoBundle\Form\DataTransformer\MandangoDocumentsToArrayTransformer;
use Mandango\MandangoBundle\Form\EventListener\MergeGroupListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Mandango\Mandango;

/**
 * MandangoDocumentType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoDocumentType extends AbstractType
{
    private $mandango;

    /**
     * Constructor.
     *
     * @param Mandango $mandango The mandango.
     */
    public function __construct(Mandango $mandango)
    {
        $this->mandango = $mandango;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple']) {
            $builder
                ->addEventSubscriber(new MergeGroupListener())
                ->addViewTransformer(new MandangoDocumentsToArrayTransformer($options['choice_list']), true);
        } else {
            $builder->addViewTransformer(new MandangoDocumentToIdTransformer($options['choice_list']), true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'template'          => 'choice',
            'property'          => null,
            'multiple'          => false,
            'expanded'          => false,
            'mandango'          => $this->mandango,
            'class'             => null,
            'field'             => null,
            'query'             => null,
            'choices'           => array(),
            'preferred_choices' => array(),
            'group_by'          => null,
        );

        $options = array_replace($defaultOptions, $options);

        if (!isset($options['choice_list'])) {
            $defaultOptions['choice_list'] = new MandangoDocumentChoiceList(
                $options['mandango'],
                $options['class'],
                $options['field'],
                $options['query'],
                $options['choices'],
                $options['preferred_choices'],
                $options['property'],
                $options['group_by']
            );
        }

        return $defaultOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mandango_document';
    }
}
