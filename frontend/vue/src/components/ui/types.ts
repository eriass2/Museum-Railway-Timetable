export type MrtAlertVariant = 'info' | 'error' | 'warning';

export type MrtComboboxOption = {
  id: number;
  label: string;
};

export type MrtLegendItem = {
  label: string;
  swatchClass?: string;
  dotClass?: string;
};

export type MrtStepProgressItem = {
  key: string;
  label: string;
  active?: boolean;
  done?: boolean;
};

export type MrtSegmentedOption<T extends string = string> = {
  value: T;
  label: string;
};

export type MrtVehicleItem = {
  kind: string;
  label: string;
  iconUrl?: string;
};
