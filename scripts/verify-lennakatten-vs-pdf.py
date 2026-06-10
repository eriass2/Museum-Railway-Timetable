#!/usr/bin/env python3
"""Verify Lennakatten CSV fixture against Anslagstidtabell-2026.pdf."""

from __future__ import annotations

import csv
import sys
from pathlib import Path

from lennakatten_anslag_tables import is_synced_green_yellow_rail, pdf_service_definitions
from lennakatten_calendar import expected_green_buss_dates
from lennakatten_symbols import four_modes_from_flags, symbol_to_flags

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


def expected_flags(
    symbol: str,
    *,
    is_origin: bool,
    is_last: bool,
    station: str = "",
    service_code: str = "",
) -> tuple[str, str]:
    pickup, dropoff = symbol_to_flags(
        symbol,
        is_origin=is_origin,
        is_last=is_last,
        station=station,
        service_code=service_code,
    )
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
    total = len(expected_stops)
    for idx, (station, arrival, departure, symbol) in enumerate(expected_stops):
        if idx >= len(actual):
            break
        row = actual[idx]
        seq = idx + 1
        exp_arrival = "" if seq == 1 else arrival
        exp_departure = "" if seq == total else departure
        if row["station_code"] != station:
            errors.append(f"{service_code} #{seq}: station {row['station_code']!r} != {station!r}")
        if row["arrival_time"] != exp_arrival:
            errors.append(
                f"{service_code} #{seq} {station}: arrival {row['arrival_time']!r} != {exp_arrival!r}"
            )
        if row["departure_time"] != exp_departure:
            errors.append(
                f"{service_code} #{seq} {station}: departure {row['departure_time']!r} != {exp_departure!r}"
            )
        exp_pu, exp_do = symbol_to_flags(
            symbol,
            is_origin=(seq == 1),
            is_last=(seq == total),
            station=station,
            service_code=service_code,
        )
        has_time = bool(exp_arrival or exp_departure)
        exp_ank_pu, exp_ank_do, exp_avg_pu, exp_avg_do = four_modes_from_flags(
            exp_pu,
            exp_do,
            is_origin=(seq == 1),
            is_last=(seq == total),
            has_time=has_time,
        )
        for field, expected in (
            ("ank_pickup_mode", exp_ank_pu),
            ("ank_dropoff_mode", exp_ank_do),
            ("avg_pickup_mode", exp_avg_pu),
            ("avg_dropoff_mode", exp_avg_do),
        ):
            if row.get(field, "") != expected:
                errors.append(
                    f"{service_code} #{seq} {station}: {field} {row.get(field)!r} != {expected!r} "
                    f"(symbol {symbol!r})"
                )
    return errors


def load_timetable_dates() -> dict[str, list[str]]:
    path = FIXTURE / "timetable_dates.csv"
    by_code: dict[str, list[str]] = {}
    with path.open(encoding="utf-8-sig", newline="") as handle:
        for row in csv.DictReader(handle):
            by_code.setdefault(row["timetable_code"], []).append(row["date"])
    for dates in by_code.values():
        dates.sort()
    return by_code


def verify_green_buss_calendar(dates_by_code: dict[str, list[str]]) -> list[str]:
    expected = expected_green_buss_dates(
        dates_by_code.get("green", []),
        dates_by_code.get("green-vard", []),
    )
    actual = dates_by_code.get("green-buss", [])
    errors: list[str] = []
    if actual != expected:
        extra = sorted(set(actual) - set(expected))
        missing = sorted(set(expected) - set(actual))
        if missing:
            errors.append(f"green-buss missing dates: {', '.join(missing)}")
        if extra:
            errors.append(f"green-buss extra dates: {', '.join(extra)}")
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
    services = pdf_service_definitions()

    dates_by_code = load_timetable_dates()
    for service_code, _tt, _route, stops in services:
        if is_synced_green_yellow_rail(service_code):
            continue
        failures.extend(compare_service(service_code, stops, by_service.get(service_code)))
    failures.extend(verify_green_buss_calendar(dates_by_code))

    print(f"PDF present: {PDF.is_file()}  readable: {pdf_readable()}")
    print(f"Checked {len(services)} Anslagstidtabell services (RÖD/ORANGE rail + GRÖN/GUL bus; GRÖN/GUL rail via B-PDF)")
    print(
        f"Checked green-buss calendar: {len(dates_by_code.get('green-buss', []))} days "
        "(1/7-16/8 on green traffic days)"
    )

    if failures:
        print(f"\nFAILURES ({len(failures)}):")
        for line in failures[:80]:
            print(line)
        if len(failures) > 80:
            print(f"... and {len(failures) - 80} more")
        return 1

    print("All Anslagstidtabell-backed services match (bus + RÖD/ORANGE rail).")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
