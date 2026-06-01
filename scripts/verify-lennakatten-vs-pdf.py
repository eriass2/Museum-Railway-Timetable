#!/usr/bin/env python3
"""Verify Lennakatten CSV fixture against Anslagstidtabell-2026.pdf reference trips."""

from __future__ import annotations

import csv
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
FIXTURE = ROOT / "testdata" / "fixtures" / "lennakatten"
PDF = ROOT / "testdata" / "reference-pdfs" / "Anslagstidtabell-2026.pdf"

# Reference trips from the poster (times + searchable connection).
REFERENCE_TRIPS = [
    {
        "label": "GRÖN lördag tåg 71 Uppsala Östra → Marielund",
        "service_code": "green-71-out",
        "from_station": "uppsala-ostra",
        "to_station": "marielund",
        "expect_departure": "10:00",
        "expect_arrival": "10:35",
        "require_dropoff_at_dest": True,
    },
    {
        "label": "RÖD söndag tåg 81 Uppsala Östra → Marielund",
        "service_code": "red-81-out",
        "from_station": "uppsala-ostra",
        "to_station": "marielund",
        "expect_departure": "10:00",
        "expect_arrival": "10:35",
        "require_dropoff_at_dest": True,
    },
    {
        "label": "ORANGE fredag tåg 73 Uppsala Östra → Marielund",
        "service_code": "orange-73-out",
        "from_station": "uppsala-ostra",
        "to_station": "marielund",
        "expect_departure": "11:15",
        "expect_arrival": "11:47",
        "require_dropoff_at_dest": True,
    },
]


def load_stoptimes() -> dict[str, list[dict[str, str]]]:
    path = FIXTURE / "stoptimes.csv"
    by_service: dict[str, list[dict[str, str]]] = {}
    with path.open(encoding="utf-8-sig", newline="") as handle:
        for row in csv.DictReader(handle):
            by_service.setdefault(row["service_code"], []).append(row)
    for stops in by_service.values():
        stops.sort(key=lambda r: int(r["sequence"]))
    return by_service


def stop_row(stops: list[dict[str, str]], station_code: str) -> dict[str, str] | None:
    for row in stops:
        if row["station_code"] == station_code:
            return row
    return None


def can_connect(stops: list[dict[str, str]], from_code: str, to_code: str) -> bool:
    from_row = stop_row(stops, from_code)
    to_row = stop_row(stops, to_code)
    if from_row is None or to_row is None:
        return False
    if int(from_row["sequence"]) >= int(to_row["sequence"]):
        return False
    return from_row["pickup_allowed"] == "1" and to_row["dropoff_allowed"] == "1"


def verify_trip(stops: list[dict[str, str]], trip: dict) -> list[str]:
    errors: list[str] = []
    from_row = stop_row(stops, trip["from_station"])
    to_row = stop_row(stops, trip["to_station"])
    if from_row is None:
        errors.append(f"missing origin stop {trip['from_station']}")
        return errors
    if to_row is None:
        errors.append(f"missing destination stop {trip['to_station']}")
        return errors

    dep = from_row["departure_time"] or from_row["arrival_time"]
    arr = to_row["arrival_time"] or to_row["departure_time"]
    if dep != trip["expect_departure"]:
        errors.append(f"departure {dep!r} != expected {trip['expect_departure']!r}")
    if arr != trip["expect_arrival"]:
        errors.append(f"arrival {arr!r} != expected {trip['expect_arrival']!r}")

    if trip.get("require_dropoff_at_dest") and to_row["dropoff_allowed"] != "1":
        errors.append(f"dropoff_allowed=0 at {trip['to_station']} (blocks journey search)")

    if from_row["pickup_allowed"] != "1":
        errors.append(f"pickup_allowed=0 at {trip['from_station']} (blocks journey search)")

    if not can_connect(stops, trip["from_station"], trip["to_station"]):
        errors.append("connection not searchable with pickup/dropoff flags")

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

    for trip in REFERENCE_TRIPS:
        code = trip["service_code"]
        stops = by_service.get(code)
        if stops is None:
            failures.append(f"{trip['label']}: service {code} not in stoptimes.csv")
            continue
        trip_errors = verify_trip(stops, trip)
        if trip_errors:
            failures.append(f"{trip['label']}:")
            failures.extend(f"  - {err}" for err in trip_errors)

    print(f"PDF present: {PDF.is_file()}  readable: {pdf_readable()}")
    print(f"Checked {len(REFERENCE_TRIPS)} reference trips from Anslagstidtabell")

    if failures:
        print("\nFAILURES:")
        for line in failures:
            print(line)
        return 1

    print("All reference trips OK.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
