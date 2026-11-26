#!/usr/bin/env bash
# Runs the bundled composer.phar. When a failure is caused by GitHub's API rate limit it explains how to get past the limit.
# Usage: scripts/composer.sh <composer arguments>

set -o pipefail

project_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
composer_output="$(mktemp)"
trap 'rm -f "$composer_output"' EXIT

composer_status=0
{
  "$project_dir/composer.phar" "$@" 2>&1 1>&3 3>&- | tee "$composer_output" >&2
  composer_status="${PIPESTATUS[0]}"
} 3>&1

if [ "$composer_status" -ne 0 ] && grep -qiE 'API rate limit|GitHub API limit \([0-9]+ calls/hr\) is exhausted' "$composer_output"; then
  cat >&2 <<'EXPLANATION'

GitHub rate limited the Composer install.
Composer downloads the package archives from api.github.com, and GitHub allows unauthenticated requests only 60 times an hour per IP address. CI runners and shared networks share addresses, so the limit is easy to hit.

To get past the limit:
  1. Wait for the reset time Composer printed above, then run the command again.
  2. Give Composer a GitHub token. A token for public repositories without any permissions is enough:
       COMPOSER_AUTH='{"github-oauth":{"github.com":"<token>"}}' ./scripts/composer.sh install
     or save it once for your user:
       ./composer.phar config --global github-oauth.github.com <token>
  3. In GitHub Actions, shivammathur/setup-php registers the workflow GITHUB_TOKEN for Composer automatically. Newer GitHub Actions tokens contain a "-", which the Composer validation in versions before 2.9.8 rejects. If the error mentions invalid token characters, update the bundled composer.phar to 2.9.8 or later.
EXPLANATION
fi

exit "$composer_status"
