export type AdminClientConfig = {
  restUrl: string;
  restNonce: string;
  initialRoute: string;
  adminBase: string;
  canManage: boolean;
  canOperate: boolean;
};

export type DashboardWarning = {
  code: string;
  message: string;
  route: string;
};

export type DashboardPayload = {
  stats: Record<string, number>;
  warnings: DashboardWarning[];
  next_traffic: { date: string; timetable_id: number; title: string }[];
  links: Record<string, string>;
  can_manage: boolean;
  can_operate: boolean;
};

export type TimetableListItem = {
  id: number;
  title: string;
  dates_count: number;
  trips_count: number;
};

export type TimetableServiceRow = {
  id: number;
  title: string;
  route_id: number;
  route_name: string;
  train_type_id: number;
  train_type_name: string;
  destination: string;
};

export type TimetableDetail = {
  id: number;
  title: string;
  type: string;
  dates: string[];
  services: TimetableServiceRow[];
  routes: { id: number; title: string }[];
  train_types: { id: number; name: string }[];
};

export type StationRow = {
  id: number;
  title: string;
  station_type: string;
  bus_suffix: boolean;
  lat: string;
  lng: string;
  display_order: number;
};

export type RouteRow = {
  id: number;
  title: string;
  start_station: number;
  end_station: number;
  station_ids: number[];
  stations: { id: number; name: string }[];
};

export type StopTimeRow = {
  id: number;
  name: string;
  sequence: number;
  stops_here: boolean;
  arrival_time: string;
  departure_time: string;
  pickup_allowed: boolean;
  dropoff_allowed: boolean;
};

export type TrainTypeRow = {
  id: number;
  name: string;
  slug: string;
  icon_key: string;
};

declare global {
  interface Window {
    mrtAdminVue?: AdminClientConfig;
  }
}

export function adminConfig(): AdminClientConfig {
  const cfg = window.mrtAdminVue;
  if (!cfg?.restUrl) {
    throw new Error('mrtAdminVue config missing');
  }
  return cfg;
}
