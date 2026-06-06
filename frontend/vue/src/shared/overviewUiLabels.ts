import type { MrtRestConfig } from '../config/types';
import { resolveMrtString } from '../utils/mrtStrings';

export type OverviewUiLabels = {
  printKeyTitle: string;
  printKeySymbolCol: string;
  printKeyMeaningCol: string;
  printKeyNote: string;
  deviationPlanned: string;
  deviationFromPlan: string;
  cancelledLabel: string;
  departuresAria: string;
  branchNote: string;
  colTrip: string;
  colConnectingTrain: string;
  trainConnecting: string;
  cardTrip: string;
};

export function overviewUiLabels(config: MrtRestConfig): OverviewUiLabels {
  return {
    printKeyTitle: resolveMrtString(config, 'ovPrintKeyTitle', 'Förklaringar'),
    printKeySymbolCol: resolveMrtString(config, 'ovPrintKeySymbolCol', 'Tecken'),
    printKeyMeaningCol: resolveMrtString(config, 'ovPrintKeyMeaningCol', 'Betydelse'),
    printKeyNote: resolveMrtString(
      config,
      'ovPrintKeyNote',
      'Med reservation för ändring av tågtyp.',
    ),
    deviationPlanned: resolveMrtString(config, 'ovDeviationPlanned', 'Planerat: %s'),
    deviationFromPlan: resolveMrtString(
      config,
      'ovDeviationFromPlan',
      'Avvikelse från planerad tågtyp',
    ),
    cancelledLabel: resolveMrtString(config, 'ovCancelledLabel', 'Inställd'),
    departuresAria: resolveMrtString(config, 'ovDeparturesAria', 'Avgångar %s'),
    branchNote: resolveMrtString(config, 'ovBranchNote', 'Anslutningsbuss'),
    colTrip: resolveMrtString(config, 'ovColTrip', 'Tur'),
    colConnectingTrain: resolveMrtString(config, 'ovColConnectingTrain', 'Anslutande tåg'),
    trainConnecting: resolveMrtString(config, 'ovTrainConnecting', 'Tåg %1$s %2$s'),
    cardTrip: resolveMrtString(config, 'ovCardTrip', 'Tur %s'),
  };
}

export function formatDeviationPlanned(template: string, plannedName: string): string {
  return template.replace('%s', plannedName);
}

export function formatDeparturesAria(template: string, routeLabel: string): string {
  return template.replace('%s', routeLabel);
}

export function formatTrainConnecting(
  template: string,
  serviceNumber: string,
  timeDisplay: string,
): string {
  return template.replace('%1$s', serviceNumber).replace('%2$s', timeDisplay);
}

export function formatCardTrip(template: string, trip: string): string {
  return template.replace('%s', trip);
}
