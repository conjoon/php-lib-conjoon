## Registering a new Resource Endpoint

## 1. Create a ResourceDescription for the entity
Create a new class that extends `Conjoon\Data\Resource\ResourceDescription` and override `getType()` so that it returns the `type` of the represented entity at this resource location.

## 2. Add a new URI-QueryValidator 
For the new endpoint, a `QueryValidator` extending `Conjoon\Web\Validation\QueryValidator` (respective `CollectionQueryValidator`) should be made available. The folder `MailClient/JsonApi/Query` already contains a few validators, so that should be a good starting point for getting to know existing validation behavior and how to configure validators.

## 3. Create a new RepositoryQuery 
A `RepositoryQuery` (package `Conjoon\Data\Resource`) is required for "translating" (validated) URI-Requests to queries that operate on **Repositories**, i.e. local or centralized data storages. A `RepositoryQuery` is usually constructed from the `ParameterBag` of an `HttpQuery` (a JsonAPI-Query, i.e., `Conjoon\JsonApi\Query\Query`, in this case). Data within the ParameterBag can be validated using the QueryValidator configured in step 2 for safely passing the data to the RepositoryQuery.
Existing RepositoryQuery-classes can be found in `MainClient\Data\Resource`.

## 4. Register the resource's URI with RequestMatcher
In `Conjoon\MailCient\JsonApi\RequestMatcher`, a constant `TEMPLATES` holds all mappings between `ResourceDescriptions` and their uri, i.e., the endpoints your API provides for accessing the described resources. 
The constant `VALIDATORS` provides a mapping for all the queryable resources amd their validation classes.
Add a new entry for your `ResourceDescription` (Step 1) to `TEMPLATES` and a new entry for your `QueryValidator` (Step 2). 

## 5. Update the ResourceResolver with a resolveTo* method
In this step, the `Conjoon\MailCient\JsonApi\ResourceResolver` has to be updated with a method that resolves to a requested Resource.
Add a new command to  `resolveToResource` that checks the type of the requested resource represented by the `ResourceDescription`, the delegates to a method for further locating and returning the requested data as a `Conjoon\JsonApi\Resource\Resource`.