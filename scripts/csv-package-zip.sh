#!/usr/bin/env bash
# Wrapper — implementation in csv/csv-package-zip.sh
exec bash "$(dirname "$0")/csv/csv-package-zip.sh" "$@"
