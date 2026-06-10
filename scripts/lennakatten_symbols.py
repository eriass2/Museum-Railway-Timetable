"""P/X stop symbols from Anslagstidtabell-2026.pdf (see docs/CSV_FORMAT.md).

Schema v3 modes + approximate_time / in_service_timetable (docs/STOP_TIME_SOURCES.md).
"""

from __future__ import annotations

STOPTIMES_CSV_HEADER = (
    "service_code,sequence,station_code,arrival_time,departure_time,"
    "ank_pickup_mode,ank_dropoff_mode,avg_pickup_mode,avg_dropoff_mode,"
    "approximate_time,in_service_timetable"
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


def anslag_overlay_flags(
    *,
    in_b_korplan: bool,
    is_origin: bool,
    is_last: bool,
    has_time: bool,
    service_code: str,
) -> tuple[int, int]:
    """Return (approximate_time, in_service_timetable) per docs/STOP_TIME_SOURCES.md."""
    if "-bus-" in service_code:
        return 1, 0
    in_svc = 1 if in_b_korplan else 0
    approx = approximate_time_for_stop(
        is_origin=is_origin,
        is_last=is_last,
        has_time=has_time,
    )
    if in_svc == 0:
        approx = 1
    return approx, in_svc


def four_modes_from_flags(
    pickup: int,
    dropoff: int,
    *,
    is_origin: bool,
    is_last: bool,
    has_time: bool,
) -> tuple[str, str, str, str]:
    """Return ank_pickup, ank_dropoff, avg_pickup, avg_dropoff."""
    if pickup and dropoff and not has_time:
        pu, do = "on_request", "on_request"
    elif pickup and not dropoff:
        pu, do = "on_request", "none"
    elif not pickup and dropoff:
        pu, do = "none", "on_request"
    elif pickup and dropoff:
        pu, do = "scheduled", "scheduled"
    else:
        pu, do = "none", "none"

    if is_origin:
        return ("none", "none", pu, "none")
    if is_last:
        return ("none", do, "none", "none")
    return (pu, do, pu, do)


def in_service_for_service(service_code: str) -> int:
    """Anslutningsbuss: not in tidtabell B (J4 Ca)."""
    return 0 if "-bus-" in service_code else 1


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
    """One stoptimes.csv row with v3 modes."""
    pickup, dropoff = symbol_to_flags(
        symbol,
        is_origin=(seq == 1),
        is_last=(seq == total_stops),
        station=station,
        service_code=service_code,
    )
    if total_stops > 1:
        if seq == 1:
            arrival = ""
        if seq == total_stops:
            departure = ""
    has_time = bool(arrival or departure)
    ank_pu, ank_do, avg_pu, avg_do = four_modes_from_flags(
        pickup,
        dropoff,
        is_origin=(seq == 1),
        is_last=(seq == total_stops),
        has_time=has_time,
    )
    approx, in_svc = anslag_overlay_flags(
        in_b_korplan=False,
        is_origin=(seq == 1),
        is_last=(seq == total_stops),
        has_time=has_time,
        service_code=service_code,
    )
    return (
        f"{service_code},{seq},{station},{arrival},{departure},"
        f"{ank_pu},{ank_do},{avg_pu},{avg_do},{approx},{in_svc}"
    )


def symbol_to_flags(
    symbol: str,
    *,
    is_origin: bool,
    is_last: bool,
    station: str = "",
    service_code: str = "",
) -> tuple[int, int]:
    """Map PDF symbol to pickup_allowed, dropoff_allowed."""
    if is_origin:
        return (1, 0)
    if is_last:
        return (1, 1)
    if symbol == "P":
        return (1, 0)
    if symbol == "X" and station == "selkna" and service_code:
        if "-out" in service_code:
            return (0, 1)
        if "-in" in service_code:
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
