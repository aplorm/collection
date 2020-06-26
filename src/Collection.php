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

namespace Aplorm\Collection;

use Aplorm\Collection\Exception\ItemNotFoundException;
use Aplorm\Collection\Exception\WrongItemTypeException;
use Aplorm\Common\Collection\CollectionInterface;
use Aplorm\Common\Collection\CollectionnableElementInterface;
use WeakReference;

class Collection implements CollectionInterface
{
    /**
     * @var array<int, WeakReference<CollectionnableElementInterface>>
     */
    protected array $elements = [];

    /**
     * @var array<int, int>
     */
    protected array $indices = [];

    /** @var array<int, int> this array store oid position to avoid loop to find oid in indices array */
    protected array $elementPosition = [];

    protected int $position = 0;

    protected ?int $oid = null;

    /**
     * Collection constructor.
     *
     * @param array<CollectionnableElementInterface>|null $elements
     *
     * @throws WrongItemTypeException
     */
    public function __construct(?array $elements = null)
    {
        if (null !== $elements) {
            $this->fromArray($elements);
        }
    }

    /**
     * @param array<mixed> $elements
     *
     * @throws WrongItemTypeException
     */
    public function fromArray(array $elements): void
    {
        foreach ($elements as $element) {
            if ($element instanceof WeakReference) {
                $instance = $element->get();
                if (!$instance instanceof CollectionnableElementInterface) {
                    throw new WrongItemTypeException($instance, CollectionnableElementInterface::class);
                }

                $this->doAdd($instance);

                continue;
            }

            if (!$element instanceof CollectionnableElementInterface) {
                throw new WrongItemTypeException($element, CollectionnableElementInterface::class);
            }

            $this->doAdd($element);
        }
    }

    public function toArray(): array
    {
        return array_map(function (WeakReference $reference) {
            return $reference->get();
        }, $this->elements);
    }

    public function add(CollectionnableElementInterface $object): CollectionInterface
    {
        return $this->doAdd($object, null);
    }

    public function remove(CollectionnableElementInterface $object): CollectionInterface
    {
        $oid = spl_object_id($object);

        if (!isset($this->elements[$oid])) {
            return $this;
        }

        $object->removeCollection($this);
        $this->doDestroy($oid);

        return $this;
    }

    public function destroy(CollectionnableElementInterface $object): void
    {
        $oid = spl_object_id($object);

        $this->doDestroy($oid);
    }

    /**
     * Return the current element.
     *
     * @see https://php.net/manual/en/iterator.current.php
     *
     * @return mixed can return any type
     */
    public function current()
    {
        if (null === $this->oid) {
            $this->oid = $this->indices[$this->position];
        }

        return $this->elements[$this->oid]->get();
    }

    /**
     * Move forward to next element.
     *
     * @see https://php.net/manual/en/iterator.next.php
     *
     * @return void any returned value is ignored
     */
    public function next(): void
    {
        $this->oid = null;
        ++$this->position;
        if (isset($this->indices[$this->position])) {
            $this->oid = $this->indices[$this->position];
        }
    }

    /**
     * Return the key of the current element.
     *
     * @see https://php.net/manual/en/iterator.key.php
     *
     * @return string|float|int|bool|null scalar on success, or null on failure
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid.
     *
     * @see https://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return isset($this->indices[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @see https://php.net/manual/en/iterator.rewind.php
     *
     * @return void any returned value is ignored
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->oid = null;
    }

    /**
     * Whether a offset exists.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->indices[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed can return all value types
     */
    public function offsetGet($offset)
    {
        if (!isset($this->indices[$offset])) {
            $backTrace = debug_backtrace();
            $data = array_shift($backTrace);
            trigger_error('Undefined offset: '.$offset.' ON '.$data['file'].':'.$data['line']);
        }
        $oid = $this->indices[$offset];

        return $this->elements[$oid]->get();
    }

    /**
     * Offset to set.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @throws WrongItemTypeException
     */
    public function offsetSet($offset, $value): void
    {
        if (null !== $offset && !\is_int($offset)) {
            throw new WrongItemTypeException($offset, 'int');
        }

        $this->doAdd($value, $offset);
    }

    /**
     * Offset to unset.
     *
     * @see https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     */
    public function offsetUnset($offset): void
    {
        if (!isset($this->indices[$offset])) {
            return;
        }

        $oid = $this->indices[$offset];

        $this->elements[$oid]->get()->removeCollection($this);

        $this->doDestroy($oid);
    }

    /**
     * Count elements of an object.
     *
     * @see https://php.net/manual/en/countable.count.php
     *
     * @return int The custom count as an integer.
     *             </p>
     *             <p>
     *             The return value is cast to an integer.
     */
    public function count(): int
    {
        return \count($this->indices);
    }

    public function exist(CollectionnableElementInterface $object): bool
    {
        return isset($this->elements[spl_object_id($object)]);
    }

    /**
     * @throws ItemNotFoundException when an inexistant element is replaced
     */
    public function replace(CollectionnableElementInterface $needle, CollectionnableElementInterface $replacement): CollectionInterface
    {
        if (!$this->exist($needle)) {
            throw new ItemNotFoundException();
        }

        $oid = spl_object_id($needle);
        $offset = $this->elementPosition[$oid];

        return $this->doAdd($replacement, $offset);
    }

    /**
     * @param int $oid object id to remove from collection
     */
    protected function doDestroy(int $oid): CollectionInterface
    {
        unset($this->elements[$oid], $this->indices[$this->elementPosition[$oid]], $this->elementPosition[$oid]);

        return $this;
    }

    protected function doAdd(CollectionnableElementInterface $object, ?int $index = null): CollectionInterface
    {
        $oid = spl_object_id($object);

        if (isset($this->elements[$oid])) {
            return $this;
        }

        $object->addCollection($this);
        $this->elements[$oid] = WeakReference::create($object);
        if (null === $index) {
            $this->indices[] = $oid;
            $this->elementPosition[$oid] = array_key_last($this->indices);

            return $this;
        }

        $this->indices[$index] = $oid;
        $this->elementPosition[$oid] = $index;

        return $this;
    }
}
