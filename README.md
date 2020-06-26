
# Aplorm Collection

[![Build Status](https://travis-ci.com/aplorm/collection.svg?branch=master)](https://travis-ci.com/aplorm/collection)

Collection abstraction library for Aplorm.

Collection work with php7.4 `WeakReference` object.

## php7.4 WeakMap implementation

with php8 `WeakMap` this [RFC](https://wiki.php.net/rfc/weak_maps) create a WeakMap object that watch reference and remove the reference
when the original object is destroy.<br />
But before PHP8 as Nikita Popov say :

```
Weak maps require first-class language support and cannot be implemented using existing functionality provided by PHP.

At first sight, it may seem that an array mapping from spl_object_id() to arbitrary values could serve the purpose of a weak map. This is not the case for multiple reasons:

- spl_object_id() values are reused after the object is destroyed. Two different objects can have the same object ID – just not at the same time.
- The object ID cannot be converted back into an object, so iteration over the map is not possible.
- The value stored under the ID will not be released when the object is destroyed.

Using the WeakReference class introduced in PHP 7.4, it is possible to avoid the first two issues, by using the following construction:
```

He gives this example :
```php
// Insertion
$this->map[spl_object_id($object)] = [WeakReference::create($object), $data];

// Lookup
$id = spl_object_id($object);
if (isset($this->map[$id])) {
    [$weakRef, $data] = $this->map[$id];
    if ($weakRef->get() === $object) {
        return $data;
    }
    // This entry belongs to a destroyed object.
    unset($this->map[$id]);
}
return null;
```

But this package give a parade for the third and last point.
