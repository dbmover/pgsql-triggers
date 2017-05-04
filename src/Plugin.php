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
    public $description = 'Dropping existing triggers...';

    public function __invoke(string $sql) : string
    {
        $tmp = md5(microtime(true));
        $database = $this->loader->getDatabase();
        $this->addOperation(<<<EOT
CREATE OR REPLACE FUNCTION strip_$tmp() RETURNS void AS $$ DECLARE
triggRecord RECORD;
BEGIN
    FOR triggRecord IN select DISTINCT trigger_name, event_object_table from information_schema.triggers where trigger_schema = 'public' AND trigger_catalog = '$database' LOOP
        EXECUTE 'DROP TRIGGER ' || triggRecord.trigger_name || ' ON ' || triggRecord.event_object_table || ';';
    END LOOP;
END;
$$ LANGUAGE plpgsql;
SELECT strip_$tmp();
DROP FUNCTION strip_$tmp();
EOT
        );
        if (preg_match_all("@^CREATE TRIGGER.*?\(\);$@ms", $sql, $triggers, PREG_SET_ORDER)) {
            foreach ($triggers as $trigger) {
                $this->defer($trigger[0]);
                $sql = str_replace($trigger[0], '', $sql);
            }
        }
        return $sql;
    }

    public function __destruct()
    {
        $this->description = 'Recreating triggers...';
        parent::__destruct();
    }
}

