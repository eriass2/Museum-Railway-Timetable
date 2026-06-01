#!/usr/bin/env python3
"""Sync GRÖN/GUL rail and connection buses in Lennakatten fixture from Anslagstidtabell."""

from __future__ import annotations

import sys
from pathlib import Path

from lennakatten_anslag_tables import service_definitions
from lennakatten_symbols import symbol_to_flags

ROOT = Path(__file__).resolve().parents[1]
FIXTURE = ROOT / "testdata" / "fixtures" / "lennakatten"
STOPTIMES = FIXTURE / "stoptimes.csv"

SYNC_PREFIXES = ("green-", "yellow-")


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


def is_synced_service(code: str, synced: set[str]) -> bool:
    return code in synced


def main() -> int:
    if not STOPTIMES.is_file():
        print(f"Missing {STOPTIMES}", file=sys.stderr)
        return 1

    generated: dict[str, list[str]] = {}
    for service_code, _timetable, _route, stops in service_definitions():
        generated[service_code] = stop_rows(service_code, stops)

    synced_codes = set(generated.keys())
    lines = STOPTIMES.read_text(encoding="utf-8-sig").splitlines()
    header = lines[0]
    kept: list[str] = [header]

    for line in lines[1:]:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if code.startswith(SYNC_PREFIXES) and is_synced_service(code, synced_codes):
            continue
        kept.append(line)

    for service_code in sorted(generated, key=_service_sort_key):
        kept.extend(generated[service_code])

    STOPTIMES.write_text("\n".join(kept) + "\n", encoding="utf-8")
    rail = sum(1 for c in generated if "-bus-" not in c)
    bus = sum(1 for c in generated if "-bus-" in c)
    print(f"Synced {rail} rail + {bus} bus services in {STOPTIMES.name}")
    return 0


def _service_sort_key(code: str) -> tuple[str, str]:
    parts = code.split("-")
    if len(parts) >= 3:
        return (parts[0], parts[2], parts[1])
    return (code, "", "")


if __name__ == "__main__":
    raise SystemExit(main())
