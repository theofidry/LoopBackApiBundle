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

Of course it does not matter if another filter type is inserted between two order filters.

### Where filters

Where filters are used to filter a collection by applying a mask (making match some properties).

Syntax:
```
url?filter[where][property][op]=value
```

where `property` is the name of the property, `op` the operator and `value` takes the value wished (case sensitive). The value is by default a `string`. If you wish to test a `boolean` value, test it against `0` (`false`) or `1` (`true`) - of course if the property is a number, it will be tested against a number and not against a boolean value! A null value is equaled to `0` (`false`).

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
| and | Logical AND operator |
| or | Logical OR operator |
| gt, gte | Numerical greater than (>); greater than or equal (>=). Valid only for numerical and date values. For Geopoint values, the units are in miles by default. See Geopoint for more information. |
| lt, lte | Numerical less than (<); less than or equal (<=). Valid only for numerical and date values. For geolocation values, the units are in miles by default. See Geopoint for more information. |
| between | True if the value is between the two specified values: greater than or equal to first value and less than or equal to second value. For geolocation values, the units are in miles by default. See Geopoint for more information. |
| neq | Not equal (!=) |
| like, nlike | LIKE / NOT LIKE operators for use with regular expressions. The regular expression format depends on the backend data source. |

##### `and`/`or`

Example: find all entities for which `title` equal to `'My Post'` and `content` to `Hello`.

REST syntax:

`?filter[where][and][0][title]=My%20Post&filter[where][and][1][content]=Hello`

And behind the scene will generate the following array for the `query parameter`:

```php
[ 'filter' => [
    'where' => [
        'and' => [
            'title'   => 'My Post',
            'content' => 'Hello'
        ]
    ]
]
````

Note: the `[0]` and `[1]` are not mandatory. You can use `[]`. However, if you do so, the order does matter.

**Example**: logical expression: `(field1 === 'foo' && field2 === 'bar') || 'field1' === 'morefoo')`.

REST syntax:

`?filter[where][or][0][and][0][field1]=foo&filter[where][or][0][and][1][field2]=bar&filter[where][or][1][field1]=morefoo`

```php
[ 'filter' => [
    'where' => [
        'or' => [
            'and' => [
                'field1' => 'foo',
                'field2' => 'bar'
            ],
            'field1' => 'morefoo'
        ]
    ]
]
````

##### `gt(e)`/`lt(e)`

Example: the following query returns all instances of the employee entity using a where filter that specifies a date property after (greater than) the specified date:

REST syntax:

`/employees?filter[where][date][gt]=2014-04-01T18:30:00.000Z`

The date value may be simplified. The format does not really matter but keep in mind that behind it, the value retrieved is instantiated as a `\DateTime` with the default timezone of the application.

##### `between`

Example of between operator: `filter[where][price][between][0]=0&filter[where][price][between][1]=7`.

##### `like`/`nlike`

The like and nlike (not like) operators enable you to match SQL regular expressions.

[1]: https://github.com/dunglas/DunglasApiBundle

# License

[![License](https://img.shields.io/packagist/l/doctrine/orm.svg?style=flat-square)](https://github.com/theofidry/LoopBackApiBundle/edit/master/LICENSE)
