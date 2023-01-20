#!/bin/bash

symfony server:start -d
symfony run -d --watch=config,src,templates,vendor symfony console messenger:consume async -vv
symfony run -d npm run watch
