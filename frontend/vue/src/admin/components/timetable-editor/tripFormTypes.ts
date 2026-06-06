export type TimetableTripDraft = {
  route_id: number;
  train_type_id: number;
  end_station_id: number;
  service_number: string;
  highlight_label: string;
  highlight_color: string;
  highlight_note: string;
};

export function emptyTripDraft(): TimetableTripDraft {
  return {
    route_id: 0,
    train_type_id: 0,
    end_station_id: 0,
    service_number: '',
    highlight_label: '',
    highlight_color: '#fff9c4',
    highlight_note: '',
  };
}

export type TripServiceApiBody = {
  route_id: number;
  train_type_id?: number;
  end_station_id?: number;
  service_number?: string;
  highlight_label?: string;
  highlight_color?: string;
  highlight_note?: string;
};

export function tripDraftToApiBody(draft: TimetableTripDraft): TripServiceApiBody {
  return {
    route_id: draft.route_id,
    train_type_id: draft.train_type_id || undefined,
    end_station_id: draft.end_station_id || undefined,
    service_number: draft.service_number,
    highlight_label: draft.highlight_label.trim(),
    highlight_color: draft.highlight_color,
    highlight_note: draft.highlight_note,
  };
}
