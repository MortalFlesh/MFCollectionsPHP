<?php declare(strict_types=1);

namespace MF\Collection\Immutable\Enhanced;

use MF\Collection\Immutable\IList;
use MF\Parser\CallbackParser;

class ListCollection extends \MF\Collection\Immutable\ListCollection
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
     * @param callable|string $callback (value:mixed,index:int):bool
     * @return mixed
     */
    public function firstBy($callback)
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::firstBy($callback);
    }

    /**
     * @param callable|string $callback (value:mixed,index:mixed):bool
     */
    public function containsBy($callback): bool
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::containsBy($callback);
    }

    /**
     * @param callable|string $callback (value:mixed,index:int):mixed
     * @return static
     */
    public function map($callback): IList
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::map($callback);
    }

    /**
     * @param callable|string $callback (value:mixed,index:int):bool
     * @return static
     */
    public function filter($callback): IList
    {
        $callback = $this->callbackParser->parseArrowFunction($callback);

        return parent::filter($callback);
    }

    /**
     * @param callable|string $reducer (total:mixed,value:mixed,index:int,list:IList):mixed
     * @param mixed|null $initialValue
     * @return mixed
     */
    public function reduce($reducer, $initialValue = null)
    {
        $reducer = $this->callbackParser->parseArrowFunction($reducer);

        return parent::reduce($reducer, $initialValue);
    }

    /** @return \MF\Collection\Mutable\IList */
    public function asMutable()
    {
        return \MF\Collection\Mutable\Enhanced\ListCollection::from($this->toArray());
    }
}
