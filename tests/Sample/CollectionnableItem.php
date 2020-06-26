<?php


namespace Aplorm\Collection\Tests\Sample;


use Aplorm\Collection\AbstractCollectionnableElement;

class CollectionnableItem extends AbstractCollectionnableElement
{
    public function get(): int
    {
        return spl_object_id($this);
    }
}
