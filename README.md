# sleeplog

logging sleep and wakeup.

## implemented

* logging

## Want to implement

* view sleep statistics

## Setup

### Setup sqlite db file

```bash
sqlite3 /path/to/sleeplog.db
```

```sql
CREATE TABLE log(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(255),
    sleep_at TIMESTAMP KEY,
    wakeup_at TIMESTAMP KEY,
    created_at TIMESTAMP DEFAULT(DATETIME('now','localtime'))
);
```

### config php file

write config.php file like config.sample.php.
variable `$USERNAME` is not use now.

```php
/**
 * config
 */
// sqlite db filepath
$DBFILE = "/path/to/sleeplog.db";
```

## enjoy

with browser, open index.php!
