<?php

namespace MF\Collection\Mutable\Enhanced;

use MF\Collection\Mutable\IList;
use MF\Parser\CallbackParser;

class ListCollection extends \MF\Collection\Mutable\ListCollection
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
    public function map($callback): IList
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::map($callback);
    }

    /**
     * @param callable $callback (value:mixed,index:int):bool
     * @return static
     */
    public function filter($callback): IList
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

    /** @return \MF\Collection\Immutable\IList */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\Enhanced\ListCollection::of($this->toArray());
    }
}
