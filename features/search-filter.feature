@whereFilter
Feature: Order filter on collections
  In order to search for specific data in large collections of resources
  As a client software developer
  I need to be able to filter on properties

  Scenario: Check fixtures
    Given the database is empty
    Given the fixtures "search-dummy-simple.yml" are loaded
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
    And the JSON node "hydra:member[2]->price" should be equal to 0
    And the JSON node "hydra:member[2]->relatedDummy" should be null
    And the JSON node "hydra:member[2]->relatedDummies" should have 0 element

    And the JSON node "hydra:member[3]->@id" should be equal to "/dummies/4"
    And the JSON node "hydra:member[3]->name" should be null
    And the JSON node "hydra:member[3]->dummyDate" should be equal to "2016-04-28T02:23:50+00:00"
    And the JSON node "hydra:member[3]->enabled" should be equal to the boolean false
    And the JSON node "hydra:member[3]->price" should be equal to 120
    And the JSON node "hydra:member[3]->relatedDummy" should be null
    And the JSON node "hydra:member[3]->relatedDummies" should have 0 element


  @firstLevel @noOperator @iriValue
  Scenario: First level search without operator on a string value
    When I send a "GET" request to "/dummies?filter[where][id]=/dummies/1"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"

    When I send a "GET" request to "/dummies?filter[where][id]=1"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"

    When I send a "GET" request to "/dummies?filter[where][id]=/dummies/99"
    Then the JSON node "hydra:totalItems" should be equal to 0

    When I send a "GET" request to "/dummies?filter[where][id]=invalidValue"
    Then the JSON node "hydra:totalItems" should be equal to 0


  @firstLevel @noOperator @stringValue
  Scenario: First level search without operator on a string value
    When I send a "GET" request to "/dummies?filter[where][name]=AAA"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[0]->name" should be equal to "AAA"

    When I send a "GET" request to "/dummies?filter[where][name]=A"
    Then the JSON node "hydra:totalItems" should be equal to 0

    When I send a "GET" request to "/dummies?filter[where][name]="
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[0]->name" should be equal to ""

    When I send a "GET" request to "/dummies?filter[where][name]=null"
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[0]->name" should be null


  @firstLevel @noOperator @booleanValue
  Scenario: First level search without operator on a boolean value
    When I send a "GET" request to "/dummies?filter[where][enabled]=0"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/4"
    And the JSON node "hydra:member[0]->enabled" should be equal to the boolean false

    When I send a "GET" request to "/dummies?filter[where][enabled]=1"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[0]->enabled" should be equal to the boolean true

    When I send a "GET" request to "/dummies?filter[where][enabled]="
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/4"
    And the JSON node "hydra:member[0]->enabled" should be equal to the boolean false

    When I send a "GET" request to "/dummies?filter[where][enabled]=null"
    Then the JSON node "hydra:totalItems" should be equal to 2
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[0]->enabled" should be null
    And the JSON node "hydra:member[1]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[1]->enabled" should be null


  @firstLevel @noOperator @numberValue
  Scenario: First level search without operator on a boolean value
    When I send a "GET" request to "/dummies?filter[where][price]=10.99"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[0]->price" should be equal to 10.99

    When I send a "GET" request to "/dummies?filter[where][price]=120"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/4"
    And the JSON node "hydra:member[0]->price" should be equal to 120

    When I send a "GET" request to "/dummies?filter[where][price]="
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[0]->price" should be equal to 0

    When I send a "GET" request to "/dummies?filter[where][price]=null"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/2"
    And the JSON node "hydra:member[0]->price" should be null


  @firstLevel @noOperator @dateValue
  Scenario: First level search without operator on a boolean value
    When I send a "GET" request to "/dummies?filter[where][dummyDate]=2015-04-28T02:23:50+00:00"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
    And the JSON node "hydra:member[0]->dummyDate" should be equal to "2015-04-28T02:23:50+00:00"

    When I send a "GET" request to "/dummies?filter[where][dummyDate]==2015-04-28"
    Then the JSON node "hydra:totalItems" should be equal to 0

    When I send a "GET" request to "/dummies?filter[where][dummyDate]="
    Then the JSON node "hydra:totalItems" should be equal to 0

    When I send a "GET" request to "/dummies?filter[where][dummyDate]=null"
    Then the JSON node "hydra:totalItems" should be equal to 1
    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/3"
    And the JSON node "hydra:member[0]->dummyDate" should be null

#  @firstLevel @regularSearch
#  Scenario:
#    Given the database is empty
#    Given the fixtures "search-dummy-simple.yml" are loaded
#    When I send a "GET" request to "/dummies?filter[where][name]=AAA"
#    Then the JSON node "hydra:totalItems" should be equal to 1
#    And the JSON node "hydra:member[0]->@id" should be equal to "/dummies/1"
#
#    When I send a "GET" request to "/dummies?filter[where][name]=A"
#    Then the JSON node "hydra:totalItems" should be equal to 0

#  Scenario:
#    Given the fixtures "search-dummy-simple.yml" are loaded
#    When I send a "GET" request to "/dummies?filter[where][relatedDummy.anotherDummy.id]=/api/90"
#    Then print last JSON response
