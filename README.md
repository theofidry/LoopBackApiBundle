# LoopBackApiundle

[![Build Status](https://travis-ci.org/theofidry/LoopBackApiBundle.svg?branch=master)](https://travis-ci.org/theofidry/LoopBackApiBundle) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/theofidry/LoopBackApiBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/theofidry/LoopBackApiBundle/?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/e7cbcdb9-f024-43e0-b7ba-7a002949aa98/big.png)](https://insight.sensiolabs.com/projects/e7cbcdb9-f024-43e0-b7ba-7a002949aa98)

[LoopBack](http://loopback.io/) like Doctrine ORM filters for [DunglasApiBundle](https://github.com/dunglas/DunglasApiBundle).

**WARNING**: Work in Progress, not ready for production.

# Documentation

## Table of Contents

* [Order filters](#order-filters)
* [Where filters](#where-filters)
  * [Boolean values](#boolean-values)
  * [Date values](#date-values)
  * [Empty values](#empty-values)
  * [Operators](#operators)
    * [and/or](#andor)
    * [gt(e)/lt(e)](#gtelte)
    * [between](#between)
    * [like/nlike](#likenlike)

### Order filters

Order filters are used to order **a collection** by properties in ascending or descending orders.

Syntax:
```
url?filter[order][property]=<ASC|DESC>
```

where `property` is the name of the property and `ASC`, `DESC` the order value (case insensitive). **The order of the order filters is important**: if we specify the filter `?order[name]=asc&order[id]=desc`, the result will be a collection ordered by `name` in ascending order and when some names are equal, ordered by `id` in descending order.

If there is an embedded relation and you want to apply the ordering a property of the other entity:

```
url?filter[order][property.propOfEntity]=<ASC|DESC>
```

### Where filters

Note: the where filter is based on Dunglas' SearchFilter. Dunglas' bundle uses `search` for this kind of filter like Django whereas Loopback uses `where`. As this bundle is more similar to LoopBack for filter syntax, I decided to keep up with it.

Where filters are used to filter a collection by applying a mask (making match some properties).

Syntax:
```
url?filter[where][property][op]=value
```

where `property` is the name of the property, `op` the operator and `value` takes the value wished (case sensitive). The value is by default a `string`. If you wish to test a `boolean` value, test it against `0` (`false`) or `1` (`true`) - of course if the property is a number, it will be tested against a number and not against a boolean value!

#### Search on embedded relation property

```
url?filter[where][property.propOfEntity][op]=value
```

#### Boolean values

To refer to a boolean value at `false`, use `0` and not `false` as a value. The same way use `1` for `true`.

Example: `url?filter[where][booleanProperty]=1`

#### Date values

The date value is a date string format which must be understood by [`\DateTime`](http://php.net/manual/fr/datetime.construct.php).

Example: `url?filter[where][property][op]=2015-04-28`

#### Null values

To search for value `null` just use `null`!

Example: `url?filter[where][property][op]=null`

Note: this feature has a drawback, it becomes impossible to search for a string value `"null"`.

#### Empty values

Careful here, an empty value for a REST API is not the same as an empty value in PHP. Indeed in PHP a value is empty if undefined, null or equivalent to `false`. For the API, and empty value is only the later:
* for a string: `""`
* for a number: `0`
* for a date: invalid, will throw an error
* for a boolean: `false`

To search for an empty value just omit the value:

Example: `url?filter[where][property][op]=` or `url?filter[where][property][op]` (the first one is builded as the second by your client).

#### Operators

| Operator | Description |
|----------|:-------------|
| or | Logical OR operator |
| gt, gte | Numerical greater than (>); greater than or equal (>=). Valid only for numerical and date values. For Geopoint values, the units are in miles by default. See Geopoint for more information. |
| lt, lte | Numerical less than (<); less than or equal (<=). Valid only for numerical and date values. For geolocation values, the units are in miles by default. See Geopoint for more information. |
| between | True if the value is between the two specified values: greater than or equal to first value and less than or equal to second value. For geolocation values, the units are in miles by default. See Geopoint for more information. |
| neq | Not equal (!=) |
| like, nlike | LIKE / NOT LIKE operators for use with regular expressions. The regular expression format depends on the backend data source. |

##### `or`

###### Example #1

Example: `field1 === 'val1' || field2 === 'val2'`

Syntax: `?filter[where][or][0][field1]=val1&filter[where][or][0][field2]=val2`


Which results in:

```php
[ 'filter' => [
    'where' => [
        'or' => [
            0 => [
                'field1' => 'val1',
                'field2' => 'val2'
            ]
        ]
    ]
]
```

###### Example #2

Example: `( (field1 === 'val1' || field2 === 'val2') ) && ( (field3 === 'val3' || field4 === 'val4') )`

Syntax:

```
?filter[where][or][0][field1]=val1&filter[where][or][0][field2]=val2
&filter[where][or][1][field3]=val3&filter[where][or][1][field4]=val4
```

Which results in:

```php
[ 'filter' => [
    'where' => [
        'or' => [
            0 => [
                'field1' => 'val1',
                'field2' => 'val2'
            ],
            1 => [
                'field3' => 'val3',
                'field4' => 'val4'
            ]
        ]
    ]
]
```

Note: as you can see, between each vakye of the array `query['filter']['where']['or']`, a `and` is applied.

##### `gt(e)`/`lt(e)`

Example: the following query returns all instances of the employee entity using a where filter that specifies a date property after (greater than) the specified date:

REST syntax:

`/employees?filter[where][date][gt]=2014-04-01T18:30:00.000Z`

The date value may be simplified. The format does not really matter but keep in mind that behind it, the value retrieved is instantiated as a `\DateTime` with the default timezone of the application.

##### `between`

Example of between operator: `filter[where][price][between][0]=0&filter[where][price][between][1]=7`.

Note: the keys `0` and `1` are optional, unlike the `or` operator, only two values are expected with `between`. It is
 assumed that the first values is the "lower" one.

##### `like`/`nlike`

The like and nlike (not like) operators enable you to match SQL regular expressions.

[1]: https://github.com/dunglas/DunglasApiBundle

# License

[![License](https://img.shields.io/packagist/l/doctrine/orm.svg?style=flat-square)](https://github.com/theofidry/LoopBackApiBundle/edit/master/LICENSE)
