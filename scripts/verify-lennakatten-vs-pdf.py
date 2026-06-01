#!/usr/bin/env python3
"""Verify Lennakatten CSV fixture against Anslagstidtabell-2026.pdf."""

from __future__ import annotations

import csv
import sys
from pathlib import Path

from lennakatten_anslag_tables import service_definitions
from lennakatten_symbols import symbol_to_flags

ROOT = Path(__file__).resolve().parents[1]
FIXTURE = ROOT / "testdata" / "fixtures" / "lennakatten"
PDF = ROOT / "testdata" / "reference-pdfs" / "Anslagstidtabell-2026.pdf"


def load_stoptimes() -> dict[str, list[dict[str, str]]]:
    path = FIXTURE / "stoptimes.csv"
    by_service: dict[str, list[dict[str, str]]] = {}
    with path.open(encoding="utf-8-sig", newline="") as handle:
        for row in csv.DictReader(handle):
            by_service.setdefault(row["service_code"], []).append(row)
    for stops in by_service.values():
        stops.sort(key=lambda r: int(r["sequence"]))
    return by_service


def expected_flags(symbol: str, *, is_origin: bool, is_last: bool) -> tuple[str, str]:
    pickup, dropoff = symbol_to_flags(symbol, is_origin=is_origin, is_last=is_last)
    return str(pickup), str(dropoff)


def compare_service(
    service_code: str,
    expected_stops: list[tuple[str, str, str, str]],
    actual: list[dict[str, str]] | None,
) -> list[str]:
    errors: list[str] = []
    if actual is None:
        return [f"{service_code}: missing from stoptimes.csv"]
    if len(actual) != len(expected_stops):
        errors.append(f"{service_code}: expected {len(expected_stops)} stops, got {len(actual)}")
    for idx, (station, arrival, departure, symbol) in enumerate(expected_stops):
        if idx >= len(actual):
            break
        row = actual[idx]
        seq = idx + 1
        if row["station_code"] != station:
            errors.append(f"{service_code} #{seq}: station {row['station_code']!r} != {station!r}")
        if row["arrival_time"] != arrival:
            errors.append(f"{service_code} #{seq} {station}: arrival {row['arrival_time']!r} != {arrival!r}")
        if row["departure_time"] != departure:
            errors.append(
                f"{service_code} #{seq} {station}: departure {row['departure_time']!r} != {departure!r}"
            )
        exp_pu, exp_do = expected_flags(
            symbol,
            is_origin=(seq == 1),
            is_last=(seq == len(expected_stops)),
        )
        if row["pickup_allowed"] != exp_pu or row["dropoff_allowed"] != exp_do:
            errors.append(
                f"{service_code} #{seq} {station}: flags {row['pickup_allowed']}/{row['dropoff_allowed']} "
                f"!= {exp_pu}/{exp_do} (symbol {symbol!r})"
            )
    return errors


def pdf_readable() -> bool:
    if not PDF.is_file():
        return False
    try:
        from pypdf import PdfReader  # noqa: PLC0415

        text = PdfReader(str(PDF)).pages[0].extract_text() or ""
    except Exception:
        return False
    return "Tidtabell" in text or "Tidtabell" in text.replace("�", "")


def main() -> int:
    if not FIXTURE.is_dir():
        print(f"Missing fixture: {FIXTURE}", file=sys.stderr)
        return 1

    by_service = load_stoptimes()
    failures: list[str] = []
    services = service_definitions()

    for service_code, _tt, _route, stops in services:
        failures.extend(compare_service(service_code, stops, by_service.get(service_code)))

    print(f"PDF present: {PDF.is_file()}  readable: {pdf_readable()}")
    print(f"Checked {len(services)} GRÖN/GUL rail and connection bus services against Anslagstidtabell")

    if failures:
        print(f"\nFAILURES ({len(failures)}):")
        for line in failures[:80]:
            print(line)
        if len(failures) > 80:
            print(f"... and {len(failures) - 80} more")
        return 1

    print("All GRÖN/GUL rail and bus services match Anslagstidtabell.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
