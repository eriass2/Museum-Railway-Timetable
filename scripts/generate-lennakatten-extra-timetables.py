#!/usr/bin/env python3
"""Generate Lennakatten fixture rows (red/orange, green-vard clone, bus split).

Run ``python scripts/sync-lennakatten-rail-fixture.py`` first so GRÖN/GUL rail
(including Marielund train-change splits) is up to date before cloning to green-vard.
"""

from __future__ import annotations

from pathlib import Path

from lennakatten_anslag_tables import (
    ORANGE_IN_TIMES,
    ORANGE_OUT_TIMES,
    RED_IN_TIMES,
    RED_OUT_TIMES,
)
from lennakatten_symbols import FAR_IN, STOPTIMES_CSV_HEADER, UP_OUT, stoptime_csv_row, symbol_to_flags, symbols_for_train

ROOT = Path(__file__).resolve().parents[1] / "testdata" / "fixtures" / "lennakatten"


def stoptime_rows(
    service_code: str,
    stations: list[str],
    times: list[str | None],
    symbols: list[str],
) -> list[str]:
    rows: list[str] = []
    count = min(len(stations), len(times), len(symbols))
    if count == 0:
        return rows
    stations = stations[:count]
    times = times[:count]
    symbols = (symbols + [""] * count)[:count]
    active = [(station, time, symbol) for station, time, symbol in zip(stations, times, symbols) if time]
    for idx, (station, time, symbol) in enumerate(active):
        seq = idx + 1
        pickup, dropoff = symbol_to_flags(
            symbol,
            is_origin=(seq == 1),
            is_last=(seq == len(active)),
            station=station,
            service_code=service_code,
        )
        rows.append(
            stoptime_csv_row(
                service_code,
                seq,
                station,
                time,
                time,
                symbol,
                total_stops=len(active),
            )
        )
    return rows


def service_row(code: str, timetable: str, route: str, number: str, end: str) -> str:
    return f"{code},{timetable},{route},{number},{end},"


# Times from Anslagstidtabell-2026.pdf (canonical copy in lennakatten_anslag_tables.py).
RED_OUT = RED_OUT_TIMES
RED_IN = RED_IN_TIMES
ORANGE_OUT = ORANGE_OUT_TIMES
ORANGE_IN = ORANGE_IN_TIMES

RED_TRAIN_TYPES = {
    "81": "angtag",
    "91": "ralsbuss",
    "83": "angtag",
    "95": "ralsbuss",
    "99": "ralsbuss",
    "85": "angtag",
    "80": "angtag",
    "92": "ralsbuss",
    "82": "angtag",
    "94": "ralsbuss",
    "84": "angtag",
}


def build_rail_block(prefix: str, timetable: str, out_map: dict, in_map: dict, types: dict) -> tuple[list[str], list[str], list[str]]:
    services: list[str] = []
    stoptimes: list[str] = []
    train_types: list[str] = []
    for num, times in out_map.items():
        code = f"{prefix}-{num}-out"
        services.append(service_row(code, timetable, "uppsala-faringe", num, "faringe"))
        stoptimes.extend(
            stoptime_rows(code, UP_OUT, times, symbols_for_train(prefix, num, "out"))
        )
        train_types.append(f"{code},{types[num]}")
    for num, times in in_map.items():
        code = f"{prefix}-{num}-in"
        services.append(service_row(code, timetable, "faringe-uppsala-ostra", num, "uppsala-ostra"))
        stoptimes.extend(
            stoptime_rows(code, FAR_IN, times, symbols_for_train(prefix, num, "in"))
        )
        train_types.append(f"{code},{types[num]}")
    return services, stoptimes, train_types


def clone_green_rail_to_vard(
    services: list[str],
    stoptimes: list[str],
    train_types: list[str],
) -> tuple[list[str], list[str], list[str]]:
    """Duplicate green rail services for green-vard (Wed/Thu summer)."""
    new_svc: list[str] = []
    new_st: list[str] = []
    new_tt: list[str] = []
    code_map: dict[str, str] = {}

    for line in services:
        if not line.strip() or line.startswith("service_code"):
            continue
        parts = line.split(",")
        code, timetable = parts[0], parts[1]
        if timetable != "green" or "-bus-" in code:
            continue
        new_code = "green-vard-" + code.removeprefix("green-")
        code_map[code] = new_code
        parts[0] = new_code
        parts[1] = "green-vard"
        new_svc.append(",".join(parts))

    for line in stoptimes:
        if not line.strip() or line.startswith("service_code"):
            continue
        code, rest = line.split(",", 1)
        if code in code_map:
            new_st.append(f"{code_map[code]},{rest}")

    for line in train_types:
        if not line.strip() or line.startswith("service_code"):
            continue
        code, train_type = line.split(",", 1)
        if code in code_map:
            new_tt.append(f"{code_map[code]},{train_type}")

    return new_svc, new_st, new_tt


def main() -> None:
    services_path = ROOT / "services.csv"
    stoptimes_path = ROOT / "stoptimes.csv"
    train_types_path = ROOT / "service_train_types.csv"

    services_lines = services_path.read_text(encoding="utf-8").splitlines()
    stoptimes_lines = stoptimes_path.read_text(encoding="utf-8").splitlines()
    train_types_lines = train_types_path.read_text(encoding="utf-8").splitlines()

    header_svc = services_lines[0]
    header_st = STOPTIMES_CSV_HEADER
    header_tt = train_types_lines[0]

    kept_services: list[str] = [header_svc]
    kept_stoptimes: list[str] = [header_st]
    kept_train_types: list[str] = [header_tt]

    for line in services_lines[1:]:
        if not line.strip():
            continue
        code, timetable, *_rest = line.split(",", 3)
        if timetable == "green" and "-bus-" in code:
            kept_services.append(line.replace(",green,", ",green-buss,", 1))
            continue
        if timetable in ("red", "orange", "green-vard"):
            continue
        kept_services.append(line)

    for line in stoptimes_lines[1:]:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if code.startswith("red-") or code.startswith("orange-") or code.startswith("green-vard-"):
            continue
        kept_stoptimes.append(line)

    for line in train_types_lines[1:]:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if code.startswith("red-") or code.startswith("orange-") or code.startswith("green-vard-"):
            continue
        kept_train_types.append(line)

    vard_svc, vard_st, vard_tt = clone_green_rail_to_vard(
        kept_services, kept_stoptimes, kept_train_types
    )
    red_svc, red_st, red_tt = build_rail_block("red", "red", RED_OUT, RED_IN, RED_TRAIN_TYPES)
    ora_svc, ora_st, ora_tt = build_rail_block("orange", "orange", ORANGE_OUT, ORANGE_IN, {n: "angtag" for n in list(ORANGE_OUT) + list(ORANGE_IN)})

    services_path.write_text(
        "\n".join(kept_services + vard_svc + red_svc + ora_svc) + "\n",
        encoding="utf-8",
    )
    stoptimes_path.write_text(
        "\n".join(kept_stoptimes + vard_st + red_st + ora_st) + "\n",
        encoding="utf-8",
    )
    train_types_path.write_text(
        "\n".join(kept_train_types + vard_tt + red_tt + ora_tt) + "\n",
        encoding="utf-8",
    )
    print(
        f"Updated fixture: +{len(vard_svc)} green-vard services, "
        f"+{len(red_svc)} red, +{len(ora_svc)} orange"
    )


if __name__ == "__main__":
    main()
