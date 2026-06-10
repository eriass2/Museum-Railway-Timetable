"""Parse Tidtabellsboken-del-B.pdf → stop times with four Ank/Avg modes.

See docs/STOP_TIME_SOURCES.md for symbol mapping and fixture rules.
"""

from __future__ import annotations

import re
from dataclasses import dataclass
from pathlib import Path

from lennakatten_anslag_tables import (
    GREEN_IN,
    GREEN_OUT,
    GREEN_RAIL_TRAIN_TYPES,
    MARIELUND,
    MARIELUND_TRAIN_CHANGES,
    YELLOW_IN,
    YELLOW_OUT,
    YELLOW_RAIL_TRAIN_TYPES,
    service_definitions,
)
from lennakatten_symbols import UP_OUT, approximate_time_for_stop, four_modes_from_flags, symbol_to_flags

B_PDF_PATH = Path(__file__).resolve().parents[1] / "testdata/reference-pdfs/Tidtabellsboken-del-B.pdf"

STATION_ABBREV: dict[str, str] = {
    "Uö": "uppsala-ostra",
    "Fl": "fyrislund",
    "Ås": "arsta",
    "Sa": "skolsta",
    "B": "barby",
    "Ga": "gunsta",
    "Ml": "marielund",
    "Lh": "lovstahagen",
    "Slä": "selkna",
    "Löt": "lot",
    "Lna": "lanna",
    "Alg": "almunge",
    "Mg": "moga",
    "Frg": "faringe",
    "Lbr": "linnes-hammarby",
}

TRAIN_HEADER = re.compile(r"^(?:Pt|Tjt)\s+(\d+)")
KORPLAN_HEADER = re.compile(r"\d\s+Ank\s+Avg")
TIME_RE = re.compile(r"(?<!\d)(\d{1,2})[.:](\d{2})(?!\d)")
STATION_TOKEN = re.compile(
    r"\b(Uö|Fl|Ås|Sa|Ga|Ml|Lh|Slä|Löt|Lna|Alg|Mg|Frg|Lbr|B)\b"
)
SYMBOL_RE = re.compile(r"(?<![A-Za-zÅÄÖåäö])([pax])(?![A-Za-zÅÄÖåäö])", re.IGNORECASE)
CIRCLE_CHARS = "ó○oO0°¾ÒÌ"

Mode = str  # none | scheduled | on_request

CONTINUATION_TRAINS = frozenset({"61", "97", "74", "64"})


@dataclass(frozen=True)
class BColumn:
    ank_symbol: str
    avg_symbol: str
    ank_time: str
    avg_time: str


@dataclass(frozen=True)
class BStop:
    station_code: str
    arrival: str
    departure: str
    ank_pickup_mode: Mode
    ank_dropoff_mode: Mode
    avg_pickup_mode: Mode
    avg_dropoff_mode: Mode
    pass_through: bool = False


def _normalize_time(match: re.Match[str]) -> str:
    return f"{int(match.group(1)):02d}:{match.group(2)}"


def _times_in(text: str) -> list[str]:
    return [_normalize_time(match) for match in TIME_RE.finditer(text)]


def _has_circle(text: str) -> bool:
    return any(ch in text for ch in CIRCLE_CHARS)


def _extract_symbols(text: str) -> tuple[str, str]:
    letters = [sym.lower() for sym in SYMBOL_RE.findall(text)]
    if _has_circle(text):
        if not letters:
            return ("circle", "circle")
        if len(letters) == 1:
            return ("circle", letters[0])
        return (letters[0], letters[1])
    if len(letters) >= 2:
        return (letters[0], letters[1])
    if len(letters) == 1:
        return ("", letters[0])
    return ("", "")


def _modes_from_symbol(symbol: str, *, has_time: bool) -> tuple[Mode, Mode]:
    sym = symbol.lower()
    if sym in ("", "circle"):
        if has_time:
            return ("scheduled", "scheduled")
        return ("none", "none")
    if sym == "p":
        return ("on_request", "none")
    if sym == "a":
        return ("none", "on_request")
    if sym == "x":
        return ("on_request", "on_request")
    if has_time:
        return ("scheduled", "scheduled")
    return ("none", "none")


def _column_to_modes(column: BColumn) -> tuple[Mode, Mode, Mode, Mode]:
    ank_has = bool(column.ank_time)
    avg_has = bool(column.avg_time)
    ank_pu, ank_do = _modes_from_symbol(column.ank_symbol, has_time=ank_has)
    avg_pu, avg_do = _modes_from_symbol(column.avg_symbol, has_time=avg_has)
    if ank_has and not avg_has and ank_pu == "none" and ank_do == "none":
        ank_do = "scheduled"
    if avg_has and not ank_has and avg_pu == "none" and avg_do == "none":
        avg_pu = "scheduled"
    if ank_has and avg_has:
        if ank_pu == "none" and ank_do == "none":
            ank_do = "scheduled"
        if avg_pu == "none" and avg_do == "none":
            avg_pu = "scheduled"
    return ank_pu, ank_do, avg_pu, avg_do


def _split_line(line: str) -> tuple[str, str, str] | None:
    match = STATION_TOKEN.search(line)
    if match is None:
        return None
    return line[: match.start()], match.group(1), line[match.end() :]


def _parse_column(text: str) -> BColumn:
    cleaned = re.sub(r"\s+", " ", text.strip())
    times = _times_in(cleaned)
    ank_sym, avg_sym = _extract_symbols(cleaned)
    if len(times) >= 2:
        return BColumn(ank_sym, avg_sym, times[0], times[1])
    if len(times) == 1:
        return BColumn(ank_sym, avg_sym, "", times[0])
    return BColumn(ank_sym, avg_sym, "", "")


def _times_from_column(
    column: BColumn,
    *,
    is_first: bool,
    is_last: bool,
    station_code: str = "",
    train_num: str = "",
    direction: str = "",
) -> tuple[str, str]:
    if column.ank_time and column.avg_time:
        return column.ank_time, column.avg_time
    single = column.avg_time or column.ank_time
    if not single:
        return "", ""
    if is_first:
        return "", single
    if is_last:
        return single, ""
    if station_code == MARIELUND and train_num and MARIELUND_TRAIN_CHANGES.get((direction, train_num)):
        return single, ""
    return "", single


def _column_to_stop(
    station_code: str,
    column: BColumn,
    *,
    is_first: bool,
    is_last: bool,
    train_num: str = "",
    direction: str = "",
) -> BStop:
    ank_pu, ank_do, avg_pu, avg_do = _column_to_modes(column)
    arrival, departure = _times_from_column(
        column,
        is_first=is_first,
        is_last=is_last,
        station_code=station_code,
        train_num=train_num,
        direction=direction,
    )
    pass_through = (
        not arrival
        and not departure
        and ank_pu == "none"
        and ank_do == "none"
        and avg_pu == "none"
        and avg_do == "none"
        and (
            column.ank_symbol == "circle"
            or column.avg_symbol == "circle"
            or _has_circle(f"{column.ank_symbol} {column.avg_symbol}")
        )
    )
    return BStop(
        station_code,
        arrival,
        departure,
        ank_pu,
        ank_do,
        avg_pu,
        avg_do,
        pass_through,
    )


def _trim_endpoint_stops(stops: list[BStop]) -> list[BStop]:
    if not stops:
        return stops
    trimmed: list[BStop] = []
    for idx, stop in enumerate(stops):
        is_first = idx == 0
        is_last = idx == len(stops) - 1
        arrival = "" if is_first else stop.arrival
        departure = "" if is_last else stop.departure
        ank_pu, ank_do, avg_pu, avg_do = stop.ank_pickup_mode, stop.ank_dropoff_mode, stop.avg_pickup_mode, stop.avg_dropoff_mode
        if is_first:
            ank_pu, ank_do = "none", "none"
            avg_do = "none"
            if departure and avg_pu == "none":
                avg_pu = "scheduled"
        if is_last:
            avg_pu, avg_do = "none", "none"
            if arrival and ank_do == "none":
                ank_do = "scheduled"
        trimmed.append(BStop(stop.station_code, arrival, departure, ank_pu, ank_do, avg_pu, avg_do))
    return trimmed


def _early_post_column(post: list[str]) -> str:
    chunks: list[str] = []
    for line in post:
        if STATION_TOKEN.search(line):
            break
        stripped = line.strip()
        if not stripped:
            if chunks:
                break
            continue
        chunks.append(stripped)
    return " ".join(chunks)


def _block_column_texts(
    anchor_left: str,
    anchor_right: str,
    pre: list[str],
    post: list[str],
    direction: str,
    *,
    is_first_anchor: bool,
) -> tuple[str, str]:
    left = anchor_left.strip()
    right = anchor_right.strip()
    pre_text = " ".join(line.strip() for line in pre if line.strip())
    post_text = " ".join(line.strip() for line in post if line.strip())
    if left and right:
        return left, right
    if direction == "out":
        if is_first_anchor and not left and not right and pre_text and _times_in(pre_text):
            return pre_text, _early_post_column(post)
        return left, right or post_text
    if right:
        return pre_text, right
    return left or pre_text, post_text


def _filter_route(stops: list[BStop], direction: str) -> list[BStop]:
    route = UP_OUT if direction == "out" else list(reversed(UP_OUT))
    allowed = set(route)
    order = {code: idx for idx, code in enumerate(route)}
    filtered = [stop for stop in stops if stop.station_code in allowed]
    filtered.sort(key=lambda stop: order[stop.station_code])
    return filtered


def _parse_korplan_block(lines: list[str], left_train: str, right_train: str, direction: str) -> dict[str, list[BStop]]:
    header_idx = next((i for i, line in enumerate(lines) if KORPLAN_HEADER.search(line)), None)
    if header_idx is None:
        return {}
    body = lines[header_idx + 1 :]
    anchors: list[tuple[int, str, str, str, str]] = []
    for idx, line in enumerate(body):
        split = _split_line(line)
        if split is None:
            continue
        left, abbrev, right = split
        station_code = STATION_ABBREV.get(abbrev)
        if station_code is None:
            continue
        anchors.append((idx, abbrev, station_code, left, right))

    parsed: dict[str, list[BStop]] = {left_train: [], right_train: []}
    for pos, (line_idx, _abbrev, station_code, left, right) in enumerate(anchors):
        prev_idx = anchors[pos - 1][0] if pos > 0 else -1
        start = line_idx + 1
        end = anchors[pos + 1][0] if pos + 1 < len(anchors) else len(body)
        pre = body[prev_idx + 1 : line_idx]
        post = body[start:end]
        left_text, right_text = _block_column_texts(
            left, right, pre, post, direction, is_first_anchor=(pos == 0)
        )
        is_first = pos == 0
        is_last = pos == len(anchors) - 1
        parsed[left_train].append(
            _column_to_stop(
                station_code,
                _parse_column(left_text),
                is_first=is_first,
                is_last=is_last,
                train_num=left_train,
                direction=direction,
            )
        )
        parsed[right_train].append(
            _column_to_stop(
                station_code,
                _parse_column(right_text),
                is_first=is_first,
                is_last=is_last,
                train_num=right_train,
                direction=direction,
            )
        )

    return {
        train: _trim_endpoint_stops(_filter_route(stops, direction))
        for train, stops in parsed.items()
        if stops
    }


def _page_direction(page_num: int) -> str:
    if page_num <= 14:
        return "out"
    if 16 <= page_num <= 23:
        return "in"
    return ""


def _parse_page(page_num: int, text: str) -> dict[str, list[BStop]]:
    direction = _page_direction(page_num)
    if not direction:
        return {}
    lines = [line.strip() for line in text.splitlines() if line.strip()]
    train_nums: list[str] = []
    for line in lines:
        match = TRAIN_HEADER.match(line)
        if match and "G" in line:
            train_nums.append(match.group(1))
    if len(train_nums) < 2:
        return {}
    left_train, right_train = train_nums[0], train_nums[1]
    return _parse_korplan_block(lines, left_train, right_train, direction)


def parse_b_pdf(path: Path | None = None) -> tuple[dict[str, list[BStop]], dict[str, str]]:
    """Return (train_num → stops, train_num → out|in from korplan page)."""
    pdf_path = path or B_PDF_PATH
    from pypdf import PdfReader  # noqa: PLC0415

    reader = PdfReader(str(pdf_path))
    by_train: dict[str, list[BStop]] = {}
    directions: dict[str, str] = {}
    for page_num, page in enumerate(reader.pages, start=1):
        text = page.extract_text() or ""
        page_direction = _page_direction(page_num)
        if not page_direction:
            continue
        for train, stops in _parse_page(page_num, text).items():
            by_train[train] = stops
            directions[train] = page_direction
    return by_train, directions


def _timetable_for_train(train_num: str) -> str | None:
    if train_num in GREEN_RAIL_TRAIN_TYPES:
        return "green"
    if train_num in YELLOW_RAIL_TRAIN_TYPES:
        return "yellow"
    return None


def _service_code(timetable: str, train_num: str, direction: str) -> str:
    return f"{timetable}-{train_num}-{direction}"


def _anslag_stops_by_service() -> dict[str, list[tuple[str, str, str, str]]]:
    return {code: stops for code, _tt, _route, stops in service_definitions() if "-bus-" not in code}


def _stop_modes_are_bare(stop: BStop) -> bool:
    return (
        stop.ank_pickup_mode == "none"
        and stop.ank_dropoff_mode == "none"
        and stop.avg_pickup_mode == "none"
        and stop.avg_dropoff_mode == "none"
    )


def _modes_from_anslag_symbol(
    symbol: str,
    *,
    is_first: bool,
    is_last: bool,
    has_time: bool,
) -> tuple[str, str, str, str]:
    pickup, dropoff = symbol_to_flags(symbol, is_origin=is_first, is_last=is_last)
    return four_modes_from_flags(
        pickup,
        dropoff,
        is_origin=is_first,
        is_last=is_last,
        has_time=has_time,
    )


def _overlay_missing_times(stops: list[BStop], service_code: str) -> list[BStop]:
    """Fill missing times and bare modes using anslag tables (B modes win when present)."""
    anslag_rows = _anslag_stops_by_service().get(service_code, [])
    anslag = {station: (arr, dep, sym) for station, arr, dep, sym in anslag_rows}
    if not anslag:
        return stops
    total = len(stops)
    merged: list[BStop] = []
    for seq, stop in enumerate(stops, start=1):
        if stop.pass_through:
            merged.append(stop)
            continue
        arr, dep, symbol = anslag.get(stop.station_code, ("", "", ""))
        arrival = stop.arrival or arr
        departure = stop.departure or dep
        has_time = bool(arrival or departure)
        ank_pu, ank_do, avg_pu, avg_do = (
            stop.ank_pickup_mode,
            stop.ank_dropoff_mode,
            stop.avg_pickup_mode,
            stop.avg_dropoff_mode,
        )
        if _stop_modes_are_bare(stop) and has_time:
            ank_pu, ank_do, avg_pu, avg_do = _modes_from_anslag_symbol(
                symbol,
                is_first=(seq == 1),
                is_last=(seq == total),
                has_time=has_time,
            )
        merged.append(
            BStop(stop.station_code, arrival, departure, ank_pu, ank_do, avg_pu, avg_do, stop.pass_through)
        )
    return merged


def _continuation_leg_from_marielund(stops: list[BStop]) -> list[BStop]:
    ml_idx = next((i for i, stop in enumerate(stops) if stop.station_code == MARIELUND), None)
    if ml_idx is None:
        return stops
    leg2 = list(stops[ml_idx:])
    first = leg2[0]
    leg2[0] = BStop(
        first.station_code,
        first.arrival,
        first.departure,
        first.ank_pickup_mode,
        first.ank_dropoff_mode,
        first.avg_pickup_mode,
        first.avg_dropoff_mode,
    )
    return _trim_endpoint_stops(leg2)


def _train_direction(train_num: str, directions: dict[str, str]) -> str | None:
    if train_num in directions:
        return directions[train_num]
    if train_num in GREEN_OUT or train_num in YELLOW_OUT:
        return "out"
    if train_num in GREEN_IN or train_num in YELLOW_IN:
        return "in"
    return None


def b_rail_service_stops(
    by_train: dict[str, list[BStop]],
    directions: dict[str, str] | None = None,
) -> dict[str, list[BStop]]:
    """Map B trains to service_code stop lists (Marielund splits applied)."""
    route_dirs = directions or {}
    services: dict[str, list[BStop]] = {}
    for train_num, stops in by_train.items():
        direction = _train_direction(train_num, route_dirs)
        if direction is None:
            continue
        timetable = _timetable_for_train(train_num)
        if timetable is None:
            continue
        if train_num in CONTINUATION_TRAINS:
            stops = _continuation_leg_from_marielund(stops)
        change = MARIELUND_TRAIN_CHANGES.get((direction, train_num))
        if change is None:
            code = _service_code(timetable, train_num, direction)
            services[code] = _overlay_missing_times(stops, code)
            continue
        cont_num, _cont_type = change
        ml_idx = next((i for i, stop in enumerate(stops) if stop.station_code == MARIELUND), None)
        if ml_idx is None:
            code = _service_code(timetable, train_num, direction)
            services[code] = _overlay_missing_times(stops, code)
            continue
        leg1 = list(stops[: ml_idx + 1])
        last = leg1[-1]
        leg1[-1] = BStop(last.station_code, last.arrival, "", last.ank_pickup_mode, last.ank_dropoff_mode, "none", "none")
        leg1 = _trim_endpoint_stops(leg1)
        leg2 = list(stops[ml_idx:])
        first = leg2[0]
        leg2[0] = BStop(first.station_code, "", first.departure, "none", "none", first.avg_pickup_mode, first.avg_dropoff_mode)
        leg2 = _trim_endpoint_stops(leg2)
        code = _service_code(timetable, train_num, direction)
        services[code] = _overlay_missing_times(leg1, code)
        cont_code = _service_code(timetable, cont_num, direction)
        if cont_num not in by_train:
            services[cont_code] = _overlay_missing_times(leg2, cont_code)
    return services


def b_stop_to_csv_row(
    service_code: str,
    seq: int,
    stop: BStop,
    *,
    total_stops: int,
) -> str:
    has_time = bool(stop.arrival or stop.departure)
    approx = approximate_time_for_stop(
        is_origin=(seq == 1),
        is_last=(seq == total_stops),
        has_time=has_time,
    )
    return (
        f"{service_code},{seq},{stop.station_code},{stop.arrival},{stop.departure},"
        f"{stop.ank_pickup_mode},{stop.ank_dropoff_mode},{stop.avg_pickup_mode},{stop.avg_dropoff_mode},"
        f"{approx},1"
    )
