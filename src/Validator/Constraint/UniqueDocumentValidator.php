<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\MandangoBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Mandango\Mandango;
use Mandango\Document\Document;

/**
 * UniqueConstraint.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class UniqueDocumentValidator extends ConstraintValidator
{
    private $mandango;

    /**
     * @param Mandango $mandango A mandango.
     */
    public function __construct(Mandango $mandango)
    {
        $this->mandango = $mandango;
    }

    /**
     * Validates the document uniqueness.
     *
     * @param \Mandango\Document\Document $value      The document.
     * @param Constraint|UniqueDocument   $constraint The constraint.=
     */
    public function validate($value, Constraint $constraint)
    {
        $document = $this->parseDocument($value);
        $fields = $this->parseFields($constraint->fields);
        $caseInsensitive = $this->parseCaseInsensitive($constraint->caseInsensitive);

        $query = $this->createQuery($document, $fields, $caseInsensitive);
        $numberResults = $query->count();

        if (0 === $numberResults) {
            return;
        }

        if (1 === $numberResults) {
            $result = $query->one();
            if ($result === $document) {
                return;
            }
        }

        if ($this->context) {
            $this->context->buildViolation($constraint->message)
                ->atPath($fields[0])
                ->addViolation();
        }
    }

    private function parseDocument($document)
    {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('The value must be a mandango document.');
        }

        return $document;
    }

    private function parseFields($fields)
    {
        if (is_string($fields)) {
            $fields = array($fields);
        } elseif (is_array($fields)) {
            if (0 === count($fields)) {
                throw new ConstraintDefinitionException('At least one field has to be specified.');
            }
        } else {
            throw new UnexpectedTypeException($fields, 'array');
        }

        return $fields;
    }

    private function parseCaseInsensitive($caseInsensitive)
    {
        if (!is_array($caseInsensitive)) {
            throw new UnexpectedTypeException($caseInsensitive, 'array');
        }

        return $caseInsensitive;
    }

    private function createQuery(Document $document, array $fields, array $caseInsensitive)
    {
        $repository = $this->mandango->getRepository(get_class($document));
        $criteria = $this->createCriteria($document, $fields, $caseInsensitive);

        return $repository->createQuery($criteria);
    }

    private function createCriteria(Document $document, array $fields, array $caseInsensitive)
    {
        $criteria = array();
        foreach ($fields as $field) {
            $value = $document->get($field);
            if (in_array($field, $caseInsensitive)) {
                $value = new \MongoDB\BSON\Regex(sprintf('^%s$', $value), 'i');
            }
            $criteria[$field] = $value;
        }

        return $criteria;
    }
}
