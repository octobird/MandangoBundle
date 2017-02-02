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
use Mandango\MandangoBundle\Form\DataTransformer\MandangoDocumentsToArrayTransformer;
use Mandango\MandangoBundle\Form\EventListener\MergeGroupListener;
use Symfony\Component\Form\Extension\Core\EventListener\MergeCollectionListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Mandango\Mandango;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * MandangoDocumentType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoDocumentType extends AbstractType
{
    private $mandango;

    private $choiceListCache = array();

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
        if ($options['multiple'] && $options['reference']) {
            $dispatcher = $builder->getEventDispatcher();
            $listeners = $builder->getEventDispatcher()->getListeners();
            $builder->getEventDispatcher()->removeListener(FormEvents::SUBMIT, $listeners[FormEvents::SUBMIT][0]);
            $builder
                ->addEventSubscriber(new MergeGroupListener($builder->getEventDispatcher()))
                ->addViewTransformer(new MandangoDocumentsToArrayTransformer($options['choice_list']), true)
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choiceList = function (Options $options) {
            $hash = md5(json_encode(array($options['class'], $options['query'], $options['field'])));
            if (!isset($choiceListCache[$hash])) {
                $choiceListCache[$hash] = new MandangoDocumentChoiceList(
                    $options['mandango'],
                    $options['class'],
                    $options['query'],
                    $options['choices'],
                    $options['preferred_choices'],
                    $options['property'],
                    $options['group_by']
                );
            }

            return $choiceListCache[$hash];
        };

        $resolver->setDefaults(array(
            'template'          => 'choice',
            'property'          => null,
            'multiple'          => false,
            'reference'          => false,
            'expanded'          => false,
            'mandango'          => $this->mandango,
            'choice_list'       => $choiceList,
            'class'             => null,
            'field'             => null,
            'query'             => null,
            'choices'           => array(),
            'preferred_choices' => array(),
            'group_by'          => null,
        ));

        $resolver->setRequired(array('class'));
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
