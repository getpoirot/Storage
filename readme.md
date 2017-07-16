# Storage 

## InMemory Store

```php
// Storage for 'test' Realm.

$s = new InMemoryStore('test');
$s->set('name', 'Payam');
$s->set('family', 'Naderi');

$fullname = $s->getFromKeys(['name', 'family']);
$fullname = StdTravers::of($fullname)
    ->each(function($val, $_ ,$prev) {
        return $prev.= ' '.$val;
    });

print_r($fullname);
```

## FlatFile Store

```php
$s = new FlatFileStore('user_codes');
        
$user_id = 'd5e110k33f';
$s->set($user_id, '1453');
// Without this syntax storage will save given entity when dispatch shutdown.
$s->save(); // force to save current 
```

> In Next Calls

values can be retrieved.

```php
$s = new FlatFileStore('user_codes');
if ($s->has('d5e110k33f'))
    print_r($s->get('d5e110k33f'));
```

> Destroy Storage

will destroy storage for given 'user_codes' realm.

```php
$s = new FlatFileStore('user_codes');
$s->destroy();
```

> Choose Desired Options

Options In under_score with construct

```php
$s = new FlatFileStore('user_codes', ['dir_path' => __DIR__.'/my_user_codes.dat']);
$s->set('key', 'value');
```

will map to setter methods

```php
$s = new FlatFileStore('user_codes');
$s->setPathDir(__DIR__.'/my_user_codes.dat');
$s->set('key', 'value');
```

## Mongo Store

```php
$client     = \Module\MongoDriver\Actions::Driver()->getClient('master');
$collection = $client->selectCollection('papioniha', 'store.app');

$s = new MongoStore('pass_trough', ['collection' => $collection]);

// Traverse All Data

$v = StdTravers::of( $s )
    ->each(function($val, $key ,$prev) {
        if ( is_array($prev) )
            return $prev[$key] = $val;

        return [$key => $val];
    });

print_r($v);
```