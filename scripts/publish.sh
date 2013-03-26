#!/bin/bash

set -eu

if [ -z "$1" ]
then
  echo "You must specify the version."
  echo "  ex: publish.sh 1.1.12"
  exit 1
fi

number=$1
version="v$number"
repository="swiftype-wordpress"
directory="/tmp/$repository-$version-release"
git_directory="$repository-$number"
svn_directory="$repository-svn"

mkdir -p $directory
cd $directory

echo -n "Downloading $repository $version from github..."
curl -sLO "https://github.com/swiftype/$repository/archive/$version.tar.gz"
echo " done"

echo -n "Uncompressing..."
tar -zxf "$version.tar.gz"
echo " done"

echo -n "Checking out $repository plugin from WordPress svn..."
svn co -q http://plugins.svn.wordpress.org/swiftype-search $svn_directory
echo " done"

echo -n "Copying current git state to svn trunk..."
rm -rf $svn_directory/trunk/*
cp -R $git_directory/* $svn_directory/trunk/
echo " done"

read -p "Are you sure you want to publish $version (y/n)? "
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
  echo "Exiting..."
  rm -rf $directory
  exit 1
fi

echo -n "Tagging $version in svn..."
cd $svn_directory
svn cp -q trunk tags/$number
svn ci -qm "bump version to $version"
echo " done"

echo -n "Cleaning up..."
rm -rf $directory
echo " done"
