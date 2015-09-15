# Introduction

This is a short introduction to JSON-LD, Hydra and a little bit of REST API.

The bundle is based on [DunglasApiBundle][1] to generate a beautiful JSON-LD REST API with Hydra markups, if you do not know what it is about, check the bundle documentation it provides all the necessary links :).

The API follow the standard REST API guidelines, the specifications described bellow are more specific to a Symfony API or the use of the [DunglasApiBundle][1] bundle.

## Contexts

If you make a request, you will find in the response a property `@context` with a URI. Contexts are an API endpoint where the properties of the entity are detailed.

For instance, if you have the following response:

```json
{
    "@context": "\/api\/contexts\/ConstraintViolationList",
    "@type": "ConstraintViolationList",
    ...
}
```

It means that if you request `/api/contexts/ConstraintViolationList`, you will get all the properties and methods of the entity `ConstraintViolationList`:

```
{
    "@context": {
        "@vocab":"http:\/\/localhost:8080\/api\/vocab#",
        "hydra":"http:\/\/www.w3.org\/ns\/hydra\/core#"
    }
}
```

What the ****?!! Hey no panic! See the `@vocab` property? It tells you that what we are looking for is not here but gives the path to it. So let's request `/api/vocab`:

```
{
    ...
    "hydra:supportedClass": [
        ...
        {
            "@id": "#ConstraintViolationList",
            "@type": "hydra:Class",
            "subClassOf": "hydra:Error",
            "hydra:title": "A constraint violation list",
            "hydra:supportedProperty": [
                {
                    "@type": "hydra:SupportedProperty",
                    "hydra:property": {
                        "@id": "#ConstraintViolationList\/violation",
                        "@type": "rdf:Property",
                        "rdfs:label": "violation",
                        "domain": "#ConstraintViolationList",
                        "range": "#ConstraintViolation"
                    },
                    "hydra:title": "violation",
                    "hydra:description": "The violations",
                    "hydra:readable": true,
                    "hydra:writable": false
                }
            ]
        },
        ...
    ]
    ...
}
```

From it we can see that the `ConstraintViolationList` has `ConstraintViolation` properties, which is itself describe in this `vocab` file. Tedious? Hell yeah! That's why you can use [HydraConsole](https://github.com/lanthaler/HydraConsole), it does the job for you!

Still, if you are looking for the details of a class, in our case, start to look in the `vocab` first. If you do not like it, you can try to look the phpDoc.

## Fields validation

When you try to update or create a resource, some constraints may be applied to the form. In this case, a `400` response is returned with a `ConstraintViolationList` entity. All violations are found in the `violations` property and each object are an instance of `ConstraintViolation` (cf. `vocab`).

[1]: https://github.com/dunglas/DunglasApiBundle

Previous chapter: [Install](../../README.md#documentation)<br />
Next chapter: [Order filter](order-filter.md)
