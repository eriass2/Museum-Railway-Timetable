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
from lennakatten_symbols import STOPTIMES_CSV_HEADER, stoptime_csv_row

ROOT = Path(__file__).resolve().parents[1]
FIXTURE = ROOT / "testdata" / "fixtures" / "lennakatten"


def stop_rows(service_code: str, stops: list[tuple[str, str, str, str]]) -> list[str]:
    total = len(stops)
    return [
        stoptime_csv_row(service_code, seq, station, arrival, departure, symbol, total_stops=total)
        for seq, (station, arrival, departure, symbol) in enumerate(stops, start=1)
    ]


def replace_synced_lines(existing: list[str], new_rows: list[str], *, header: str | None = None) -> list[str]:
    kept = [header if header is not None else existing[0]]
    for line in existing[1:]:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if is_synced_green_yellow_rail(code) or (
            code.startswith("green-b") and "-bus-" in code
        ):
            continue
        kept.append(line)
    kept.extend(new_rows)
    return kept


def main() -> int:
    if not FIXTURE.is_dir():
        print(f"Missing fixture: {FIXTURE}", file=sys.stderr)
        return 1

    stoptime_rows: list[str] = []
    for service_code, _tt, _route, stops in service_definitions():
        stoptime_rows.extend(stop_rows(service_code, stops))

    services_path = FIXTURE / "services.csv"
    stoptimes_path = FIXTURE / "stoptimes.csv"
    train_types_path = FIXTURE / "service_train_types.csv"

    services_path.write_text(
        "\n".join(
            replace_synced_lines(
                services_path.read_text(encoding="utf-8-sig").splitlines(),
                service_csv_rows(),
            )
        )
        + "\n",
        encoding="utf-8",
    )
    stoptimes_path.write_text(
        "\n".join(
            replace_synced_lines(
                stoptimes_path.read_text(encoding="utf-8-sig").splitlines(),
                stoptime_rows,
                header=STOPTIMES_CSV_HEADER,
            )
        )
        + "\n",
        encoding="utf-8",
    )
    train_types_path.write_text(
        "\n".join(
            replace_synced_lines(
                train_types_path.read_text(encoding="utf-8-sig").splitlines(),
                service_train_type_rows(),
            )
        )
        + "\n",
        encoding="utf-8",
    )

    rail = sum(1 for c, _t, _r, _s in service_definitions() if "-bus-" not in c)
    bus = sum(1 for c, _t, _r, _s in service_definitions() if "-bus-" in c)
    print(f"Synced {rail} rail + {bus} bus services in {FIXTURE.name}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
