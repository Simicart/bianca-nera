#!/bin/bash

cd $( dirname "${BASH_SOURCE[0]}" )
echo "Running.."


if [ "$(pm2 list | grep -v grep | grep biancasiminia | grep online)" != "" ];
then
        echo "Process already running."
        exit 99
fi

if [ "$(pm2 list | grep -v grep | grep biancasiminia | grep stopped)" != "" ];
then
        /usr/bin/pm2 start biancasiminia
echo "1123"
        exit 99
fi

if [ "$(pm2 list | grep -v grep | grep biancasiminia | grep error)" != "" ];
then
        /usr/bin/pm2 start biancasiminia
echo "345"
        exit 99
fi

pm2 delete biancasiminia
NODE_ENV=production PORT=1402 pm2 start --name biancasiminia  npm -- run stage:siminia
