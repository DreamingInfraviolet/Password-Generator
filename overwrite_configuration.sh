#!/bin/bash
set -x

echo "Overwriting database settings"

echo "<?php
      \$dbUsername = \"${SQL_USERNAME}\";
      \$dbHost = \"${SQL_HOST}\";
      \$dbDatabase = \"${SQL_DATABASE_NAME}\";
      \$dbPassword = \"${SQL_PASSWORD}\";
      ?>" > $1
