#!/bin/bash
rm -R prod
docker build -t nginx-php-lo .