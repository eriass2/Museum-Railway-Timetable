#!/usr/bin/env python3
"""Generate red/orange + green-buss/yellow-buss rows for Lennakatten CSV fixture."""

from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1] / "testdata" / "fixtures" / "lennakatten"

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


def stoptime_rows(service_code: str, stations: list[str], times: list[str | None]) -> list[str]:
    rows: list[str] = []
    seq = 0
    for station, time in zip(stations, times):
        if not time:
            continue
        seq += 1
        rows.append(f"{service_code},{seq},{station},{time},{time},1,0")
    return rows


def service_row(code: str, timetable: str, route: str, number: str, end: str) -> str:
    return f"{code},{timetable},{route},{number},{end},"


# Times from Anslagstidtabell-2026.pdf (main line; branch shuttles omitted).
RED_OUT = {
    "81": ["10:00", "10:04", "10:07", "10:12", "10:23", "10:25", "10:35", "10:46", "10:50", "10:54", "10:57", "11:10", "11:14", "11:25"],
    "91": ["10:45", "10:48", "10:50", "10:53", "11:03", "11:04", "11:12", "11:42", "11:43", "11:47", "11:50", "11:54", "12:04", "12:07"],
    "83": ["12:50", "12:54", "12:57", "13:02", "13:13", "13:15", "13:25", "13:33", "13:37", "13:41", "13:47", "14:00", "14:04", "14:15"],
    "95": ["13:30", "13:33", "13:35", "13:38", "13:48", "13:49", "13:57", "15:44", "15:47", "15:50", "15:53", "16:03", "16:07", "16:15"],
    "99": ["15:20", "15:23", "15:25", "15:28", "15:35", "15:36", "15:43", "15:44", "15:47", "15:50", "15:53", "16:03", "16:07", "16:15"],
    "85": ["15:48", "15:52", "15:55", "16:00", "16:10", "16:12", "16:22", "16:39", "16:43", "16:48", "16:52", "17:05", "17:11", "17:22"],
}
RED_IN = {
    "80": ["07:40", "07:49", "08:02", "08:15", "08:17", "08:22", "08:27", "08:30", "08:45", "08:51", "08:55", "09:04", "09:09", "09:12", "09:20"],
    "92": ["09:40", "09:47", "09:57", "10:08", "10:10", "10:14", "10:17", "10:20", "11:20", "11:24", "11:27", "11:33", "11:36", "11:38", "11:47"],
    "82": ["10:25", "10:30", "10:33", "10:40", "10:44", "10:46", "10:55", "11:00", "11:55", "12:01", "12:05", "12:14", "12:19", "12:22", "12:30"],
    "94": ["12:27", "12:34", "12:41", "12:54", "12:56", "13:01", "13:04", "13:07", "14:05", "14:09", "14:12", "14:18", "14:21", "14:23", "14:32"],
    "84": ["14:25", "14:31", "14:36", "14:46", "14:47", "14:52", "14:55", "14:58", "15:15", "15:15", "15:15", "15:15", "15:15", "15:15", "16:45"],
}
ORANGE_OUT = {
    "73": ["11:15", "11:18", "11:20", "11:24", "11:37", "11:38", "11:47", "14:43", "14:47", "14:50", "14:54", "15:07", "15:11", "15:22"],
    "77": ["13:55", "13:58", "14:00", "14:04", "14:17", "14:18", "14:27", "17:11", "17:14", "17:17", "17:23", "17:33", "17:36", "17:45"],
}
ORANGE_IN = {
    "72": ["09:30", "09:37", "09:49", "09:59", "10:01", "10:06", "10:09", "10:12", "10:25", "10:30", "10:33", "10:40", "10:44", "10:46", "10:55"],
    "76": ["15:30", "15:36", "15:42", "15:52", "15:53", "15:57", "16:00", "16:03", "16:03", "16:08", "16:10", "16:16", "16:19", "16:21", "16:30"],
}

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
        stoptimes.extend(stoptime_rows(code, UP_OUT, times))
        train_types.append(f"{code},{types[num]}")
    for num, times in in_map.items():
        code = f"{prefix}-{num}-in"
        services.append(service_row(code, timetable, "faringe-uppsala-ostra", num, "uppsala-ostra"))
        stoptimes.extend(stoptime_rows(code, FAR_IN, times))
        train_types.append(f"{code},{types[num]}")
    return services, stoptimes, train_types


def main() -> None:
    services_path = ROOT / "services.csv"
    stoptimes_path = ROOT / "stoptimes.csv"
    train_types_path = ROOT / "service_train_types.csv"

    services_lines = services_path.read_text(encoding="utf-8").splitlines()
    stoptimes_lines = stoptimes_path.read_text(encoding="utf-8").splitlines()
    train_types_lines = train_types_path.read_text(encoding="utf-8").splitlines()

    header_svc = services_lines[0]
    header_st = stoptimes_lines[0]
    header_tt = train_types_lines[0]

    kept_services: list[str] = [header_svc]
    kept_stoptimes: list[str] = [header_st]
    kept_train_types: list[str] = [header_tt]

    move_bus = ("green-buss", "yellow-buss")

    for line in services_lines[1:]:
        if not line.strip():
            continue
        code, timetable, *_rest = line.split(",", 3)
        if timetable == "green" and "-bus-" in code:
            kept_services.append(line.replace(",green,", ",green-buss,", 1))
            continue
        if timetable == "yellow" and "-bus-" in code:
            kept_services.append(line.replace(",yellow,", ",yellow-buss,", 1))
            continue
        if timetable in ("red", "orange"):
            continue
        kept_services.append(line)

    for line in stoptimes_lines[1:]:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if code.startswith("red-") or code.startswith("orange-"):
            continue
        kept_stoptimes.append(line)

    for line in train_types_lines[1:]:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if code.startswith("red-") or code.startswith("orange-"):
            continue
        kept_train_types.append(line)

    red_svc, red_st, red_tt = build_rail_block("red", "red", RED_OUT, RED_IN, RED_TRAIN_TYPES)
    ora_svc, ora_st, ora_tt = build_rail_block("orange", "orange", ORANGE_OUT, ORANGE_IN, {n: "angtag" for n in list(ORANGE_OUT) + list(ORANGE_IN)})

    services_path.write_text("\n".join(kept_services + red_svc + ora_svc) + "\n", encoding="utf-8")
    stoptimes_path.write_text("\n".join(kept_stoptimes + red_st + ora_st) + "\n", encoding="utf-8")
    train_types_path.write_text("\n".join(kept_train_types + red_tt + ora_tt) + "\n", encoding="utf-8")
    print("Updated services, stoptimes, service_train_types")


if __name__ == "__main__":
    main()
