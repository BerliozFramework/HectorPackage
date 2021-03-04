# Hector ORM package for Berlioz Framework

This package is intended to provide **Hector ORM** in **Berlioz Framework**.

For more information, and use of Berlioz Framework, go to website and online documentation :
https://getberlioz.com

## Installation

### Composer

You can install **Hector Package** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/hector-package
```

### Dependencies

* **PHP** ^8.0
* Packages:
  * **berlioz/core**
  * **hectororm/orm**


## Usage

Package add a service named `hector`, who correspond to the `\Hector\Orm\Orm` class.

See [**Hector ORM** documentation](https://gethectororm.com/) for more information.


## Configuration

Default configuration:
```json
{
  "hector": {
    "dsn": null,
    "read_dsn": null,
    "schemas": []
  }
}
```