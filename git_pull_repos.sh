#!/bin/bash

# Reset and pull lastest code
git reset --hard HEAD
git pull

# Pull siminia
if [ !-d ./siminia ]; then
    git clone -b customize/2980-basmajiramez@gmail.com-Bianca-Nera https://github.com/Simicart/siminia
else
    pushd ./siminia
    git checkout customize/2980-basmajiramez@gmail.com-Bianca-Nera
    git reset --hard HEAD
    git pull
    popd
fi

# Pull server
if [ !-d ./Server-Customization ]; then
    git clone -b 2980-basmajiramez@gmail.com-Bianca-Nera https://github.com/Simicart/Server-Customization
else
    pushd ./Server-Customization
    git checkout 2980-basmajiramez@gmail.com-Bianca-Nera
    git reset --hard HEAD
    git pull
    popd
fi

commitMessage=''

# Copy siminia
if [ -d ./siminia ]; then
    pushd ./siminia
    commitMessage=$( git log -n 1 --format=%B )
    popd
    rm -rf ./reactjs-pwa/packages/siminia
    cp -rpf ./siminia ./reactjs-pwa/packages/siminia
else
    echo "No source ./siminia"
fi

# Copy server
if [ -d ./Server-Customization ]; then
    pushd ./Server-Customization
    commitMessage2=$( git log -n 1 --format=%B )
    commitMessage="$commitMessage, $commitMessage2"
    popd
    rm -rf ./magento/app/code
    cp -rpf ./Server-Customization/app/code ./magento/app/code
else
    echo "No source ./Server-Customization"
fi

# Commit code
git status
git add .
git commit -m "$commitMessage"
