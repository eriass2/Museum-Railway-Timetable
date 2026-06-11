type ServiceTripSource = {
  service_number: string;
  destination: string;
  route_name: string;
};

export function formatServiceTripLabel(
  service: ServiceTripSource | undefined,
  fallback: string,
): string {
  if (!service) {
    return fallback;
  }
  return `${service.service_number} — ${service.destination || service.route_name}`;
}
