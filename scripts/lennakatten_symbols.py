"""P/X stop symbols from Anslagstidtabell-2026.pdf (see docs/CSV_FORMAT.md).

approximate_time (Ca): bold time in PDF -> 0, normal weight -> 1 (docs/STOP_TIME_CA.md).
"""

from __future__ import annotations

STOPTIMES_CSV_HEADER = (
    "service_code,sequence,station_code,arrival_time,departure_time,"
    "pickup_allowed,dropoff_allowed,approximate_time"
)

UP_OUT = [
    "uppsala-ostra",
    "fyrislund",
    "arsta",
    "skolsta",
    "barby",
    "gunsta",
    "marielund",
    "lovstahagen",
    "selkna",
    "lot",
    "lanna",
    "almunge",
    "moga",
    "faringe",
]

FAR_IN = list(reversed(UP_OUT))


def approximate_time_for_stop(*, is_origin: bool, is_last: bool, has_time: bool) -> int:
    """Map PDF typography: origin/destination bold (fixed) vs intermediate normal (Ca)."""
    if not has_time:
        return 0
    return 0 if (is_origin or is_last) else 1


def stoptime_csv_row(
    service_code: str,
    seq: int,
    station: str,
    arrival: str,
    departure: str,
    symbol: str,
    *,
    total_stops: int,
) -> str:
    """One stoptimes.csv row with P/A and approximate_time."""
    pickup, dropoff = symbol_to_flags(
        symbol,
        is_origin=(seq == 1),
        is_last=(seq == total_stops),
    )
    approx = approximate_time_for_stop(
        is_origin=(seq == 1),
        is_last=(seq == total_stops),
        has_time=bool(arrival or departure),
    )
    if total_stops > 1:
        if seq == 1:
            arrival = ""
        if seq == total_stops:
            departure = ""
    return (
        f"{service_code},{seq},{station},{arrival},{departure},"
        f"{pickup},{dropoff},{approx}"
    )


def symbol_to_flags(symbol: str, *, is_origin: bool, is_last: bool) -> tuple[int, int]:
    """Map PDF symbol to pickup_allowed, dropoff_allowed."""
    if is_origin:
        return (1, 0)
    if is_last:
        return (1, 1)
    if symbol == "P":
        return (1, 0)
    return (1, 1)


def flags_for_stations(symbols: list[str]) -> list[tuple[int, int]]:
    """One symbol per station (UP_OUT order)."""
    if len(symbols) != len(UP_OUT):
        raise ValueError(f"expected {len(UP_OUT)} symbols, got {len(symbols)}")
    return [
        symbol_to_flags(sym, is_origin=(idx == 0), is_last=(idx == len(UP_OUT) - 1))
        for idx, sym in enumerate(symbols)
    ]


# Hand-curated from Anslagstidtabell-2026.pdf (RÖD / ORANGE blocks).
RED_OUT_SYMBOLS: dict[str, list[str]] = {
    "81": ["P", "P", "P", "X", "", "X", "", "", "", "", "", "", "", ""],
    "91": ["P", "P", "P", "X", "", "X", "", "", "", "", "", "", "", ""],
    "83": ["P", "P", "P", "X", "", "X", "", "", "", "", "", "", "", ""],
    "95": ["P", "P", "P", "X", "", "X", "", "", "", "", "", "", "", ""],
    "99": ["P", "X", "X", "X", "", "X", "X", "X", "X", "X", "X", "", "X", ""],
    "85": ["P", "X", "X", "X", "", "X", "", "X", "X", "X", "X", "", "X", ""],
}

RED_IN_SYMBOLS: dict[str, list[str]] = {
    # FAR_IN order (faringe → uppsala-ostra).
    "80": ["P", "X", "", "X", "X", "X", "X", "X", "X", "X", "", "X", "X", ""],
    "92": ["P", "X", "", "X", "X", "X", "X", "X", "X", "X", "", "X", "X", ""],
    "82": ["P", "X", "", "X", "X", "X", "X", "X", "X", "X", "", "X", "X", ""],
    "94": ["P", "X", "", "X", "X", "X", "X", "X", "X", "X", "", "X", "X", ""],
    "84": ["P", "X", "", "X", "X", "X", "X", "", "", "", "", "", "", ""],
}

ORANGE_OUT_SYMBOLS: dict[str, list[str]] = {
    "73": ["P", "P", "P", "X", "", "X", "", "X", "X", "X", "X", "", "X", ""],
    "77": ["P", "P", "P", "X", "", "X", "", "X", "X", "X", "X", "", "X", ""],
}

ORANGE_IN_SYMBOLS: dict[str, list[str]] = {
    "72": ["P", "X", "", "X", "X", "X", "X", "X", "X", "X", "", "X", "X", ""],
    "76": ["P", "X", "", "X", "X", "X", "X", "X", "X", "X", "", "X", "X", ""],
}


def symbols_for_train(prefix: str, train: str, direction: str) -> list[str]:
    """Return 14 PDF symbols in travel order (UP_OUT outbound, FAR_IN inbound)."""
    maps: dict[str, dict[str, list[str]]] = {
        "red:out": RED_OUT_SYMBOLS,
        "red:in": RED_IN_SYMBOLS,
        "orange:out": ORANGE_OUT_SYMBOLS,
        "orange:in": ORANGE_IN_SYMBOLS,
    }
    table = maps.get(f"{prefix}:{direction}", {})
    if direction == "out":
        default = ["P"] + [""] * (len(UP_OUT) - 1)
    else:
        default = ["P"] + [""] * (len(FAR_IN) - 1)
    return table.get(train, default)
