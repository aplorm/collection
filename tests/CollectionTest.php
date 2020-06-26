<?php
/**
 *  This file is part of the Aplorm package.
 *
 *  (c) Nicolas Moral <n.moral@live.fr>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Aplorm\Collection\Tests;

use Aplorm\Collection\Collection;
use Aplorm\Collection\Exception\ItemNotFoundException;
use Aplorm\Collection\Exception\WrongItemTypeException;
use Aplorm\Collection\Tests\Sample\CollectionnableItem;
use Aplorm\Common\Test\AbstractTest;

class CollectionTest extends AbstractTest
{
    private ?Collection $collection = null;

    public static function setupBeforeClass(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function doSetup(): void
    {
        $this->collection = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    protected function doTearDown(): void
    {
        $this->collection = null;
    }

    public function testAddElement(): void
    {
        $element = new CollectionnableItem();

        $this->collection->add($element);

        self::assertCount(1, $this->collection);
    }

    public function testAddExistantElement(): void
    {
        $element = new CollectionnableItem();

        $this->collection->add($element)->add($element);

        self::assertCount(1, $this->collection);
    }

    public function testPushElement(): void
    {
        $element = new CollectionnableItem();

        $this->collection[] = $element;

        self::assertCount(1, $this->collection);
    }

    public function testInsertAtElement(): void
    {
        $element = new CollectionnableItem();

        $this->collection[3] = $element;

        self::assertCount(1, $this->collection);
        self::assertNotNull($this->collection[3]);
    }

    public function testReplace(): void
    {
        $element = new CollectionnableItem();
        $element2 = new CollectionnableItem();
        $this->collection->add($element);
        $this->collection->replace($element, $element2);
        self::assertCount(1, $this->collection);
        self::assertEquals($element2, $this->collection[0]);
    }

    public function testInsertAt(): void
    {
        $element = new CollectionnableItem();
        $element2 = new CollectionnableItem();
        $this->collection[] = $element;
        $this->collection[0] = $element2;
        self::assertCount(1, $this->collection);
        self::assertEquals($element2, $this->collection[0]);
    }

    public function testRemoveElement(): void
    {
        $element = new CollectionnableItem();

        $this->collection->add($element)->remove($element);

        self::assertCount(0, $this->collection);
    }

    public function testRemoveInexistantElement(): void
    {
        $element = new CollectionnableItem();

        $this->collection->remove($element);

        self::assertTrue(true);
    }

    public function testWrongKeyType(): void
    {
        self::expectException(WrongItemTypeException::class);
        $element = new CollectionnableItem();

        $this->collection['bla'] = $element;
    }

    public function testExist(): void
    {
        $element = new CollectionnableItem();

        $this->collection->add($element);

        self::assertTrue($this->collection->exist($element));
    }

    public function testForeachLoop(): void
    {
        $element = new CollectionnableItem();
        $this->collection[] = $element;
        $element2 = new CollectionnableItem();
        $this->collection[] = $element2;

        foreach ($this->collection as $key => $elem) {
            self::assertTrue(isset($this->collection[$key]));
            if (1 === $key) {
                self::assertEquals($elem, $element2);
            }
        }
    }

    public function testForLoop(): void
    {
        $element = new CollectionnableItem();
        $this->collection[] = $element;
        $element2 = new CollectionnableItem();
        $this->collection[] = $element2;
        for ($i = 0, $len = \count($this->collection); $i < $len; ++$i) {
            $el = $this->collection[$i];
            if (1 === $i) {
                self::assertEquals($el, $element2);
            }
        }

        self::assertTrue(true);
    }

    public function testWhileLoop(): void
    {
        $element = new CollectionnableItem();
        $this->collection[] = $element;
        $element2 = new CollectionnableItem();
        $this->collection[] = $element2;
        $i = 0;
        while ($i < \count($this->collection)) {
            $el = $this->collection[$i];
            if (1 === $i) {
                self::assertEquals($el, $element2);
            }
            ++$i;
        }

        self::assertTrue(true);
    }

    public function testUnexistantOffset(): void
    {
        self::expectNotice();
        $elem = $this->collection[1];
    }

    public function testUnsetExistant(): void
    {
        $element = new CollectionnableItem();

        $this->collection->add($element);
        unset($this->collection[0]);
        self::assertCount(0, $this->collection);
    }

    public function testUnsetInexistant(): void
    {
        unset($this->collection[0]);
        self::assertCount(0, $this->collection);
    }

    public function testReplaceInexistant(): void
    {
        self::expectException(ItemNotFoundException::class);
        $element = new CollectionnableItem();
        $element2 = new CollectionnableItem();
        $this->collection->replace($element, $element2);
    }

    public function testWrongObjectType(): void
    {
        self::expectException(\TypeError::class);
        $element = new \stdClass();
        $this->collection[] = $element;
    }

    public function testUnsetElement(): void
    {
        $element = new CollectionnableItem();

        $this->collection->add($element);

        unset($element);

        self::assertCount(0, $this->collection);
    }

    public function testElementWhenCollectionIsUnset(): void
    {
        $element = new CollectionnableItem();

        $this->collection->add($element);

        $this->collection = null;

        self::assertNotNull($element);
    }

    public function testConstruct(): void
    {
        $element = new CollectionnableItem();
        $this->collection = new Collection([$element]);

        $this->assertCount(1, $this->collection);
    }

    public function testConstructWithWeakReference(): void
    {
        $element = new CollectionnableItem();
        $ref = \WeakReference::create($element);
        $this->collection = new Collection([$ref]);

        $this->assertCount(1, $this->collection);
    }

    public function testRemoveWeakRefPassedInConstruct(): void
    {
        $element = new CollectionnableItem();
        $ref = \WeakReference::create($element);
        $this->collection = new Collection([$ref]);
        unset($element);
        $this->assertCount(0, $this->collection);
    }

    public function testRemoveElementPassedInConstruct(): void
    {
        $element = new CollectionnableItem();
        $this->collection = new Collection([$element]);
        unset($element);
        $this->assertCount(0, $this->collection);
    }

    public function testConstructWithWrongObject(): void
    {
        $this->expectException(WrongItemTypeException::class);
        $element = new \stdClass();
        $this->collection = new Collection([$element]);
    }

    public function testConstructWithWrongObjectInReference(): void
    {
        $this->expectException(WrongItemTypeException::class);
        $element = new \stdClass();
        $ref = \WeakReference::create($element);
        $this->collection = new Collection([$ref]);
    }

    public function testToArray(): void
    {
        $element = new CollectionnableItem();
        $this->collection[] = $element;
        $element2 = new CollectionnableItem();
        $this->collection[] = $element2;

        self::assertCount(2, $this->collection->toArray());
        unset($element2);
        self::assertCount(1, $this->collection->toArray());
    }
}
