<?php

namespace MFCollections\Collections\Immutable\Enhanced;

use MFCollections\Services\Parsers\CallbackParser;

class Map extends \MFCollections\Collections\Immutable\Map
{
    /** @var CallbackParser */
    private $callbackParser;

    public function __construct()
    {
        parent::__construct();
        $this->callbackParser = new CallbackParser();
    }

    /**
     * @param callable (key:mixed,value:mixed):mixed $callback
     * @return static
     */
    public function map($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::map($callback);
    }

    /**
     * @param callable (key:mixed,value:mixed):bool $callback
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::filter($callback);
    }

    /**
     * @param callable (total:mixed,value:mixed,key:mixed,map:Map):mixed $reducer
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return \MFCollections\Collections\Enhanced\Map
     */
    public function asMutable()
    {
        return \MFCollections\Collections\Enhanced\Map::createFromArray($this->toArray());
    }
}