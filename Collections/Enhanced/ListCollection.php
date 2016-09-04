<?php

namespace MFCollections\Collections\Enhanced;

use MF\Parser\CallbackParser;

class ListCollection extends \MFCollections\Collections\ListCollection
{
    /** @var CallbackParser */
    private $callbackParser;

    public function __construct()
    {
        parent::__construct();
        $this->callbackParser = new CallbackParser();
    }

    /**
     * @param callable (value:mixed,index:int):mixed $callback
     * @return static
     */
    public function map($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::map($callback);
    }

    /**
     * @param callable (value:mixed,index:int):bool $callback
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::filter($callback);
    }

    /**
     * @param callable (total:mixed,value:mixed,index:int,list:List):mixed $reducer
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return \MFCollections\Collections\Immutable\ListCollection
     */
    public function asImmutable()
    {
        return \MFCollections\Collections\Immutable\Enhanced\ListCollection::createFromArray($this->toArray());
    }
}
