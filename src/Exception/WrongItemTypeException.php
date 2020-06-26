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

namespace Aplorm\Collection\Exception;

use Exception;

class WrongItemTypeException extends Exception
{
    private const CODE = 0X432;

    /**
     * WrongItemTypeException constructor.
     *
     * @param mixed $object   item gived
     * @param mixed $expected type expected
     */
    public function __construct($object, $expected)
    {
        parent::__construct('Expected: '.$expected.', got: '.(\is_object($object) ? \get_class($object) : \gettype($object)), self::CODE);
    }
}
