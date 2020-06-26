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

class ItemNotFoundException extends Exception
{
    private const CODE = 0X431;

    public function __construct()
    {
        parent::__construct('Object not found', self::CODE);
    }
}
