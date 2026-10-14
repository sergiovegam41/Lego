<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Helper functions for database migrations
 * These functions provide common functionality for PostgreSQL migrations
 */

/**
 * Create or replace the update_updated_at_column() function in PostgreSQL
 * This function is used by triggers to automatically update the updated_at column
 */
function createUpdateTimestampFunction(): void
{
    Capsule::statement("
        CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS $$
        BEGIN
            NEW.updated_at = NOW();
            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
    ");
}

/**
 * Create a trigger to automatically update the updated_at column
 * 
 * @param string $tableName The name of the table
 */
function createUpdateTimestampTrigger(string $tableName): void
{
    $triggerName = "update_{$tableName}_updated_at";
    
    Capsule::statement("
        CREATE TRIGGER {$triggerName}
        BEFORE UPDATE ON {$tableName}
        FOR EACH ROW
        EXECUTE FUNCTION update_updated_at_column();
    ");
}

/**
 * Drop a trigger for updating the updated_at column
 * 
 * @param string $tableName The name of the table
 */
function dropUpdateTimestampTrigger(string $tableName): void
{
    $triggerName = "update_{$tableName}_updated_at";
    
    Capsule::statement("DROP TRIGGER IF EXISTS {$triggerName} ON {$tableName};");
}

/**
 * Check if a table exists in the database
 * 
 * @param string $tableName The name of the table
 * @return bool
 */
function tableExists(string $tableName): bool
{
    $result = Capsule::select("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = ?
        ) as exists
    ", [$tableName]);
    
    return $result[0]->exists ?? false;
}

/**
 * Check if a column exists in a table
 * 
 * @param string $tableName The name of the table
 * @param string $columnName The name of the column
 * @return bool
 */
function columnExists(string $tableName, string $columnName): bool
{
    $result = Capsule::select("
        SELECT EXISTS (
            SELECT FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = ? 
            AND column_name = ?
        ) as exists
    ", [$tableName, $columnName]);
    
    return $result[0]->exists ?? false;
}
