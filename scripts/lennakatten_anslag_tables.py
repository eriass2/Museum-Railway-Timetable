"""Canonical GRÖN/GUL stop times from Anslagstidtabell-2026.pdf.

Each stop: (station_code, arrival, departure, symbol) where symbol is P, X or ''.
"""

from __future__ import annotations

from lennakatten_symbols import FAR_IN, UP_OUT, symbols_for_train

Stop = tuple[str, str, str, str]

# Outbound: Uppsala Östra → Faringe (train numbers 71, 93, …).
GREEN_OUT: dict[str, list[Stop]] = {
    "71": [
        ("uppsala-ostra", "10:00", "10:00", "P"),
        ("fyrislund", "10:03", "10:03", "P"),
        ("arsta", "10:05", "10:05", "P"),
        ("skolsta", "10:09", "10:09", "X"),
        ("barby", "10:23", "10:23", ""),
        ("gunsta", "10:24", "10:24", "X"),
        ("marielund", "10:35", "10:45", ""),
        ("lovstahagen", "10:46", "10:46", "P"),
        ("selkna", "10:50", "10:50", ""),
        ("lot", "10:54", "10:54", "X"),
        ("lanna", "10:57", "10:57", ""),
        ("almunge", "11:10", "11:10", ""),
        ("moga", "11:14", "11:14", "X"),
        ("faringe", "11:25", "11:25", ""),
    ],
    "93": [
        ("uppsala-ostra", "11:10", "11:10", "P"),
        ("fyrislund", "11:13", "11:13", "P"),
        ("arsta", "11:15", "11:15", "P"),
        ("skolsta", "11:18", "11:18", "X"),
        ("barby", "11:28", "11:28", ""),
        ("gunsta", "11:29", "11:29", "X"),
        ("marielund", "11:37", "11:42", ""),
        ("lovstahagen", "11:43", "11:43", "X"),
        ("selkna", "11:47", "11:47", ""),
        ("lot", "11:50", "11:50", "X"),
        ("lanna", "11:54", "11:54", ""),
        ("almunge", "12:04", "12:04", ""),
        ("moga", "12:07", "12:07", "X"),
        ("faringe", "12:17", "12:17", ""),
    ],
    "75": [
        ("uppsala-ostra", "12:38", "12:38", "P"),
        ("fyrislund", "12:41", "12:41", "P"),
        ("arsta", "12:43", "12:43", "P"),
        ("skolsta", "12:47", "12:47", "X"),
        ("barby", "13:00", "13:00", ""),
        ("gunsta", "13:01", "13:01", "X"),
        ("marielund", "13:10", "13:32", ""),
        ("lovstahagen", "13:33", "13:33", "X"),
        ("selkna", "13:37", "13:37", ""),
        ("lot", "13:41", "13:41", "X"),
        ("lanna", "13:47", "13:47", ""),
        ("almunge", "14:00", "14:00", ""),
        ("moga", "14:04", "14:04", "X"),
        ("faringe", "14:15", "14:15", ""),
    ],
    "63": [
        ("uppsala-ostra", "14:10", "14:10", "P"),
        ("fyrislund", "14:13", "14:13", "P"),
        ("arsta", "14:15", "14:15", "P"),
        ("skolsta", "14:19", "14:19", "X"),
        ("barby", "14:30", "14:30", ""),
        ("gunsta", "14:31", "14:31", "X"),
        ("marielund", "14:40", "15:10", ""),
        ("lovstahagen", "15:11", "15:11", "X"),
        ("selkna", "15:15", "15:15", ""),
        ("lot", "15:18", "15:18", "X"),
        ("lanna", "15:21", "15:21", ""),
        ("almunge", "15:31", "15:31", ""),
        ("moga", "15:34", "15:34", "X"),
        ("faringe", "15:43", "15:43", ""),
    ],
    "65": [
        ("uppsala-ostra", "15:55", "15:55", "P"),
        ("fyrislund", "15:58", "15:58", "P"),
        ("arsta", "16:00", "16:00", "P"),
        ("skolsta", "16:04", "16:04", "X"),
        ("barby", "16:13", "16:13", ""),
        ("gunsta", "16:14", "16:14", "X"),
        ("marielund", "16:23", "17:00", ""),
        ("lovstahagen", "17:01", "17:01", "X"),
        ("selkna", "17:04", "17:04", "X"),
        ("lot", "17:08", "17:08", "X"),
        ("lanna", "17:11", "17:11", "X"),
        ("almunge", "17:22", "17:22", ""),
        ("moga", "17:26", "17:26", "X"),
        ("faringe", "17:37", "17:37", ""),
    ],
    "79": [
        ("uppsala-ostra", "18:07", "18:07", "P"),
        ("fyrislund", "18:10", "18:10", "X"),
        ("arsta", "18:12", "18:12", "X"),
        ("skolsta", "18:16", "18:16", "X"),
        ("barby", "18:25", "18:25", ""),
        ("gunsta", "18:26", "18:26", "X"),
        ("marielund", "18:35", "18:50", ""),
        ("lovstahagen", "18:51", "18:51", "X"),
        ("selkna", "18:54", "18:54", "X"),
        ("lot", "18:58", "18:58", "X"),
        ("lanna", "19:01", "19:01", ""),
        ("almunge", "19:12", "19:12", ""),
        ("moga", "19:16", "19:16", "X"),
        ("faringe", "19:27", "19:27", ""),
    ],
}

# Inbound: Faringe → Uppsala Östra (FAR_IN order).
GREEN_IN: dict[str, list[Stop]] = {
    "70": [
        ("faringe", "07:55", "07:55", "P"),
        ("moga", "08:02", "08:02", "X"),
        ("almunge", "08:14", "08:14", ""),
        ("lanna", "08:25", "08:25", "X"),
        ("lot", "08:27", "08:27", "X"),
        ("selkna", "08:31", "08:31", "X"),
        ("lovstahagen", "08:34", "08:34", "X"),
        ("marielund", "08:38", "08:53", ""),
        ("gunsta", "08:58", "08:58", "X"),
        ("barby", "09:01", "09:01", ""),
        ("skolsta", "09:08", "09:08", "X"),
        ("arsta", "09:12", "09:12", "X"),
        ("fyrislund", "09:14", "09:14", "X"),
        ("uppsala-ostra", "09:23", "09:23", ""),
    ],
    "60": [
        ("faringe", "09:40", "09:40", "P"),
        ("moga", "09:47", "09:47", "X"),
        ("almunge", "09:57", "09:57", ""),
        ("lanna", "10:08", "10:08", "X"),
        ("lot", "10:10", "10:10", "X"),
        ("selkna", "10:14", "10:14", "X"),
        ("lovstahagen", "10:17", "10:17", "X"),
        ("marielund", "10:20", "11:45", ""),
        ("gunsta", "11:50", "11:50", "X"),
        ("barby", "11:53", "11:53", ""),
        ("skolsta", "12:00", "12:00", "X"),
        ("arsta", "12:04", "12:04", "X"),
        ("fyrislund", "12:06", "12:06", "X"),
        ("uppsala-ostra", "12:17", "12:17", ""),
    ],
    "62": [
        ("faringe", "12:27", "12:27", "P"),
        ("moga", "12:34", "12:34", "X"),
        ("almunge", "12:41", "12:41", ""),
        ("lanna", "12:54", "12:54", "X"),
        ("lot", "12:56", "12:56", "X"),
        ("selkna", "13:01", "13:01", "X"),
        ("lovstahagen", "13:04", "13:04", "X"),
        ("marielund", "13:07", "13:15", ""),
        ("gunsta", "13:20", "13:20", "X"),
        ("barby", "13:23", "13:23", ""),
        ("skolsta", "13:30", "13:30", "X"),
        ("arsta", "13:34", "13:34", "X"),
        ("fyrislund", "13:36", "13:36", "X"),
        ("uppsala-ostra", "13:47", "13:47", ""),
    ],
    "96": [
        ("faringe", "14:25", "14:25", "P"),
        ("moga", "14:31", "14:31", "X"),
        ("almunge", "14:36", "14:36", ""),
        ("lanna", "14:46", "14:46", ""),
        ("lot", "14:47", "14:47", "X"),
        ("selkna", "14:52", "14:52", ""),
        ("lovstahagen", "14:55", "14:55", "X"),
        ("marielund", "14:58", "15:05", ""),
        ("gunsta", "15:10", "15:10", "X"),
        ("barby", "15:13", "15:13", ""),
        ("skolsta", "15:20", "15:20", "X"),
        ("arsta", "15:24", "15:24", "X"),
        ("fyrislund", "15:26", "15:26", "X"),
        ("uppsala-ostra", "15:37", "15:37", ""),
    ],
    "78": [
        ("faringe", "16:13", "16:13", "P"),
        ("moga", "16:20", "16:20", "X"),
        ("almunge", "16:28", "16:28", ""),
        ("lanna", "16:41", "16:41", "X"),
        ("lot", "16:43", "16:43", "X"),
        ("selkna", "16:48", "16:48", "X"),
        ("lovstahagen", "16:51", "16:51", "X"),
        ("marielund", "16:55", "17:15", ""),
        ("gunsta", "17:20", "17:20", "X"),
        ("barby", "17:23", "17:23", ""),
        ("skolsta", "17:30", "17:30", "X"),
        ("arsta", "17:34", "17:34", "X"),
        ("fyrislund", "17:36", "17:36", "X"),
        ("uppsala-ostra", "17:47", "17:47", ""),
    ],
}

YELLOW_OUT: dict[str, list[Stop]] = {
    "101": [
        ("uppsala-ostra", "16:45", "16:45", "P"),
        ("fyrislund", "16:48", "16:48", "P"),
        ("arsta", "16:50", "16:50", "P"),
        ("skolsta", "16:53", "16:53", "X"),
        ("barby", "17:03", "17:03", ""),
        ("gunsta", "17:04", "17:04", "X"),
        ("marielund", "17:10", "17:10", "X"),
        ("lovstahagen", "17:11", "17:11", "X"),
        ("selkna", "17:14", "17:14", "X"),
        ("lot", "17:17", "17:17", "X"),
        ("lanna", "17:23", "17:23", ""),
        ("almunge", "17:33", "17:33", ""),
        ("moga", "17:36", "17:36", "X"),
        ("faringe", "17:45", "17:45", ""),
    ],
    "103": [
        ("uppsala-ostra", "21:35", "21:35", "P"),
        ("fyrislund", "21:38", "21:38", "P"),
        ("arsta", "21:40", "21:40", "P"),
        ("skolsta", "21:43", "21:43", "X"),
        ("barby", "21:50", "21:50", ""),
        ("gunsta", "21:51", "21:51", "X"),
        ("marielund", "21:58", "21:58", "X"),
        ("lovstahagen", "21:59", "21:59", "X"),
        ("selkna", "22:02", "22:02", "X"),
        ("lot", "22:05", "22:05", "X"),
        ("lanna", "22:08", "22:08", "X"),
        ("almunge", "22:18", "22:18", ""),
        ("moga", "22:21", "22:21", "X"),
        ("faringe", "22:32", "22:32", ""),
    ],
}

YELLOW_IN: dict[str, list[Stop]] = {
    "100": [
        ("faringe", "15:30", "15:30", "P"),
        ("moga", "15:36", "15:36", "X"),
        ("almunge", "15:42", "15:42", ""),
        ("lanna", "15:52", "15:52", "X"),
        ("lot", "15:53", "15:53", "X"),
        ("selkna", "15:57", "15:57", "X"),
        ("lovstahagen", "16:00", "16:00", "X"),
        ("marielund", "16:03", "16:03", "X"),
        ("gunsta", "16:08", "16:08", "X"),
        ("barby", "16:10", "16:10", ""),
        ("skolsta", "16:16", "16:16", "X"),
        ("arsta", "16:19", "16:19", "X"),
        ("fyrislund", "16:21", "16:21", "X"),
        ("uppsala-ostra", "16:30", "16:30", ""),
    ],
    "102": [
        ("faringe", "20:10", "20:10", "P"),
        ("moga", "20:16", "20:16", "X"),
        ("almunge", "20:22", "20:22", ""),
        ("lanna", "20:37", "20:37", ""),
        ("lot", "20:38", "20:38", "X"),
        ("selkna", "20:42", "20:42", "X"),
        ("lovstahagen", "20:45", "20:45", "X"),
        ("marielund", "20:47", "20:47", "X"),
        ("gunsta", "20:51", "20:51", "X"),
        ("barby", "20:54", "20:54", ""),
        ("skolsta", "21:00", "21:00", "X"),
        ("arsta", "21:03", "21:03", "X"),
        ("fyrislund", "21:05", "21:05", "X"),
        ("uppsala-ostra", "21:15", "21:15", ""),
    ],
}


# RÖD / ORANGE – huvudlinje (tider från Anslagstidtabell; P/X via lennakatten_symbols).
RED_OUT_TIMES: dict[str, list[str]] = {
    "81": ["10:00", "10:04", "10:07", "10:12", "10:23", "10:25", "10:35", "10:46", "10:50", "10:54", "10:57", "11:10", "11:14", "11:25"],
    "91": ["10:45", "10:48", "10:50", "10:53", "11:03", "11:04", "11:12", "11:42", "11:43", "11:47", "11:50", "11:54", "12:04", "12:07"],
    "83": ["12:50", "12:54", "12:57", "13:02", "13:13", "13:15", "13:25", "13:33", "13:37", "13:41", "13:47", "14:00", "14:04", "14:15"],
    "95": ["13:30", "13:33", "13:35", "13:38", "13:48", "13:49", "13:57", "15:44", "15:47", "15:50", "15:53", "16:03", "16:07", "16:15"],
    "99": ["15:20", "15:23", "15:25", "15:28", "15:35", "15:36", "15:43", "15:44", "15:47", "15:50", "15:53", "16:03", "16:07", "16:15"],
    "85": ["15:48", "15:52", "15:55", "16:00", "16:10", "16:12", "16:22", "16:39", "16:43", "16:48", "16:52", "17:05", "17:11", "17:22"],
}
RED_IN_TIMES: dict[str, list[str]] = {
    "80": ["07:40", "07:49", "08:02", "08:15", "08:17", "08:22", "08:27", "08:30", "08:45", "08:51", "08:55", "09:04", "09:09", "09:12", "09:20"],
    "92": ["09:40", "09:47", "09:57", "10:08", "10:10", "10:14", "10:17", "10:20", "11:20", "11:24", "11:27", "11:33", "11:36", "11:38", "11:47"],
    "82": ["10:25", "10:30", "10:33", "10:40", "10:44", "10:46", "10:55", "11:00", "11:55", "12:01", "12:05", "12:14", "12:19", "12:22", "12:30"],
    "94": ["12:27", "12:34", "12:41", "12:54", "12:56", "13:01", "13:04", "13:07", "14:05", "14:09", "14:12", "14:18", "14:21", "14:23", "14:32"],
    "84": ["14:25", "14:31", "14:36", "14:46", "14:47", "14:52", "14:55", "14:58", "15:15", "15:15", "15:15", "15:15", "15:15", "15:15", "16:45"],
}
ORANGE_OUT_TIMES: dict[str, list[str]] = {
    "73": ["11:15", "11:18", "11:20", "11:24", "11:37", "11:38", "11:47", "14:43", "14:47", "14:50", "14:54", "15:07", "15:11", "15:22"],
    "77": ["13:55", "13:58", "14:00", "14:04", "14:17", "14:18", "14:27", "17:11", "17:14", "17:17", "17:23", "17:33", "17:36", "17:45"],
}
ORANGE_IN_TIMES: dict[str, list[str]] = {
    "72": ["09:30", "09:37", "09:49", "09:59", "10:01", "10:06", "10:09", "10:12", "10:25", "10:30", "10:33", "10:40", "10:44", "10:46", "10:55"],
    "76": ["15:30", "15:36", "15:42", "15:52", "15:53", "15:57", "16:00", "16:03", "16:03", "16:08", "16:10", "16:16", "16:19", "16:21", "16:30"],
}


def _rail_out_stops(prefix: str, train: str, times: list[str]) -> list[Stop]:
    symbols = symbols_for_train(prefix, train, "out")
    count = min(len(UP_OUT), len(times), len(symbols))
    return [(UP_OUT[i], times[i], times[i], symbols[i]) for i in range(count)]


def _rail_in_stops(prefix: str, train: str, times: list[str]) -> list[Stop]:
    symbols = symbols_for_train(prefix, train, "in")
    count = min(len(FAR_IN), len(times), len(symbols))
    return [(FAR_IN[i], times[i], times[i], symbols[i]) for i in range(count)]


# Anslutningsbussar – blå stjärnrader i GRÖN-tabellen (Från Selknä* / Till Fjällnora* m.m.).
# Trafik 1/7–16/8 på dagar markerade ”Bussanslutningar kör” i kalendern. Ingen GUL-buss i PDF.
GREEN_BUSS_OUT: dict[str, list[Stop]] = {
    "1": [
        ("selkna", "10:53", "10:53", "P"),
        ("fjallnora", "11:00", "11:00", ""),
    ],
    "2": [
        ("selkna", "11:50", "11:50", "P"),
        ("fjallnora", "11:57", "11:57", ""),
    ],
    "3": [
        ("selkna", "13:40", "13:40", "P"),
        ("fjallnora", "13:47", "13:47", ""),
    ],
    "4": [
        ("selkna", "15:18", "15:18", "P"),
        ("fjallnora", "15:25", "15:25", ""),
    ],
}

GREEN_BUSS_IN: dict[str, list[Stop]] = {
    "6": [
        ("fjallnora", "12:51", "12:51", "P"),
        ("selkna", "12:58", "12:58", ""),
    ],
    "7": [
        ("fjallnora", "14:42", "14:42", "P"),
        ("selkna", "14:49", "14:49", ""),
    ],
    "8": [
        ("fjallnora", "16:38", "16:38", "P"),
        ("selkna", "16:45", "16:45", ""),
    ],
}


def service_definitions() -> list[tuple[str, str, str, list[Stop]]]:
    """Return (service_code, timetable, route_code, stops)."""
    out: list[tuple[str, str, str, list[Stop]]] = []
    for num, stops in GREEN_OUT.items():
        out.append((f"green-{num}-out", "green", "uppsala-faringe", stops))
    for num, stops in GREEN_IN.items():
        out.append((f"green-{num}-in", "green", "faringe-uppsala-ostra", stops))
    for num, stops in YELLOW_OUT.items():
        out.append((f"yellow-{num}-out", "yellow", "uppsala-faringe", stops))
    for num, stops in YELLOW_IN.items():
        out.append((f"yellow-{num}-in", "yellow", "faringe-uppsala-ostra", stops))
    for num, stops in GREEN_BUSS_OUT.items():
        out.append((f"green-b{num}-bus-out", "green-buss", "selkna-fjallnora", stops))
    for num, stops in GREEN_BUSS_IN.items():
        out.append((f"green-b{num}-bus-in", "green-buss", "fjallnora-selkna", stops))
    return out


def red_orange_service_definitions() -> list[tuple[str, str, str, list[Stop]]]:
    """Return (service_code, timetable, route_code, stops) for RÖD/ORANGE rail."""
    out: list[tuple[str, str, str, list[Stop]]] = []
    for num, times in RED_OUT_TIMES.items():
        out.append((f"red-{num}-out", "red", "uppsala-faringe", _rail_out_stops("red", num, times)))
    for num, times in RED_IN_TIMES.items():
        out.append((f"red-{num}-in", "red", "faringe-uppsala-ostra", _rail_in_stops("red", num, times)))
    for num, times in ORANGE_OUT_TIMES.items():
        out.append((f"orange-{num}-out", "orange", "uppsala-faringe", _rail_out_stops("orange", num, times)))
    for num, times in ORANGE_IN_TIMES.items():
        out.append((f"orange-{num}-in", "orange", "faringe-uppsala-ostra", _rail_in_stops("orange", num, times)))
    return out


def pdf_service_definitions() -> list[tuple[str, str, str, list[Stop]]]:
    """All services verifiable against Anslagstidtabell-2026.pdf."""
    return service_definitions() + red_orange_service_definitions()
