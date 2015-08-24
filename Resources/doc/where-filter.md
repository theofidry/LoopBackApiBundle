# Where filter

Note: the where filter is based on Dunglas' [SearchFilter](https://github.com/dunglas/DunglasApiBundle/blob/master/Doctrine/Orm/Filter/SearchFilter.php). Dunglas' bundle uses the `search` term for this kind of filter like Django whereas Loopback uses `where`. As this bundle is more similar to LoopBack for filter syntax, I decided to keep up with it.

Where filters are used to filter a collection by applying a mask (making match some properties).

REST Syntax:

```
?filter[where][property]=value     # Will operate an exact search match
?filter[where][property][op]=value # Depends of the operator
```

where `property` is the name of the property, `op` the operator and `value` takes the value wished (case sensitive). The value is by default a `string`. If you wish to test a `boolean` value, test it against `0` (`false`) or `1` (`true`) - of course if the property is a number, it will be tested against a number and not against a boolean value!


## Special values

### Search on embedded relation property

**WARNING**: not supported yet.

```
?filter[where][property.propOfEntity][op]=value
```

### Boolean values

To refer to a boolean value at `false`, use `0` and not `false` as a value. The same way use `1` for `true`.

Example: `url?filter[where][booleanProperty]=1`

### Date values

The date value is a date string format which must be understood by [`\DateTime`](http://php.net/manual/fr/datetime.construct.php).

Example: `url?filter[where][property][op]=2015-04-28`

### Null values

To search for value `null` just use `null`!

Example: `url?filter[where][property][op]=null`

Note: It is impossible to search for a string value `"null"`.

### Empty values

Careful here, an empty value for a REST API is not the same as an empty value in PHP. Indeed in PHP a value is empty if undefined, null or equivalent to `false`. For the API, and empty value is only the later:
* for a string: `""`
* for a number: `0`
* for a date: invalid, will throw an error
* for a boolean: `false`

To search for an empty value just omit the value:

Example: `?filter[where][property][op]=` or `?filter[where][property][op]` (the first one is built as the second by
your client).

## Operators

| Operator | Description |
|----------|:-------------|
| or | Logical OR operator |
| gt, gte | Numerical greater than (>); greater than or equal (>=). Valid only for numerical and date values. For Geopoint values, the units are in miles by default. See Geopoint for more information. |
| lt, lte | Numerical less than (<); less than or equal (<=). Valid only for numerical and date values. For geolocation values, the units are in miles by default. See Geopoint for more information. |
| between | True if the value is between the two specified values: greater than or equal to first value and less than or equal to second value. For geolocation values, the units are in miles by default. See Geopoint for more information. |
| neq | Not equal (!=) |
| like, nlike | LIKE / NOT LIKE operators for use with regular expressions. The regular expression format depends on the backend data source. |

### `or`

Note: This operator is the only one which differs from others since it's the only one you can use with other operators.

REST syntax: `?filter[where][or][0][property1][op]=val1&filter[where][or][0][property2][op]=val2`

`0` in the example above is a key of the `or` operator. The operator compare the two values of the same key. As a result, if you which to use multiple time this operator, you can use several key like in the second example.

#### Example #1

Example: `field1 === 'val1' || field2 === 'val2'`

REST syntax: `?filter[where][or][0][][field1]=val1&filter[where][or][0][][field2]=val2`

#### Example #2

Example: `( (field1 === 'val1' || field2 === 'val2') ) && ( (field3 === 'val3' || field4 === 'val4') )`

REST syntax:

```
?filter[where][or][0][][field1]=val1&filter[where][or][0][][field2]=val2
&filter[where][or][1][][field3]=val3&filter[where][or][1][][field4]=val4
```

### `gt(e)`/`lt(e)`

Example: the following query returns all instances of the employee entity using a where filter that specifies a date property after (greater than) the specified date:

REST syntax:

`/employees?filter[where][date][gt]=2014-04-01T18:30:00.000Z`

The date value may be simplified. The format does not really matter but keep in mind that behind it, the value retrieved is instantiated as a `\DateTime` with the default timezone of the application.

### `between`

Example of between operator: `?filter[where][price][between][0]=0&filter[where][price][between][1]=7`.

Note: the keys `0` and `1` are optional, unlike the `or` operator, only two values are expected with `between`. It is
 assumed that the first values is the "lower" one.

### `neq`

Examples:
* looking for names which are not null: `?filter[where][name][neq]=null`
* looking for names which are not empty: `?filter[where][name][neq]=`
* looking for names which are not empty: `?filter[where][name][neq]=""`
* looking for names which are not equal to a given value: `?filter[where][name][neq]=a%20name`

### `like`/`nlike`

The like and nlike (not like) operators enable you to match SQL regular expressions.

Previous chapter: [Order filter](order-filter.md)
