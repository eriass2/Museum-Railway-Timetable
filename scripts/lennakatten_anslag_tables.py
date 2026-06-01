"""Canonical GRÖN/GUL stop times from Anslagstidtabell-2026.pdf.

Each stop: (station_code, arrival, departure, symbol) where symbol is P, X or ''.
"""

from __future__ import annotations

from lennakatten_symbols import FAR_IN, UP_OUT

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


def _uppsala_after_fjallnora(fjallnora: str, minutes: int = 28) -> str:
    h, m = map(int, fjallnora.split(":"))
    total = h * 60 + m + minutes
    return f"{total // 60:02d}:{total % 60:02d}"


def _uppsala_before_fjallnora(fjallnora: str, minutes: int = 27) -> str:
    h, m = map(int, fjallnora.split(":"))
    total = h * 60 + m - minutes
    return f"{total // 60:02d}:{total % 60:02d}"


# Anslutningsbussar – blå fält i PDF (Selknä* / Fjällnora*).
# Uppsala-ben: +28 min efter Fjällnora (ut) / −27 min före Fjällnora (in), ej i huvudtabellen.
GREEN_BUSS_OUT: dict[str, list[Stop]] = {
    "1": [
        ("selkna", "10:53", "10:53", "P"),
        ("fjallnora", "11:00", "11:00", ""),
        ("uppsala-ostra", _uppsala_after_fjallnora("11:00"), _uppsala_after_fjallnora("11:00"), ""),
    ],
    "2": [
        ("selkna", "11:50", "11:50", "P"),
        ("fjallnora", "11:57", "11:57", ""),
        ("uppsala-ostra", _uppsala_after_fjallnora("11:57"), _uppsala_after_fjallnora("11:57"), ""),
    ],
    "3": [
        ("selkna", "13:40", "13:40", "P"),
        ("fjallnora", "13:47", "13:47", ""),
        ("uppsala-ostra", _uppsala_after_fjallnora("13:47"), _uppsala_after_fjallnora("13:47"), ""),
    ],
    "4": [
        ("selkna", "15:18", "15:18", "P"),
        ("fjallnora", "15:25", "15:25", ""),
        ("uppsala-ostra", _uppsala_after_fjallnora("15:25"), _uppsala_after_fjallnora("15:25"), ""),
    ],
}

GREEN_BUSS_IN: dict[str, list[Stop]] = {
    # B5: ej i anslagstavla (*‑rader); behåll etablerade tider tills Gron-tdt-buss-vard verifieras.
    "5": [
        ("uppsala-ostra", "11:05", "11:05", "P"),
        ("fjallnora", "11:32", "11:32", ""),
        ("selkna", "11:40", "11:40", ""),
    ],
    "6": [
        ("uppsala-ostra", _uppsala_before_fjallnora("12:51"), _uppsala_before_fjallnora("12:51"), "P"),
        ("fjallnora", "12:51", "12:51", ""),
        ("selkna", "12:58", "12:58", ""),
    ],
    "7": [
        ("uppsala-ostra", _uppsala_before_fjallnora("14:42"), _uppsala_before_fjallnora("14:42"), "P"),
        ("fjallnora", "14:42", "14:42", ""),
        ("selkna", "14:49", "14:49", ""),
    ],
    "8": [
        ("uppsala-ostra", _uppsala_before_fjallnora("16:38"), _uppsala_before_fjallnora("16:38"), "P"),
        ("fjallnora", "16:38", "16:38", ""),
        ("selkna", "16:45", "16:45", ""),
    ],
}

# GUL bussar: selkna/fjällnora ej i huvud-PDF; tider validerade mot GUL-tåg + samma Uppsala-offset.
YELLOW_BUSS_OUT: dict[str, list[Stop]] = {
    "1": [
        ("selkna", "17:22", "17:22", "P"),
        ("fjallnora", "17:30", "17:30", ""),
        ("uppsala-ostra", "17:58", "17:58", ""),
    ],
    "2": [
        ("selkna", "22:14", "22:14", "P"),
        ("fjallnora", "22:22", "22:22", ""),
        ("uppsala-ostra", "22:50", "22:50", ""),
    ],
}

YELLOW_BUSS_IN: dict[str, list[Stop]] = {
    "3": [
        ("uppsala-ostra", "16:33", "16:33", "P"),
        ("fjallnora", "17:00", "17:00", ""),
        ("selkna", "17:08", "17:08", ""),
    ],
    "4": [
        ("uppsala-ostra", "21:25", "21:25", "P"),
        ("fjallnora", "21:52", "21:52", ""),
        ("selkna", "22:00", "22:00", ""),
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
        out.append((f"green-b{num}-bus-out", "green-buss", "selkna-uppsala-ostra", stops))
    for num, stops in GREEN_BUSS_IN.items():
        out.append((f"green-b{num}-bus-in", "green-buss", "uppsala-ostra-selkna", stops))
    for num, stops in YELLOW_BUSS_OUT.items():
        out.append((f"yellow-b{num}-bus-out", "yellow-buss", "selkna-uppsala-ostra", stops))
    for num, stops in YELLOW_BUSS_IN.items():
        out.append((f"yellow-b{num}-bus-in", "yellow-buss", "uppsala-ostra-selkna", stops))
    return out
