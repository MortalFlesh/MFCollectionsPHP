<?php declare(strict_types=1);

namespace MF\Collection\Helper;

/**
 * @internal
 *
 * Modifier =
 * | Map        -> [mapper of callable]
 * | Filter     -> [filter of callable]
 * | Take       -> [limit of int]
 * | TakeUpTo   -> [limit of int]
 * | TakeWhile  -> [limit of callable]
 */
enum SeqModifier
{
    case Map;
    case Mapi;
    case Filter;
    case Take;
    case TakeUpTo;
    case TakeWhile;
}
