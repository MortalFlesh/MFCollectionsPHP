<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Enhanced;

use MF\Parser\CallbackParser;

class Map extends \MF\Collection\Mutable\Map
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
     * @param callable $reducer (total:mixed,value:mixed,index:mixed,map:Map):mixed
     * @param null|mixed $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /**
     * @return \MF\Collection\Immutable\Enhanced\Map
     */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\Enhanced\Map::from($this->toArray());
    }
}
