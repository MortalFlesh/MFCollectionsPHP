<?php declare(strict_types=1);

namespace MF\Collection\Mutable\Enhanced;

use MF\Collection\Mutable\IList;
use MF\Parser\CallbackParser;

class ListCollection extends \MF\Collection\Mutable\ListCollection
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

    /** @return \MF\Collection\Immutable\IList */
    public function asImmutable()
    {
        return \MF\Collection\Immutable\Enhanced\ListCollection::from($this->toArray());
    }
}
