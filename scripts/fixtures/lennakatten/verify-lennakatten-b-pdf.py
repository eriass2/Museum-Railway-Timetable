#!/usr/bin/env python3
"""Verify Lennakatten GRÖN/GUL rail fixture against Tidtabellsboken-del-B.pdf."""

from __future__ import annotations

import csv
import sys
from pathlib import Path

from lennakatten_b_pdf import BStop, b_rail_service_stops, parse_b_pdf
from lennakatten_symbols import anslag_overlay_flags

ROOT = Path(__file__).resolve().parents[2]
FIXTURE = ROOT / "testdata" / "fixtures" / "lennakatten"
B_PDF = ROOT / "testdata" / "reference-pdfs" / "Tidtabellsboken-del-B.pdf"

MODE_FIELDS = (
    "ank_pickup_mode",
    "ank_dropoff_mode",
    "avg_pickup_mode",
    "avg_dropoff_mode",
)
OVERLAY_FIELDS = ("approximate_time", "in_service_timetable")


def load_stoptimes() -> dict[str, list[dict[str, str]]]:
    path = FIXTURE / "stoptimes.csv"
    by_service: dict[str, list[dict[str, str]]] = {}
    with path.open(encoding="utf-8-sig", newline="") as handle:
        for row in csv.DictReader(handle):
            by_service.setdefault(row["service_code"], []).append(row)
    for stops in by_service.values():
        stops.sort(key=lambda r: int(r["sequence"]))
    return by_service


def expected_overlay(
    service_code: str,
    seq: int,
    stop: BStop,
    *,
    total_stops: int,
) -> tuple[str, str]:
    arrival = "" if seq == 1 else stop.arrival
    departure = "" if seq == total_stops else stop.departure
    has_time = bool(arrival or departure)
    approx, in_svc = anslag_overlay_flags(
        in_b_korplan=stop.in_b_korplan,
        is_origin=(seq == 1),
        is_last=(seq == total_stops),
        has_time=has_time,
        has_b_time=stop.has_b_time,
        service_code=service_code,
    )
    return str(approx), str(in_svc)


def compare_stop(
    service_code: str,
    seq: int,
    expected: BStop,
    actual: dict[str, str],
    *,
    total_stops: int,
) -> list[str]:
    errors: list[str] = []
    if actual["station_code"] != expected.station_code:
        errors.append(
            f"{service_code} #{seq}: station {actual['station_code']!r} != {expected.station_code!r}"
        )
    exp_arrival = "" if seq == 1 else expected.arrival
    exp_departure = "" if seq == total_stops else expected.departure
    for field, value in (
        ("arrival_time", exp_arrival),
        ("departure_time", exp_departure),
    ):
        if actual.get(field, "") != value:
            errors.append(
                f"{service_code} #{seq} {expected.station_code}: {field} "
                f"{actual.get(field, '')!r} != {value!r}"
            )
    for field in MODE_FIELDS:
        if actual.get(field, "") != getattr(expected, field):
            errors.append(
                f"{service_code} #{seq} {expected.station_code}: {field} "
                f"{actual.get(field, '')!r} != {getattr(expected, field)!r}"
            )
    exp_approx, exp_in_svc = expected_overlay(service_code, seq, expected, total_stops=total_stops)
    for field, value in (("approximate_time", exp_approx), ("in_service_timetable", exp_in_svc)):
        if actual.get(field, "") != value:
            errors.append(
                f"{service_code} #{seq} {expected.station_code}: {field} "
                f"{actual.get(field, '')!r} != {value!r}"
            )
    return errors


def compare_service(
    service_code: str,
    expected_stops: list[BStop],
    actual: list[dict[str, str]] | None,
) -> list[str]:
    if actual is None:
        return [f"{service_code}: missing from stoptimes.csv"]
    errors: list[str] = []
    if len(actual) != len(expected_stops):
        errors.append(f"{service_code}: expected {len(expected_stops)} stops, got {len(actual)}")
    for idx, stop in enumerate(expected_stops):
        if idx >= len(actual):
            break
        errors.extend(compare_stop(service_code, idx + 1, stop, actual[idx], total_stops=len(expected_stops)))
    return errors


def verify_train_71_stickprov(stops: list[BStop]) -> list[str]:
    """Reference checks for GRÖN tur 71 (docs/STOP_TIME_SOURCES.md)."""
    errors: list[str] = []
    if len(stops) != 7:
        return [f"green-71-out stickprov: expected 7 stops, got {len(stops)}"]
    origin = stops[0]
    if origin.station_code != "uppsala-ostra" or origin.departure != "10:00":
        errors.append("green-71-out stickprov: origin departure 10:00")
    if origin.avg_pickup_mode != "scheduled":
        errors.append("green-71-out stickprov: origin scheduled departure")
    arsta = stops[2]
    if arsta.avg_pickup_mode != "on_request":
        errors.append("green-71-out stickprov: arsta on_request pickup")
    marielund = stops[-1]
    if marielund.arrival != "10:35" or marielund.departure != "":
        errors.append("green-71-out stickprov: marielund arrival 10:35, no departure")
    if not marielund.in_b_korplan:
        errors.append("green-71-out stickprov: marielund must be in B körplan")
    barby = stops[4]
    if barby.departure != "10:23":
        errors.append("green-71-out stickprov: barby departure 10:23 from anslag/B overlay")
    approx, in_svc = anslag_overlay_flags(
        in_b_korplan=barby.in_b_korplan,
        is_origin=False,
        is_last=False,
        has_time=True,
        has_b_time=barby.has_b_time,
        service_code="green-71-out",
    )
    if approx != 0 or in_svc != 1:
        errors.append("green-71-out stickprov: barby no Ca when B has clock times")
    skolsta = stops[3]
    if skolsta.arrival != "10:09" or skolsta.departure != "10:09":
        errors.append("green-71-out stickprov: skolsta 10:09 from anslag overlay")
    if skolsta.avg_pickup_mode != "on_request" or skolsta.avg_dropoff_mode != "on_request":
        errors.append("green-71-out stickprov: skolsta X modes from anslag")
    return errors


def pdf_readable() -> bool:
    if not B_PDF.is_file():
        return False
    try:
        from pypdf import PdfReader  # noqa: PLC0415

        text = PdfReader(str(B_PDF)).pages[0].extract_text() or ""
    except Exception:
        return False
    return "Tidtabellsboken" in text or "Tidtabell" in text


def main() -> int:
    if not FIXTURE.is_dir():
        print(f"Missing fixture: {FIXTURE}", file=sys.stderr)
        return 1

    by_train, directions = parse_b_pdf()
    expected_by_service = b_rail_service_stops(by_train, directions)
    by_service = load_stoptimes()
    failures: list[str] = []

    for service_code, expected_stops in sorted(expected_by_service.items()):
        failures.extend(compare_service(service_code, expected_stops, by_service.get(service_code)))

    failures.extend(verify_train_71_stickprov(expected_by_service.get("green-71-out", [])))

    print(f"B PDF present: {B_PDF.is_file()}  readable: {pdf_readable()}")
    print(f"Checked {len(expected_by_service)} GRÖN/GUL rail services against Tidtabellsboken del B")

    if failures:
        print(f"\nFAILURES ({len(failures)}):")
        for line in failures[:80]:
            print(line)
        if len(failures) > 80:
            print(f"... and {len(failures) - 80} more")
        return 1

    print("All GRÖN/GUL rail services match Tidtabellsboken del B.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
