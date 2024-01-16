<?php

namespace LapayGroup\DoctrineCockroach\Schema;

use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\DBAL\Schema\Sequence;

class CockroachSchemaManager extends PostgreSQLSchemaManager
{
    protected function _getPortableSequenceDefinition($sequence): Sequence
    {
        if ($sequence['schemaname'] !== 'public') {
            $sequenceName = $sequence['schemaname'] . '.' . $sequence['relname'];
        } else {
            $sequenceName = $sequence['relname'];
        }

        if (!isset($sequence['increment_by'], $sequence['min_value'])) {
            $sequence['min_value'] = 0;
            $sequence['increment_by'] = 0;

            /** @var string[] $data */
            $data = $this->_conn->fetchAssociative('SHOW CREATE ' . $this->_platform->quoteIdentifier($sequenceName));
            if (!empty($data['create_statement'])) {
                $matches = [];
                preg_match_all('/ -?\d+/', $data['create_statement'], $matches);
                if (!empty($matches[0])) {
                    $matches = array_map('trim', $matches[0]);
                    $sequence['min_value'] = $matches[0];
                    $sequence['increment_by'] = $matches[2];
                }
            }
        }

        return new Sequence($sequenceName, (int) $sequence['increment_by'], $sequence['min_value']);
    }

    protected function selectTableColumns(string $databaseName, ?string $tableName = null): Result
    {
        $sql = 'SELECT';

        if ($tableName === null) {
            $sql .= ' c.relname AS table_name, n.nspname AS schema_name,';
        }

        $sql .= <<<'SQL'
            a.attnum,
            quote_ident(a.attname) AS field,
            t.typname AS type,
            format_type(a.atttypid, a.atttypmod) AS complete_type,
            (SELECT tc.collcollate FROM pg_catalog.pg_collation tc WHERE tc.oid = a.attcollation) AS collation,
            (SELECT t1.typname FROM pg_catalog.pg_type t1 WHERE t1.oid = t.typbasetype) AS domain_type,
            (SELECT format_type(t2.typbasetype, t2.typtypmod) FROM
              pg_catalog.pg_type t2 WHERE t2.typtype = 'd' AND t2.oid = a.atttypid) AS domain_complete_type,
            a.attnotnull AS isnotnull,
            (SELECT 't'
             FROM pg_index
             WHERE c.oid = pg_index.indrelid
                AND pg_index.indkey[0] = a.attnum
                AND pg_index.indisprimary = 't'
            ) AS pri,
            (SELECT pg_get_expr(adbin, adrelid)
             FROM pg_attrdef
             WHERE c.oid = pg_attrdef.adrelid
                AND pg_attrdef.adnum=a.attnum
            ) AS default,
            (SELECT pg_description.description
                FROM pg_description WHERE pg_description.objoid = c.oid AND a.attnum = pg_description.objsubid
            ) AS comment
            FROM pg_attribute a
                INNER JOIN pg_class c
                    ON c.oid = a.attrelid
                INNER JOIN pg_type t
                    ON t.oid = a.atttypid
                INNER JOIN pg_namespace n
                    ON n.oid = c.relnamespace
                LEFT JOIN pg_depend d
                    ON d.objid = c.oid
                        AND d.deptype = 'e'
                        AND d.classid = (SELECT oid FROM pg_class WHERE relname = 'pg_class')
SQL;

        $conditions = array_merge([
            'a.attnum > 0',
            'a.attisdropped = FALSE',
            "c.relkind = 'r'",
            'd.refobjid IS NULL',
        ], $this->buildQueryConditions($tableName));

        $sql .= ' WHERE ' . implode(' AND ', $conditions) . ' ORDER BY a.attnum';

        return $this->_conn->executeQuery($sql);
    }

    /**
     * @return list<string>
     */
    private function buildQueryConditions(?string $tableName): array
    {
        $conditions = [];

        if ($tableName !== null) {
            if (strpos($tableName, '.') !== false) {
                [$schemaName, $tableName] = explode('.', $tableName);
                $conditions[]             = 'n.nspname = ' . $this->_platform->quoteStringLiteral($schemaName);
            } else {
                $conditions[] = 'n.nspname = ANY(current_schemas(false))';
            }

            $identifier   = new Identifier($tableName);
            $conditions[] = 'c.relname = ' . $this->_platform->quoteStringLiteral($identifier->getName());
        }

        $conditions[] = "n.nspname NOT IN ('pg_catalog', 'information_schema', 'pg_toast')";

        return $conditions;
    }
}
