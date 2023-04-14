<?php

namespace Fast\Eloquent;

class Collection extends \ArrayObject
{
    /**
     * Initial collection
     * 
     * @param array $input = []
     * @param int $flag
     * 
     * @return void
     */
    public function __construct(array $input = [], int $flag = \ArrayObject::STD_PROP_LIST)
    {
        parent::__construct($input, $flag);
    }

    /**
     * Collection to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        $array = objectToArray($this);

        return $array;
    }
}
