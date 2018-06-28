MF/CollectionsPHP - documentation
================================

Welcome to the `MF/CollectionsPHP` documentation!
See [Repository](https://github.com/MortalFlesh/MFCollectionsPHP)


## Base Interfaces

### <a name="collection-interface"></a>ICollection
- basic Interface for Collections
- extends `IteratorAggregate, Countable`
- [see ICollection](/MFCollectionsPHP/ICollection.md)
- [see Mutable ICollection](/MFCollectionsPHP/Mutable-ICollection.md)
- [see Immutable ICollection](/MFCollectionsPHP/Immutable-ICollection.md)
- [see Generic ICollection](/MFCollectionsPHP/Generic-ICollection.md)

### <a name="list-interface"></a>IList
- extends `ICollection`
- [see IList](/MFCollectionsPHP/IList.md)
- [see Mutable IList](/MFCollectionsPHP/Mutable-IList.md)
- [see Immutable IList](/MFCollectionsPHP/Immutable-IList.md)
- [see Generic IList](/MFCollectionsPHP/Generic-IList.md)

### <a name="map-interface"></a>IMap
- extends `ICollection, ArrayAccess`
- [see IMap](/MFCollectionsPHP/IMap.md)
- [see Mutable IMap](/MFCollectionsPHP/Mutable-IMap.md)
- [see Immutable IMap](/MFCollectionsPHP/Immutable-IMap.md)
- [see Generic IMap](/MFCollectionsPHP/Generic-IMap.md)

### <a name="seq-interface"></a>ISeq
- extends `ICollection`
- [see Immutable seq](https://github.com/MortalFlesh/MFCollectionsPHP#immutable-seq)

### <a name="tuple-interface"></a>ITuple
- extends `ArrayAccess`, `Countable`
- [see Immutable tuple](https://github.com/MortalFlesh/MFCollectionsPHP#immutable-tuple)
