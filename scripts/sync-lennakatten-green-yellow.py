#!/usr/bin/env python3
"""Sync GRÖN/GUL stoptimes in Lennakatten fixture from Anslagstidtabell tables."""

from __future__ import annotations

import csv
import sys
from pathlib import Path

from lennakatten_anslag_tables import service_definitions
from lennakatten_symbols import symbol_to_flags

ROOT = Path(__file__).resolve().parents[1]
FIXTURE = ROOT / "testdata" / "fixtures" / "lennakatten"
STOPTIMES = FIXTURE / "stoptimes.csv"

PREFIXES = ("green-", "yellow-")


def stop_rows(service_code: str, stops: list[tuple[str, str, str, str]]) -> list[str]:
    rows: list[str] = []
    for seq, (station, arrival, departure, symbol) in enumerate(stops, start=1):
        pickup, dropoff = symbol_to_flags(
            symbol,
            is_origin=(seq == 1),
            is_last=(seq == len(stops)),
        )
        rows.append(
            f"{service_code},{seq},{station},{arrival},{departure},{pickup},{dropoff}"
        )
    return rows


def is_green_yellow_rail(code: str) -> bool:
    if not code.startswith(PREFIXES):
        return False
    return "-bus-" not in code and not code.startswith("green-vard-")


def main() -> int:
    if not STOPTIMES.is_file():
        print(f"Missing {STOPTIMES}", file=sys.stderr)
        return 1

    generated: dict[str, list[str]] = {}
    for service_code, _timetable, _route, stops in service_definitions():
        generated[service_code] = stop_rows(service_code, stops)

    lines = STOPTIMES.read_text(encoding="utf-8-sig").splitlines()
    header = lines[0]
    kept: list[str] = [header]
    replaced = 0

    for line in lines[1:]:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if is_green_yellow_rail(code):
            if code not in generated:
                kept.append(line)
            continue
        kept.append(line)

    for service_code in sorted(generated, key=_service_sort_key):
        kept.extend(generated[service_code])
        replaced += 1

    STOPTIMES.write_text("\n".join(kept) + "\n", encoding="utf-8")
    print(f"Synced {replaced} GRÖN/GUL rail services in {STOPTIMES.name}")
    return 0


def _service_sort_key(code: str) -> tuple[str, str]:
    parts = code.split("-")
    if len(parts) >= 3:
        return (parts[0], parts[2], parts[1])
    return (code, "", "")


if __name__ == "__main__":
    raise SystemExit(main())
