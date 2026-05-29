export type TimetableOverviewIconUrls = Record<string, string>;

export type TimetablePrintKeyRow = {
  symbol: string;
  text: string;
};

export type TimetableOverviewColumn = {
  serviceNumber: string;
  trainTypeName: string;
  trainTypeSlug: string;
  iconKey: string;
  isSpecial: boolean;
  specialName: string;
  highlightColor: string;
};

export type TimetableTimeCell = {
  text: string;
  specialName?: string;
  highlightColor?: string;
};

export type TimetableVehicleCell = {
  typeName: string;
  serviceNumber: string;
  iconKey: string;
  detail: string;
};

export type TimetableTransferCell = {
  vehicles: TimetableVehicleCell[];
};

export type TimetableOverviewRow =
  | {
      kind: 'from' | 'to' | 'station' | 'arrival' | 'departure';
      label: string;
      cells: TimetableTimeCell[];
    }
  | {
      kind: 'trainChange' | 'busConnection';
      label: string;
      cells: TimetableTransferCell[];
    };

export type TimetableRailGroup = {
  kind: 'rail';
  routeLabel: string;
  fromLabel: string;
  toLabel: string;
  columns: TimetableOverviewColumn[];
  rows: TimetableOverviewRow[];
};

export type TimetableBranchTrip = {
  trip: string;
  fromTime: string;
  toTime: string;
  midTime?: string;
  connectingTrains: { serviceNumber: string; timeDisplay: string }[];
};

export type TimetableBranchGroup = {
  kind: 'branch';
  routeLabel: string;
  fromLabel: string;
  toLabel: string;
  midLabel?: string;
  trips: TimetableBranchTrip[];
};

export type TimetableOverviewGroup = TimetableRailGroup | TimetableBranchGroup;

export type TimetableOverviewPayload = {
  scope: 'timetable' | 'day';
  timetableId: number;
  title: string;
  dateYmd: string;
  timetableType: string;
  typeBanner: { label: string };
  printKey: TimetablePrintKeyRow[];
  iconUrls: TimetableOverviewIconUrls;
  groups: TimetableOverviewGroup[];
};
