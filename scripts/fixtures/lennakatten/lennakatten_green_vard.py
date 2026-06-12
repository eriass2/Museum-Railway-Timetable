"""Clone GRÖN lördag rail rows to green-vard (ons/tors summer)."""

from __future__ import annotations


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


def _drop_green_vard_lines(lines: list[str]) -> list[str]:
    kept: list[str] = []
    for line in lines:
        if not line.strip():
            continue
        code = line.split(",", 1)[0]
        if code.startswith("green-vard-"):
            continue
        kept.append(line)
    return kept


def refresh_green_vard_lines(
    services: list[str],
    stoptimes: list[str],
    train_types: list[str],
) -> tuple[list[str], list[str], list[str]]:
    """Replace green-vard rows with a fresh clone of current green rail."""
    services = _drop_green_vard_lines(services)
    stoptimes = _drop_green_vard_lines(stoptimes)
    train_types = _drop_green_vard_lines(train_types)

    vard_svc, vard_st, vard_tt = clone_green_rail_to_vard(services, stoptimes, train_types)
    return (
        services + vard_svc,
        stoptimes + vard_st,
        train_types + vard_tt,
    )
