#!/bin/bash

# Define the repository path and branch
REPO_PATH="/home/user/htdocs/srv633900.hstgr.cloud"
BRANCH="dev"

# Navigate to the repository and pull changes
cd $REPO_PATH
git fetch origin $BRANCH
git reset --hard origin/$BRANCH

# Optional: Restart services if required
# e.g., systemctl restart apache2 or php-fpm
