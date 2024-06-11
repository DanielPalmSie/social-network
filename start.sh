#!/bin/sh
export CURRENT_UID=$(id -u)
export CURRENT_GID=$(id -g)
docker-compose up -d
