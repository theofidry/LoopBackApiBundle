@orderFilter
Feature: Order filter on collections
  In order to retrieve ordered large collections of resources
  As a client software developer
  I need to retrieve collections ordered properties

  Scenario: Check fixtures
    Given the database is empty
    Given the fixtures "order-dummy-simple.yml" are loaded
    When I send a GET request to "/dummies"
    And the response status code should be 200
    Then the response should be in JSON
    And the JSON node "hydra:totalItems" should be equal to 4

    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[0]->name" should be equal to "AAA"
    And the JSON node "hydra:member[0]->dummyDate" should be equal to "2015-04-28T02:23:50+00:00"
    And the JSON node "hydra:member[0]->enabled" should be equal to true
    And the JSON node "hydra:member[0]->price" should be equal to 10.99
    And the JSON node "hydra:member[0]->relatedDummy" should be null
    And the JSON node "hydra:member[0]->relatedDummies" should have 0 element

    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[1]->name" should be null
    And the JSON node "hydra:member[1]->dummyDate" should be equal to "2015-04-28T02:05:50+00:00"
    And the JSON node "hydra:member[1]->enabled" should be null
    And the JSON node "hydra:member[1]->price" should be null
    And the JSON node "hydra:member[1]->relatedDummy" should be null
    And the JSON node "hydra:member[1]->relatedDummies" should have 0 element

    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[2]->name" should be equal to ""
    And the JSON node "hydra:member[2]->dummyDate" should be null
    And the JSON node "hydra:member[2]->enabled" should be null
    And the JSON node "hydra:member[2]->price" should be null
    And the JSON node "hydra:member[2]->relatedDummy" should be null
    And the JSON node "hydra:member[2]->relatedDummies" should have 0 element

    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/4"
    And the JSON node "hydra:member[3]->name" should be equal to "ZZZ"
    And the JSON node "hydra:member[3]->dummyDate" should be equal to "2016-04-28T02:23:50+00:00"
    And the JSON node "hydra:member[3]->enabled" should be equal to the boolean false
    And the JSON node "hydra:member[3]->price" should be equal to 120
    And the JSON node "hydra:member[3]->relatedDummy" should be null
    And the JSON node "hydra:member[3]->relatedDummies" should have 0 element


  @firstLevel @iriValue
  Scenario Outline: First level order with an IRI with valid values; expect to be case insensitive to the value
    When I send a GET request to <url-asc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/4"

    When I send a GET request to <url-desc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/4"
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/1"
  Examples:
    | url-asc                          | url-desc                          |
    | "/dummies?filter[order][id]=asc" | "/dummies?filter[order][id]=desc" |
    | "/dummies?filter[order][id]=ASC" | "/dummies?filter[order][id]=DESC" |
    | "/dummies?filter[order][id]=aSc" | "/dummies?filter[order][id]=dESc" |

  @firstLevel @iriValue
  Scenario Outline: First level order with an IRI with invalid values
    When I send a GET request to <url>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/4"
  Examples:
    | url                                  |
    | "/dummies?filter[order][id]="        |
    | "/dummies?filter[order][id]=null"    |
    | "/dummies?filter[order][id]=unknown" |


  @firstLevel @booleanValue
  Scenario Outline: First level order with a boolean with valid values; expect to be case insensitive to the value
    When I send a GET request to <url-asc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->enabled" should be null
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[1]->enabled" should be null
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[2]->enabled" should be equal to the boolean false
    And the JSON node "hydra:member[3]->enabled" should be equal to the boolean true

    When I send a GET request to <url-desc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->enabled" should be equal to the boolean true
    And the JSON node "hydra:member[1]->enabled" should be equal to the boolean false
    And the JSON node "hydra:member[2]->enabled" should be null
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[3]->enabled" should be null
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/3"
  Examples:
    | url-asc                               | url-desc                               |
    | "/dummies?filter[order][enabled]=asc" | "/dummies?filter[order][enabled]=desc" |
    | "/dummies?filter[order][enabled]=ASC" | "/dummies?filter[order][enabled]=DESC" |
    | "/dummies?filter[order][enabled]=aSc" | "/dummies?filter[order][enabled]=dESc" |

  @firstLevel @booleanValue
  Scenario Outline: First level order with a boolean with invalid values
    When I send a GET request to <url>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/4"
  Examples:
    | url                                       |
    | "/dummies?filter[order][enabled]="        |
    | "/dummies?filter[order][enabled]=null"    |
    | "/dummies?filter[order][enabled]=unknown" |


  @firstLevel @numericalValue
  Scenario Outline: First level order with a numerical with valid values; expect to be case insensitive to the value
    When I send a GET request to <url-asc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->price" should be null
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[1]->price" should be null
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[2]->price" should be equal to the boolean 10.99
    And the JSON node "hydra:member[3]->price" should be equal to the boolean 120

    When I send a GET request to <url-desc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->price" should be equal to the boolean 120
    And the JSON node "hydra:member[1]->price" should be equal to the boolean 10.99
    And the JSON node "hydra:member[2]->price" should be null
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[3]->price" should be null
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/3"
  Examples:
    | url-asc                             | url-desc                             |
    | "/dummies?filter[order][price]=asc" | "/dummies?filter[order][price]=desc" |
    | "/dummies?filter[order][price]=ASC" | "/dummies?filter[order][price]=DESC" |
    | "/dummies?filter[order][price]=aSc" | "/dummies?filter[order][price]=dESc" |

  @firstLevel @numericalValue
  Scenario Outline: First level order with a numerical with invalid values
    When I send a GET request to <url>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/4"
  Examples:
    | url                                     |
    | "/dummies?filter[order][price]="        |
    | "/dummies?filter[order][price]=null"    |
    | "/dummies?filter[order][price]=unknown" |


  @firstLevel @stringValue
  Scenario Outline: First level order with a string with valid values; expect to be case insensitive to the value
    When I send a GET request to <url-asc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->name" should be null
    And the JSON node "hydra:member[1]->name" should be equal to ""
    And the JSON node "hydra:member[2]->name" should be equal to "AAA"
    And the JSON node "hydra:member[3]->name" should be equal to "ZZZ"

    When I send a GET request to <url-desc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->name" should be equal to "ZZZ"
    And the JSON node "hydra:member[1]->name" should be equal to "AAA"
    And the JSON node "hydra:member[2]->name" should be equal to ""
    And the JSON node "hydra:member[3]->name" should be null
  Examples:
    | url-asc                            | url-desc                            |
    | "/dummies?filter[order][name]=asc" | "/dummies?filter[order][name]=desc" |
    | "/dummies?filter[order][name]=ASC" | "/dummies?filter[order][name]=DESC" |
    | "/dummies?filter[order][name]=aSc" | "/dummies?filter[order][name]=dESc" |

  @firstLevel @stringValue
  Scenario Outline: First level order with a string with invalid values
    When I send a GET request to <url>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/4"
  Examples:
    | url                                    |
    | "/dummies?filter[order][name]="        |
    | "/dummies?filter[order][name]=null"    |
    | "/dummies?filter[order][name]=unknown" |


  @firstLevel @dateValue
  Scenario Outline: First level order with a string with valid values; expect to be case insensitive to the value
    When I send a GET request to <url-asc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->dummyDate" should be null
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[1]->dummyDate" should be equal to "2015-04-28T02:05:50+00:00"
    And the JSON node "hydra:member[2]->dummyDate" should be equal to "2015-04-28T02:23:50+00:00"
    And the JSON node "hydra:member[3]->dummyDate" should be equal to "2016-04-28T02:23:50+00:00"

    When I send a GET request to <url-desc>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->dummyDate" should be equal to "2016-04-28T02:23:50+00:00"
    And the JSON node "hydra:member[1]->dummyDate" should be equal to "2015-04-28T02:23:50+00:00"
    And the JSON node "hydra:member[2]->dummyDate" should be equal to "2015-04-28T02:05:50+00:00"
    And the JSON node "hydra:member[3]->dummyDate" should be null
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/3"
  Examples:
    | url-asc                                 | url-desc                                 |
    | "/dummies?filter[order][dummyDate]=asc" | "/dummies?filter[order][dummyDate]=desc" |
    | "/dummies?filter[order][dummyDate]=ASC" | "/dummies?filter[order][dummyDate]=DESC" |
    | "/dummies?filter[order][dummyDate]=aSc" | "/dummies?filter[order][dummyDate]=dESc" |

  @firstLevel @dateValue
  Scenario Outline: First level order with a string with invalid values
    When I send a GET request to <url>
    And the response status code should be 200
    And the response should be in JSON
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[2]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/4"
  Examples:
    | url                                         |
    | "/dummies?filter[order][dummyDate]="        |
    | "/dummies?filter[order][dummyDate]=null"    |
    | "/dummies?filter[order][dummyDate]=unknown" |
