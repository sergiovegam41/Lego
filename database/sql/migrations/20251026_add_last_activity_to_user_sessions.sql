-- Migration: Add last_activity_at to auth_user_sessions
-- Date: 2025-10-26
-- Description: Adds last_activity_at column to track user activity for token extension

ALTER TABLE auth_user_sessions
ADD COLUMN IF NOT EXISTS last_activity_at TIMESTAMP DEFAULT NOW();

-- Update existing records to set last_activity_at to current time
UPDATE auth_user_sessions
SET last_activity_at = COALESCE(updated_at, created_at, NOW())
WHERE last_activity_at IS NULL;

-- Add comment to document the column purpose
COMMENT ON COLUMN auth_user_sessions.last_activity_at IS 'Tracks the last user activity to enable automatic token extension';
