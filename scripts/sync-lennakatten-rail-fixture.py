#!/usr/bin/env python3
"""Sync GRÖN/GUL rail + bus fixture CSV from Anslagstidtabell tables."""

from __future__ import annotations

import sys
from pathlib import Path

from lennakatten_anslag_tables import (
    is_synced_green_yellow_rail,
    service_csv_rows,
    service_definitions,
    service_train_type_rows,
)
from lennakatten_b_pdf import b_rail_service_stops, b_stop_to_csv_row, parse_b_pdf
from lennakatten_green_vard import refresh_green_vard_lines
from lennakatten_symbols import STOPTIMES_CSV_HEADER, stoptime_csv_row

ROOT = Path(__file__).resolve().parents[1]
FIXTURE = ROOT / "testdata" / "fixtures" / "lennakatten"


def anslag_bus_stop_rows(service_code: str, stops: list[tuple[str, str, str, str]]) -> list[str]:
    total = len(stops)
    return [
        stoptime_csv_row(service_code, seq, station, arrival, departure, symbol, total_stops=total)
        for seq, (station, arrival, departure, symbol) in enumerate(stops, start=1)
    ]


def b_rail_stop_rows() -> list[str]:
    rows: list[str] = []
    by_train, directions = parse_b_pdf()
    services = b_rail_service_stops(by_train, directions)
    for service_code in sorted(services):
        stops = services[service_code]
        total = len(stops)
        for seq, stop in enumerate(stops, start=1):
            rows.append(b_stop_to_csv_row(service_code, seq, stop, total_stops=total))
    return rows


def synced_bus_codes() -> set[str]:
    return {code for code, _tt, _route, _stops in service_definitions() if "-bus-" in code}


def replace_synced_lines(existing: list[str], new_rows: list[str], *, header: str | None = None) -> list[str]:
    bus_codes = synced_bus_codes()
    kept = [header if header is not None else existing[0]]
    for line in existing[1:]:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if is_synced_green_yellow_rail(code) or code in bus_codes:
            continue
        kept.append(line)
    kept.extend(new_rows)
    return kept


def main() -> int:
    if not FIXTURE.is_dir():
        print(f"Missing fixture: {FIXTURE}", file=sys.stderr)
        return 1

    stoptime_rows: list[str] = b_rail_stop_rows()
    for service_code, _tt, _route, stops in service_definitions():
        if "-bus-" not in service_code:
            continue
        stoptime_rows.extend(anslag_bus_stop_rows(service_code, stops))

    services_path = FIXTURE / "services.csv"
    stoptimes_path = FIXTURE / "stoptimes.csv"
    train_types_path = FIXTURE / "service_train_types.csv"

    services_lines = replace_synced_lines(
        services_path.read_text(encoding="utf-8-sig").splitlines(),
        service_csv_rows(),
    )
    stoptimes_lines = replace_synced_lines(
        stoptimes_path.read_text(encoding="utf-8-sig").splitlines(),
        stoptime_rows,
        header=STOPTIMES_CSV_HEADER,
    )
    train_types_lines = replace_synced_lines(
        train_types_path.read_text(encoding="utf-8-sig").splitlines(),
        service_train_type_rows(),
    )
    services_lines, stoptimes_lines, train_types_lines = refresh_green_vard_lines(
        services_lines,
        stoptimes_lines,
        train_types_lines,
    )

    services_path.write_text("\n".join(services_lines) + "\n", encoding="utf-8")
    stoptimes_path.write_text("\n".join(stoptimes_lines) + "\n", encoding="utf-8")
    train_types_path.write_text("\n".join(train_types_lines) + "\n", encoding="utf-8")

    rail = sum(1 for c, _t, _r, _s in service_definitions() if "-bus-" not in c)
    bus = sum(1 for c, _t, _r, _s in service_definitions() if "-bus-" in c)
    vard = sum(1 for line in services_lines if line.startswith("green-vard-"))
    print(
        f"Synced {rail} rail + {bus} bus services in {FIXTURE.name}; "
        f"refreshed {vard} green-vard clones"
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
