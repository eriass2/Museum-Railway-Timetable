import type { Ref } from 'vue';
import type { PricesPayload } from '../api/adminRest';
import { slugPriceKey, uniquePriceKey } from '../utils/priceSchemaKeys';

function ensureAfternoonReturnCells(data: PricesPayload): void {
  if (!data.afternoon_return) {
    data.afternoon_return = {};
  }
  for (const cat of Object.keys(data.categories)) {
    if (data.afternoon_return[cat] === undefined) {
      data.afternoon_return[cat] = null;
    }
  }
}

function ensureMatrixCells(data: PricesPayload): void {
  for (const ticket of Object.keys(data.ticket_types)) {
    if (!data.matrix[ticket]) {
      data.matrix[ticket] = {};
    }
    for (const cat of Object.keys(data.categories)) {
      if (!data.matrix[ticket][cat]) {
        data.matrix[ticket][cat] = {};
      }
      for (const zone of data.zones) {
        if (data.matrix[ticket][cat][zone] === undefined) {
          data.matrix[ticket][cat][zone] = null;
        }
      }
    }
  }
  ensureAfternoonReturnCells(data);
}

export function usePriceSchemaEditor(data: Ref<PricesPayload | null>) {
  function addTicketType(label: string): void {
    if (!data.value || !label.trim()) {
      return;
    }
    const key = uniquePriceKey(slugPriceKey(label.trim(), 'ticket'), data.value.ticket_types);
    data.value.ticket_types[key] = label.trim();
    ensureMatrixCells(data.value);
  }

  function removeTicketType(key: string): void {
    if (!data.value || Object.keys(data.value.ticket_types).length <= 1) {
      return;
    }
    const nextTypes = { ...data.value.ticket_types };
    delete nextTypes[key];
    data.value.ticket_types = nextTypes;
    const nextMatrix = { ...data.value.matrix };
    delete nextMatrix[key];
    data.value.matrix = nextMatrix;
  }

  function addCategory(label: string): void {
    if (!data.value || !label.trim()) {
      return;
    }
    const key = uniquePriceKey(slugPriceKey(label.trim(), 'category'), data.value.categories);
    data.value.categories[key] = label.trim();
    data.value.afternoon_return[key] = null;
    ensureMatrixCells(data.value);
  }

  function removeCategory(key: string): void {
    if (!data.value || Object.keys(data.value.categories).length <= 1) {
      return;
    }
    const nextCats = { ...data.value.categories };
    delete nextCats[key];
    data.value.categories = nextCats;
    const nextMatrix = { ...data.value.matrix };
    for (const ticket of Object.keys(nextMatrix)) {
      if (nextMatrix[ticket][key]) {
        const row = { ...nextMatrix[ticket] };
        delete row[key];
        nextMatrix[ticket] = row;
      }
    }
    const nextAfternoon = { ...data.value.afternoon_return };
    delete nextAfternoon[key];
    data.value.afternoon_return = nextAfternoon;
    data.value.matrix = nextMatrix;
  }

  function addZone(zone: number): void {
    if (!data.value || zone < 1 || zone > 99 || data.value.zones.includes(zone)) {
      return;
    }
    data.value.zones = [...data.value.zones, zone].sort((a, b) => a - b);
    ensureMatrixCells(data.value);
  }

  function removeZone(zone: number): void {
    if (!data.value || data.value.zones.length <= 1) {
      return;
    }
    data.value.zones = data.value.zones.filter((z) => z !== zone);
    const nextMatrix = { ...data.value.matrix };
    for (const ticket of Object.keys(nextMatrix)) {
      for (const cat of Object.keys(nextMatrix[ticket])) {
        if (nextMatrix[ticket][cat][zone] !== undefined) {
          const row = { ...nextMatrix[ticket][cat] };
          delete row[zone];
          nextMatrix[ticket][cat] = row;
        }
      }
    }
    data.value.matrix = nextMatrix;
  }

  return {
    addTicketType,
    removeTicketType,
    addCategory,
    removeCategory,
    addZone,
    removeZone,
    ensureMatrixCells,
  };
}
