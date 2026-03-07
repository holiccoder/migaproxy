#!/bin/bash

# Auto-commit script for tracking agent changes

# Check if there are any changes
if [[ -z $(git status --porcelain) ]]; then
    echo "No changes to commit"
    exit 0
fi

# Get current timestamp
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Create commit message
COMMIT_MSG="Auto-commit: Agent changes at $TIMESTAMP"

# Add all changes
git add .

# Commit with timestamp
git commit -m "$COMMIT_MSG"

echo "✅ Auto-commit completed: $COMMIT_MSG"

# Show status
git status