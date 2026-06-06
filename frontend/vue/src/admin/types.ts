export type AdminHelpSection = {
  title: string;
  body: string;
  adminOnly?: boolean;
  devOnly?: boolean;
};

export type AdminHelpShortcode = {
  tag: string;
  title: string;
  summary: string;
  example: string;
  params: { name: string; desc: string }[];
};

export type AdminHelpFaqItem = {
  q: string;
  a: string;
  aEditor?: string;
};

export type AdminHelpContent = {
  title: string;
  intro: string;
  panelWhat: string;
  colPart: string;
  colDescription: string;
  partAdmin: string;
  partAdminDesc: string;
  partPublic: string;
  partPublicDesc: string;
  panelAdmin: string;
  panelAdminHint: string;
  panelWorkflow: string;
  panelOperations: string;
  panelShortcodes: string;
  panelFaq: string;
  panelMore: string;
  panelPriceZones: string;
  priceZonesIntro: string;
  priceZonesSteps: string[];
  shortcodesIntro: string;
  shortcodesDevHint: string;
  shortcodeExample: string;
  paramName: string;
  operationsNote: string;
  moreInfoBody: string;
  moreInfoDocs: string;
  adminSections: AdminHelpSection[];
  workflowSteps: string[];
  operations: { title: string; body: string }[];
  shortcodes: AdminHelpShortcode[];
  faq: AdminHelpFaqItem[];
  shortcodesPageTitle: string;
  shortcodesPageIntro: string;
  shortcodesHowToTitle: string;
  shortcodesHowToSteps: string[];
  shortcodesQuickRefTitle: string;
  shortcodesQuickRefHint: string;
  shortcodesColShortcode: string;
  shortcodesColUse: string;
  shortcodesSetupTitle: string;
  shortcodesSetupSteps: string[];
  shortcodesWidgetTitle: string;
  shortcodesWidgetNote: string;
  helpLinkToShortcodes: string;
};

export type AdminImportExportPackageFile = {
  file: string;
  required: boolean;
  desc: string;
};

export type AdminImportExportGuide = {
  intro: string;
  buildTitle: string;
  buildSteps: string[];
  packageTitle: string;
  packageHint: string;
  colFile: string;
  colRequired: string;
  colDescription: string;
  requiredYes: string;
  requiredNo: string;
  packageFiles: AdminImportExportPackageFile[];
  orderTitle: string;
  orderHint: string;
  orderSteps: string[];
  keysTitle: string;
  keysIntro: string;
  keysTips: string[];
  modesTitle: string;
  modeMergeDetail: string;
  modeOverrideDetail: string;
  modeOverrideWarning: string;
  tipsTitle: string;
  tips: string[];
  docsNote: string;
  workflowTitle: string;
  workflowSteps: string[];
  manifestAutoNote: string;
  guideDisclosureSummary: string;
};

export type AdminClientConfig = {
  restUrl: string;
  restNonce: string;
  initialRoute: string;
  adminBase: string;
  canManage: boolean;
  canOperate: boolean;
  isDevMode: boolean;
  trainTypeIconUrls: Record<string, string>;
  componentDemoAdminUrl?: string;
  strings?: Record<string, string>;
  help?: AdminHelpContent;
  importExportGuide?: AdminImportExportGuide;
};

export type DashboardWarning = {
  code: string;
  message: string;
  route: string;
};

export type TrafficToday = {
  date: string;
  timetable_id: number;
  timetable_title: string;
  services_count: number;
  cancelled_count: number;
  all_cancelled: boolean;
};

export type DashboardPayload = {
  stats: Record<string, number>;
  warnings: DashboardWarning[];
  next_traffic: { date: string; timetable_id: number; title: string }[];
  traffic_today: TrafficToday | null;
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
  service_number: string;
  end_station_id?: number;
  route_id: number;
  route_name: string;
  train_type_id: number;
  train_type_name: string;
  train_type_icon_key?: string;
  destination: string;
};

export type TimetableDetail = {
  id: number;
  title: string;
  type: string;
  dates: string[];
  services: TimetableServiceRow[];
  routes: { id: number; title: string }[];
  train_types: { id: number; name: string; icon_key?: string }[];
};

export type TrainChangeMap = Record<string, { typeName: string; serviceNumber: string }>;

export type StationRow = {
  id: number;
  title: string;
  station_type: string;
  bus_suffix: boolean;
  lat: string;
  lng: string;
  display_order: number;
  price_zones: number[];
  train_change_map?: TrainChangeMap;
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
  return {
    ...cfg,
    isDevMode: cfg.isDevMode ?? false,
    trainTypeIconUrls: cfg.trainTypeIconUrls ?? {},
    strings: cfg.strings ?? {},
    help: cfg.help,
  };
}
