#!/bin/bash

GIT_TLD="$( git rev-parse --show-toplevel )" # Top dir of the git repo
VERSION="$( cat "$GIT_TLD/version" )"

cd "$GIT_TLD" || {
  echo "Failed to cd to $GIT_TLD"
  exit 1
}

ZIPF="$GIT_TLD/wcms-theme-adivvyo-$VERSION.zip"
echo "Making $ZIPF..."
zip -9r "$ZIPF" . --exclude '*.git*' --exclude 'scripts/*'

