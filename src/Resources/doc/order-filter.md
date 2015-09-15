# Order filter

Order filters are used to order **a collection** by properties in ascending or descending orders.

REST syntax:

```
?filter[order][property]=<ASC|DESC>
```

where `property` is the name of the property and `ASC`, `DESC` the order value (case insensitive). **The order of the order filters is important**: if we specify the filter `?order[name]=asc&order[id]=desc`, the result will be a collection ordered by `name` in ascending order and when some names are equal, ordered by `id` in ascending order.

#### Not supported yet

The following feature is not supported yet.

If there is an embedded relation and you want to apply the ordering a property of the other entity:

```
?filter[order][property.propOfEntity]=<ASC|DESC>
```

Previous chapter: [Introduction](introduction.md)<br />
Next chapter: [Where filter](where-filter.md)
