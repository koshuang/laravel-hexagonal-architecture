#!/usr/bin/env bash
# NOTE: Sometimes when running php-cs-fixer again, it will fix some files again.
#       So to try to run php-cs-fixer again unless you make sure there is no changes anymore.

set -euo pipefail

FIX_TYPE=${1:-}
PHP_CS_FIXER=(vendor/bin/php-cs-fixer fix --diff --config=.php-cs-fixer.php)
PHP_CodeSniffer=(vendor/bin/phpcbf)

if [[ "${FIX_TYPE}" == "all" ]]; then
  rm -rf .php-cs-fixer.cache
  "${PHP_CS_FIXER[@]}"
  "${PHP_CodeSniffer[@]}"
elif [[ "${FIX_TYPE}" == "pr" ]]; then
  files=()
  while IFS= read -r file; do
    files+=("${file}")
  done < <(git diff --name-only --diff-filter=ACMR origin/main...HEAD -- '*.php')

  if ((${#files[@]} > 0)); then
    "${PHP_CS_FIXER[@]}" --path-mode=intersection -- "${files[@]}"
    "${PHP_CodeSniffer[@]}" "${files[@]}"
  else
    echo "No PHP files changed."
  fi
else
  printf 'Usage: %s <all|pr>\n' "$0" >&2
  exit 2
fi
