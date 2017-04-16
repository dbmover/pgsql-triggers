# Dbmover\PgsqlTriggers
PostgreSQL-specific trigger (re)creation for DbMover

## Installation
```sh
$ composer require dbmover/pgsql-triggers
```

    This package is part of the `dbmover/pgsql` meta-package.

## Usage
See `dbmover/core` for general DbMover usage instructions.

## Notes
In PostgreSQL, a trigger is simply a reference to an existing function.
(Re)creation of the functions associated with your triggers is handled by the
`dbmover/pgsql-procedures` plugin (also included in `dbmover/pgsql`).

Or, to be explicit:

```sql
-- This is handled by `dbmover/pgsql-procedures`:
CREATE FUNCTION foo_after_insert() RETURNS "trigger" AS $$
BEGIN
    -- do stuff...
    RETURN NEW;
END;
$$ LANGUAGE 'plpgsql';

-- This is handled by the `dbmover/pgsql-triggers` plugin:
CREATE TRIGGER foo_after_insert AFTER INSERT ON foo FOR EACH ROW EXECUTE PROCEDURE foo_after_insert();
```

## Contributing
See `dbmover/core`.

