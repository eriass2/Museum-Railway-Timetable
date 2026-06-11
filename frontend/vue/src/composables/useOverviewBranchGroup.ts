import { computed, toValue, type MaybeRefOrGetter } from 'vue';
import { branchTripIsCancelled, branchTripNoticeDetail } from '../shared/branchTripCancelled';
import type { OverviewUiLabels } from '../shared/overviewUiLabels';
import {
  formatCardTrip,
  formatDeparturesAria,
  formatTrainConnecting,
} from '../shared/overviewUiLabels';
import { ROAD_BUS_TRAIN_TYPE_SLUG } from '../shared/trainTypeIcons';
import type {
  TimetableBranchGroup,
  TimetableBranchTrip,
  TimetableOverviewIconUrls,
} from '../types/timetableOverview';
import { trainTypeIconUrl } from '../utils/overviewGrid';

export function useOverviewBranchGroup(
  group: MaybeRefOrGetter<TimetableBranchGroup>,
  iconUrls: MaybeRefOrGetter<TimetableOverviewIconUrls>,
  labels: MaybeRefOrGetter<OverviewUiLabels>,
) {
  const busIconUrl = computed(() =>
    trainTypeIconUrl(toValue(iconUrls), ROAD_BUS_TRAIN_TYPE_SLUG),
  );

  function departuresAria(): string {
    const g = toValue(group);
    const l = toValue(labels);
    return formatDeparturesAria(l.departuresAria, g.routeLabel);
  }

  function trainLabel(serviceNumber: string, timeDisplay: string): string {
    return formatTrainConnecting(toValue(labels).trainConnecting, serviceNumber, timeDisplay);
  }

  function tripCancelled(trip: TimetableBranchTrip): boolean {
    return branchTripIsCancelled(trip);
  }

  function tripNoticeDetail(trip: TimetableBranchTrip): string {
    return branchTripNoticeDetail(trip, toValue(labels).cancelledLabel);
  }

  function cardTripLabel(tripNumber: string): string {
    return formatCardTrip(toValue(labels).cardTrip, tripNumber);
  }

  return { busIconUrl, departuresAria, trainLabel, tripCancelled, tripNoticeDetail, cardTripLabel };
}
