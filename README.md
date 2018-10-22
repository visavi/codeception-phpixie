# Codeception PHPixie module

### Installation
You can install this package through composer:

```
composer require visavi/codeception-phpixie
```

### Settings

Include the phpixie module in the `tests/functional.suite` file
```yaml
actor: FunctionalTester
modules:
    enabled:
        - Phpixie:
            url: 'http://localhost'
        - \Helper\Functional
```

In acceptance tests, you can use methods to work with a database of functional tests.

To do this, you must enable the module in the `tests/acceptance.suite` file
```yaml
actor: AcceptanceTester
modules:
    enabled:
        - PhpBrowser:
            url: http://localhost
        - Phpixie:
            part: ORM
        - \Helper\Acceptance
```

After that 7 methods will be available to work with the database.

### Methods for working with the DB

All methods work in a transaction, which means that after the tests are completed, the database will be in its original form

##### Inserts record into the database

```php
$user = $I->haveRecord('user', ['name' => 'phpixie']);
```
##### Checks that record exists in database.

```php
$I->seeRecord('user', ['name' => 'phpixie']);
```

##### Checks that record does not exist in database.

```php
$I->dontSeeRecord('user', ['name' => 'trixie']);
```

##### Retrieves record from database.

```php
$record = $I->grabRecord('user', ['name' => 'phpixie']);
```

##### Removes a record from the database.

```php
$I->deleteRecord('user', ['id' => $user->id]);
```

Transaction control methods do not look at the global transaction enable settings, if you have transactions enabled by default, you can turn them off for each method

Conversely, if transactions are globally disabled (cleanup: false), then methods can force transactions to be enabled.

```php
$I->startTransaction();
```

```php
$I->stopTransaction();
```

### Call methods

Calling methods for functional and acceptance tests:

 ```
$I->methodName();
```

Calling methods for unit tests:
 ```
$this->tester->methodName();
```

### Example

```php
$data = [
    'login'    => 'phpixie',
    'password' => 'password',
    'name'     => 'trixie',
];

$entity = $I->haveRecord('user', $data);

$I->seeRecord('user', ['id' => $entity->id]);

$I->wantTo('Test user page');
$I->amOnPage('/user/' . $entity->login);
$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);
$I->seeInSource('trixie');
```

### License

The class is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
