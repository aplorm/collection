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

use Aplorm\Common\Collection\CollectionInterface;
use Aplorm\Common\Collection\CollectionnableElementInterface;

class AbstractCollectionnableElement implements CollectionnableElementInterface
{
    /** @var \WeakReference<CollectionInterface>[] */
    protected array $collections = [];

    public function addCollection(CollectionInterface $collection): void
    {
        $this->collections[spl_object_id($collection)] = \WeakReference::create($collection);
    }

    public function removeCollection(CollectionInterface $collection): void
    {
        unset($this->collections[spl_object_id($collection)]);
    }

    public function __destruct()
    {
        foreach ($this->collections as $collection) {
            /** @var CollectionInterface<CollectionnableElementInterface>|null $instance */
            $instance = $collection->get();
            if (null === $instance) {
                continue;
            }

            $instance->destroy($this);
        }
    }
}
