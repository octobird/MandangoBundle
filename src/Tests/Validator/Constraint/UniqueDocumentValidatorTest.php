<?php

namespace Mandango\MandangoBundle\Tests\Validator\Constraint;

use Mandango\MandangoBundle\Tests\TestCase;
use Mandango\MandangoBundle\Validator\Constraint\UniqueDocument;
use Mandango\MandangoBundle\Validator\Constraint\UniqueDocumentValidator;
use Model\Article;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueDocumentValidatorTest extends TestCase
{
    /** @var UniqueDocumentValidator */
    private $validator;
    /** @var \Symfony\Component\Validator\ExecutionContext|\Symfony\Component\Validator\Context\ExecutionContext|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    protected function setUp()
    {
        parent::setUp();
        $contextClassName = class_exists(\Symfony\Component\Validator\ExecutionContext::class) ? \Symfony\Component\Validator\ExecutionContext::class : \Symfony\Component\Validator\Context\ExecutionContext::class;
        $this->context = $this->createMock($contextClassName);
        $this->validator = new UniqueDocumentValidator($this->mandango);
        $this->validator->initialize($this->context);

        $this->context->expects($this->any())
            ->method('getClassName')
            ->will($this->returnValue(__CLASS__));
    }

    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
    }

    /**
      * @expectedException \InvalidArgumentException
      * @dataProvider IsValidNotMandangoDocumentProvider
      */
    public function testIsValidNotMandangoDocument($document)
    {
        $constraint = new UniqueDocument(array('fields' => array('title')));
        $this->validator->validate($document, $constraint);
    }

    public function IsValidNotMandangoDocumentProvider()
    {
        return array(
            array('foo'),
            array(1),
            array(1.1),
            array(true)
        );
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @dataProvider isValidFieldsNotValidProvider
     */
    public function testIsValidFieldsNotValid($fields)
    {
        $this->validator->validate($this->createArticle(), $this->createConstraint($fields));
    }

    public function isValidFieldsNotValidProvider()
    {
        return array(
            array(1),
            array(1.1),
            array(true),
            array(new \ArrayObject())
        );
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    public function testIsValidAtLeastOneField()
    {
        $this->validator->validate($this->createArticle(), $this->createConstraint(array()));
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @dataProvider isValidCaseInsensitiveNotValidProvider
     */
    public function testIsValidCaseInsensitiveNotValid($caseInsensitive)
    {
        $constraint = $this->createConstraint('title');
        $constraint->caseInsensitive = $caseInsensitive;
        $this->validator->validate($this->createArticle(), $constraint);
    }

    public function isValidCaseInsensitiveNotValidProvider()
    {
        return array(
            array('foo'),
            array(1),
            array(1.1),
            array(true),
            array(new \ArrayObject())
        );
    }

    public function testIsValidWithoutResults()
    {
        $article = $this->createArticle()->setTitle('foo');
        $this->context->expects($this->never())
            ->method('buildViolation');
        $this->validator->validate($article, $this->createConstraint('title'));
    }

    public function testIsValidSameResult()
    {
        $article = $this->createArticle()->setTitle('foo')->save();
        $this->context->expects($this->never())
            ->method('buildViolation');
        $this->validator->validate($article, $this->createConstraint('title'));
    }

    public function testIsValidOneField()
    {
        $article1 = $this->createArticle()->setTitle('foo')->save();
        $article2 = $this->createArticle()->setTitle('foo');

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->method('atPath')->willReturnSelf();

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('This value is already used.')
            ->willReturn($builder);

        $this->validator->validate($article2, $this->createConstraint('title'));
    }

    public function testIsValidCaseInsensitive()
    {
        $article1 = $this->createArticle()->setTitle('foo')->save();
        $article2 = $this->createArticle()->setTitle('foO');

        $constraint = $this->createConstraint('title');
        $constraint->caseInsensitive = array('title');

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->method('atPath')->willReturnSelf();

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('This value is already used.')
            ->willReturn($builder);

        $this->validator->validate($article2, $constraint);
    }

    private function createConstraint($fields)
    {
        return new UniqueDocument(array('fields' => $fields));
    }

    /**
     * @return Article
     */
    private function createArticle()
    {
        return $this->mandango->create('Model\Article');
    }
}
