export type TimetableTripDraft = {
  line_code: string;
  toward_station_id: number;
  train_type_id: number;
  service_number: string;
  highlight_label: string;
  highlight_color: string;
  highlight_note: string;
};

export function emptyTripDraft(): TimetableTripDraft {
  return {
    line_code: '',
    toward_station_id: 0,
    train_type_id: 0,
    service_number: '',
    highlight_label: '',
    highlight_color: '#fff9c4',
    highlight_note: '',
  };
}

export type TripServiceApiBody = {
  line_code: string;
  toward_station_id: number;
  train_type_id?: number;
  service_number?: string;
  highlight_label?: string;
  highlight_color?: string;
  highlight_note?: string;
};

export function tripDraftToApiBody(draft: TimetableTripDraft): TripServiceApiBody {
  return {
    line_code: draft.line_code,
    toward_station_id: draft.toward_station_id,
    train_type_id: draft.train_type_id || undefined,
    service_number: draft.service_number,
    highlight_label: draft.highlight_label.trim(),
    highlight_color: draft.highlight_color,
    highlight_note: draft.highlight_note,
  };
}

export function tripDraftIsComplete(draft: TimetableTripDraft): boolean {
  return draft.line_code !== '' && draft.toward_station_id > 0;
}

export function tripDraftSnapshot(draft: TimetableTripDraft): string {
  return JSON.stringify(draft);
}
