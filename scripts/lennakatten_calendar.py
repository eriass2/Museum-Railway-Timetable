"""Trafikdagskalender enligt Anslagstidtabell-2026.pdf.

Stopptider: lennakatten_anslag_tables.py. Detta modul = datumregler + bussfönster.
"""

from __future__ import annotations

from datetime import date

# Anslagstidtabell fotnot: anslutningsbussar 1 juli–16 augusti, se kalender.
GREEN_BUSS_WINDOW_START = date(2026, 7, 1)
GREEN_BUSS_WINDOW_END = date(2026, 8, 16)


def parse_iso(value: str) -> date:
    y, m, d = value.split("-")
    return date(int(y), int(m), int(d))


def in_green_buss_window(day: date) -> bool:
    return GREEN_BUSS_WINDOW_START <= day <= GREEN_BUSS_WINDOW_END


def expected_green_buss_dates(green_rail: list[str], green_vard_rail: list[str]) -> list[str]:
    """Buss på gröna trafikdagar (lördag + ons/tors sommar) inom bussfönstret."""
    candidates = {parse_iso(d) for d in green_rail} | {parse_iso(d) for d in green_vard_rail}
    buss = sorted(d for d in candidates if in_green_buss_window(d))
    return [d.isoformat() for d in buss]


def expected_red_buss_dates(red_rail: list[str]) -> list[str]:
    """Linnés Hammarby-buss på röda söndagar inom bussfönstret (1/7–16/8)."""
    buss = sorted(
        parse_iso(d) for d in red_rail if in_green_buss_window(parse_iso(d))
    )
    return [d.isoformat() for d in buss]
