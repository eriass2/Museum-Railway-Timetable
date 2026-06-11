import {
  formatDeviationTripLabel,
  isCancelledDeviationNotice,
  type DeviationRow,
  type DeviationTripSource,
} from './deviationsPayload';

export function deviationTripLabelForId(
  services: DeviationTripSource[],
  serviceId: number,
): string {
  const service = services.find((s) => s.id === serviceId);
  return service ? formatDeviationTripLabel(service) : '—';
}

export function deviationTrainTypeName(
  trainTypes: { id: number; name: string }[],
  typeId: number,
): string {
  if (typeId <= 0) {
    return '—';
  }
  return trainTypes.find((t) => t.id === typeId)?.name ?? '—';
}

export function deviationRowIsCancelled(row: DeviationRow, cancelledNotice: string): boolean {
  return isCancelledDeviationNotice(row.notice, cancelledNotice);
}

export function deviationNoticePreview(notice: string): string {
  const text = notice.trim();
  return text || '—';
}
