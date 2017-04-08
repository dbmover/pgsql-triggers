<?php

/**
 * @package Dbmover
 * @subpackage Pgsql
 * @subpackage Triggers
 */

namespace Dbmover\Pgsql\Triggers;

use Dbmover\Core;

class Plugin extends Core\Plugin
{
    public function __invoke(string $sql) : string
    {
        $tmp = md5(microtime(true));
        $database = $this->loader->getDatabase();
        $this->loader->addOperation(<<<EOT
CREATE OR REPLACE FUNCTION strip_$tmp() RETURNS text AS $$ DECLARE
triggRecord RECORD;
BEGIN
    FOR triggRecord IN select distinct(trigger_name, event_object_table) from information_schema.triggers where trigger_schema = 'public' AND trigger_catalog = '$database' LOOP
        EXECUTE 'DROP TRIGGER ' || triggRecord.trigger_name || ' ON ' || triggRecord.event_object_table || ';';
    END LOOP;
    RETURN 'done';
END;
$$ LANGUAGE plpgsql;
SELECT strip_$tmp();
DROP FUNCTION strip_$tmp();
EOT
            ,
            '...stripping existing triggers...'
        );
        if (preg_match_all("@^CREATE TRIGGER.*?\(\);$@ms", $sql, $triggers, PREG_SET_ORDER)) {
            foreach ($triggers as $trigger) {
                $this->triggers[] = $trigger[0];
                $sql = str_replace($trigger[0], '', $sql);
            }
        }
        return $sql;
    }
}

