#!/bin/bash

#
# Copyright (c) 2022-2025 Iomywiab/PN, Hamburg, Germany. All rights reserved
# File name: phpstan.sh
# Project: Converting
# Modified at: 22/07/2025, 23:39
# Modified by: pnehls
#

docker compose exec phpstan sh -c "php -d memory_limit=2G ./vendor/bin/phpstan analyse"
