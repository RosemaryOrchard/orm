<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\Tests\OrmFunctionalTestCase;

class GH8031Test extends OrmFunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setUpEntitySchema([
            GH8031Invoice::class,
        ]);
    }

    public function testEntityIsFetched()
    {
        $entity = new GH8031Invoice(new GH8031InvoiceCode(1, 2020));
        $this->_em->persist($entity);
        $this->_em->flush();
        $this->_em->clear();

        /** @var GH8031Invoice $fetched */
        $fetched = $this->_em->find(GH8031Invoice::class, $entity->getId());
        $this->assertInstanceOf(GH8031Invoice::class, $fetched);
        $this->assertSame(1, $fetched->getCode()->getNumber());
        $this->assertSame(2020, $fetched->getCode()->getYear());

        $this->_em->clear();
        $this->assertCount(
            1,
            $this->_em->getRepository(GH8031Invoice::class)->findBy([], ['code.number' => 'ASC'])
        );
    }
}

/**
 * @Embeddable
 */
class GH8031InvoiceCode extends GH8031AbstractYearSequenceValue
{
}

/**
 * @Embeddable
 */
abstract class GH8031AbstractYearSequenceValue
{
    /**
     * @Column(type="integer", name="number", length=6)
     * @var int
     */
    protected $number;

    /**
     * @Column(type="smallint", name="year", length=4)
     * @var int
     */
    protected $year;

    public function __construct(int $number, int $year)
    {
        $this->number = $number;
        $this->year   = $year;
    }

    public function getNumber() : int
    {
        return $this->number;
    }

    public function getYear() : int
    {
        return $this->year;
    }
}

/**
 * @Entity
 */
class GH8031Invoice
{
    /**
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    private $id;

    /**
     * @Embedded(class=GH8031InvoiceCode::class)
     * @var GH8031InvoiceCode
     */
    private $code;

    public function __construct(GH8031InvoiceCode $code)
    {
        $this->code = $code;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode() : GH8031InvoiceCode
    {
        return $this->code;
    }
}
