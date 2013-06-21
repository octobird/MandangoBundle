<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\MandangoBundle\Form\ChoiceList;

use Symfony\Component\Form\Exception\StringCastException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Mandango\Query;
use Mandango\Mandango;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * MandangoDocumentChoiceList.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MandangoDocumentChoiceList extends ObjectChoiceList
{
    private $mandango;
    private $class;
    private $query;

    private $documents;

    private $preferredChoices;

    /**
     * Whether the entities have already been loaded.
     *
     * @var Boolean
     */
    private $loaded = false;

    public function __construct(Mandango $mandango, $class, Query $query = null, array $choices = array(), $preferredChoices = array(), $labelPath = null, $groupPath = null, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->mandango = $mandango;
        $this->class = $class;
        $this->query = $query;
        $this->preferredChoices = $preferredChoices;

        parent::__construct($choices, $labelPath, $preferredChoices, $groupPath, null, $propertyAccessor);
    }

    /**
     * Returns the list of entities
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getChoices()
    {
        if (!$this->loaded) {
            $this->load();
        }

        return parent::getChoices();
    }

    public function getDocuments()
    {
        if (null === $this->documents) {
            $this->load();
        }

        return $this->documents;
    }

    /**
     * Returns the values for the entities
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getValues()
    {
        if (!$this->loaded) {
            $this->load();
        }

        return parent::getValues();
    }

    /**
     * Returns the choice views of the preferred choices as nested array with
     * the choice groups as top-level keys.
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getPreferredViews()
    {
        if (!$this->loaded) {
            $this->load();
        }

        return parent::getPreferredViews();
    }

    /**
     * Returns the choice views of the choices that are not preferred as nested
     * array with the choice groups as top-level keys.
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getRemainingViews()
    {
        if (!$this->loaded) {
            $this->load();
        }

        return parent::getRemainingViews();
    }

    /**
     * Returns the entities corresponding to the given values.
     *
     * @param array $values
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getChoicesForValues(array $values)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return parent::getChoicesForValues($values);
    }

    /**
     * Returns the values corresponding to the given entities.
     *
     * @param array $entities
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getValuesForChoices(array $entities)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return parent::getValuesForChoices($entities);
    }

    /**
     * Returns the indices corresponding to the given entities.
     *
     * @param array $entities
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getIndicesForChoices(array $entities)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return parent::getIndicesForChoices($entities);
    }

    /**
     * Returns the entities corresponding to the given values.
     *
     * @param array $values
     *
     * @return array
     *
     * @see Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface
     */
    public function getIndicesForValues(array $values)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return parent::getIndicesForValues($values);
    }

    /**
     * Creates a new unique index for this entity.
     *
     * If the entity has a single-field identifier, this identifier is used.
     *
     * Otherwise a new integer is generated.
     *
     * @param mixed $entity The choice to create an index for
     *
     * @return integer|string A unique index containing only ASCII letters,
     *                        digits and underscores.
     */
    protected function createIndex($entity)
    {
        return parent::createIndex($entity);
    }

    /**
     * Creates a new unique value for this entity.
     *
     * If the entity has a single-field identifier, this identifier is used.
     *
     * Otherwise a new integer is generated.
     *
     * @param mixed $entity The choice to create a value for
     *
     * @return integer|string A unique value without character limitations.
     */
    protected function createValue($entity)
    {
        return parent::createValue($entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function fixIndex($index)
    {
        $index = parent::fixIndex($index);

        return $index;
    }

    protected function load()
    {
        if ($this->query) {
            $documents = $this->query->all();
        } else {
            $documents = $this->mandango->getRepository($this->class)->createQuery()->all();
        }
        $this->documents = $documents;

        try {
            // The second parameter $labels is ignored by ObjectChoiceList
            parent::initialize($documents, array(), $this->preferredChoices);
        } catch (StringCastException $e) {
            throw new StringCastException(str_replace('argument $labelPath', 'option "property"', $e->getMessage()), null, $e);
        }

        $this->loaded = true;
    }
}
