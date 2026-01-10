#!/bin/bash

# Check for staged changes
if ! git diff --cached --quiet; then
    echo "WARNING: There are staged changes ready to commit."
else
    echo "No staged changes found."
fi

# Also simpler to warn if there are UNstaged changes too, just in case
if ! git diff --quiet; then
    echo "WARNING: There are unstaged changes in the working directory."
else
    echo "No unstaged changes found."
fi


GIT_TLD="$( git rev-parse --show-toplevel )" # Top dir of the git repo
VERSION="$( cat "$GIT_TLD/version" )"

if [ -z "$VERSION" ]; then
    echo "ERROR: Could not get version string."
    exit 1
fi

VER_TAG="$VERSION"

echo "Sitting at version: $VERSION ($VER_TAG)"
echo
echo "# Commands to copy/paste to make the release in git:"
echo "git tag -a '$VER_TAG' -m 'Release version $VERSION'"
echo "git push origin '$VER_TAG'"
echo "# And then to see all tags:"
echo "git tag"

