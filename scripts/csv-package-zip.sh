#!/usr/bin/env bash
# Wrapper — implementation in csv/csv-package-zip.sh
exec "$(dirname "$0")/csv/csv-package-zip.sh" "$@"
