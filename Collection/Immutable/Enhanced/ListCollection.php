<?php

namespace MF\Collection\Immutable\Enhanced;

use MF\Parser\CallbackParser;

class ListCollection extends \MF\Collection\Immutable\ListCollection
{
    /** @var CallbackParser */
    private $callbackParser;

    public function __construct()
    {
        parent::__construct();
        $this->callbackParser = new CallbackParser();
    }

    /**
     * @param callable $callback (value:mixed,index:int):mixed
     * @return static
     */
    public function map($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::map($callback);
    }

    /**
     * @param callable $callback (value:mixed,index:int):bool
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::filter($callback);
    }

    /**
     * @param callable $reducer (total:mixed,value:mixed,index:int,list:List):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return \MF\Collection\Mutable\ListCollection
     */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Enhanced\ListCollection::of($this->toArray());
    }
}
