<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Enhanced;

use MF\Parser\CallbackParser;

class Map extends \MF\Collection\Immutable\Map
{
    /** @var CallbackParser */
    private $callbackParser;

    public static function create(iterable $source, $creator)
    {
        $creator = (new CallbackParser())->parseArrowFunction($creator);

        return parent::create($source, $creator);
    }

    public function __construct()
    {
        parent::__construct();
        $this->callbackParser = new CallbackParser();
    }

    /**
     * @param callable $callback (key:mixed,value:mixed):mixed
     * @return static
     */
    public function map($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::map($callback);
    }

    /**
     * @param callable $callback (key:mixed,value:mixed):bool
     * @return static
     */
    public function filter($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::filter($callback);
    }

    /**
     * @param callable $reducer (total:mixed,value:mixed,key:mixed,map:Map):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return \MF\Collection\Mutable\IMap
     */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Enhanced\Map::from($this->toArray());
    }
}
