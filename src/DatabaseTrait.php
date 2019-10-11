<?php declare(strict_types=1);

namespace Mrself\PHPUnit;

use Doctrine\DBAL\Connection;

trait DatabaseTrait
{
    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function truncateTables()
    {
        /** @var Connection $conn */
        $conn = static::$container->get('doctrine.dbal.default_connection');
        $conn->getConfiguration()->setSQLLogger(null);
        $conn->prepare('set foreign_key_checks = 0')->execute();
        $tables = $conn->getSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            $conn->query('delete from ' . $table);
        }
        $conn->prepare('set foreign_key_checks = 1')->execute();
    }

    protected function getEntityManager()
    {
        return static::$container->get('doctrine.orm.entity_manager');
    }

    protected function assertDB(array $data, $depth = 0)
    {
        foreach ($data as $class => $entities) {
            $existingEntities = $this->getEntityManager()
                ->getRepository($class)
                ->findAll();
            if (count($entities) !== count($existingEntities)) {
                $this->fail('Count actual db entities (' . count($existingEntities) . ') does not meet the expected for class ' . $class . ' (' . count($entities) . ')');
            }
            if (count($entities) === 0) {
                $this->assertTrue(true, 'Database expected state matches actual one');
            }
            foreach ($entities as $index => $expected) {
                if (!isset($existingEntities[$index])) {
                    $this->fail('Database does not contain an expected entity');
                }
                $actual = EntitySerializer::toArray($existingEntities[$index], $depth);
                $this->assertExpectArray($expected, $actual);
            }
        }
    }

    protected function assertExpectArray(array $expected, array $actual)
    {
        foreach ($expected as $key => $expectedValue) {
            $actualValue = @$actual[$key];
            if (is_array($expectedValue)) {
                if (!is_array($actualValue)) {
                    $expectedValue = json_encode($expectedValue);
                    $actualValue = json_encode($actualValue);
                    $this->fail("Value of $key expected to be an '$expectedValue', got '$actualValue'");
                }
                $this->assertExpectArray($expectedValue, $actualValue);
            } else {
                $this->assertEquals($expectedValue, $actualValue, "Database key '$key' does not match expected'");
            }
        }
    }
}