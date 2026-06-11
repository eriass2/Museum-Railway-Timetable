import type { Ref } from 'vue';
import { watch } from 'vue';
import type { CacheParams, CacheResource } from './cacheKeys';
import { buildResourceCacheKey } from './cacheKeys';
import { wizardPrefetchRelated } from './prefetchPolicy';

const STORAGE_KEY = 'mrt-wizard-resource-cache';

type CacheEntry<T> = {
  data: T;
  ts: number;
};

type SessionStore = {
  generation: number;
  entries: Record<string, CacheEntry<unknown>>;
};

export type ResourceFetchSpec<T> = {
  resource: CacheResource;
  params: CacheParams;
  request: () => Promise<T | null>;
};

export type ResourceLoadOptions = {
  priority?: 'user' | 'prefetch';
};

export type WizardResourceCache = {
  load: <T>(spec: ResourceFetchSpec<T>, options?: ResourceLoadOptions) => Promise<T | null>;
  prefetchRelated: <T>(
    resource: CacheResource,
    params: CacheParams,
    run: (spec: Pick<ResourceFetchSpec<T>, 'resource' | 'params'>) => Promise<T | null>,
  ) => void;
  clearIfGenerationStale: (generation: number) => void;
  clear: () => void;
};

function readSessionStore(): SessionStore | null {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return null;
    }
    const parsed = JSON.parse(raw) as SessionStore;
    if (!parsed || typeof parsed.generation !== 'number' || !parsed.entries) {
      return null;
    }
    return parsed;
  } catch {
    return null;
  }
}

function writeSessionStore(store: SessionStore): void {
  try {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(store));
  } catch {
    // Quota or private mode — memory cache still works.
  }
}

export function createWizardResourceCache(generationRef: Ref<number>): WizardResourceCache {
  const memory = new Map<string, CacheEntry<unknown>>();
  const inFlight = new Map<string, Promise<unknown>>();
  let sessionGeneration = generationRef.value;

  function syncSessionGeneration(generation: number): void {
    sessionGeneration = generation;
    const stored = readSessionStore();
    if (!stored || stored.generation !== generation) {
      writeSessionStore({ generation, entries: {} });
      return;
    }
    for (const [key, entry] of Object.entries(stored.entries)) {
      if (!memory.has(key)) {
        memory.set(key, entry);
      }
    }
  }

  function persistEntry(key: string, entry: CacheEntry<unknown>): void {
    memory.set(key, entry);
    const stored = readSessionStore();
    const base =
      stored && stored.generation === sessionGeneration
        ? stored
        : { generation: sessionGeneration, entries: {} as Record<string, CacheEntry<unknown>> };
    base.entries[key] = entry;
    writeSessionStore(base);
  }

  function buildKey(resource: CacheResource, params: CacheParams): string {
    return buildResourceCacheKey(generationRef.value, resource, params);
  }

  function getEntry<T>(key: string): T | null {
    const hit = memory.get(key);
    return hit ? (hit.data as T) : null;
  }

  async function fetchAndStore<T>(
    spec: ResourceFetchSpec<T>,
    key: string,
  ): Promise<T | null> {
    const pending = inFlight.get(key);
    if (pending) {
      return (await pending) as T | null;
    }
    const promise = (async () => {
      try {
        const data = await spec.request();
        if (data !== null) {
          persistEntry(key, { data, ts: Date.now() });
        }
        return data;
      } finally {
        inFlight.delete(key);
      }
    })();
    inFlight.set(key, promise);
    return promise;
  }

  async function load<T>(
    spec: ResourceFetchSpec<T>,
    options?: ResourceLoadOptions,
  ): Promise<T | null> {
    const key = buildKey(spec.resource, spec.params);
    const cached = getEntry<T>(key);
    if (cached !== null) {
      if (options?.priority !== 'prefetch') {
        void fetchAndStore(spec, key);
      }
      return cached;
    }
    return fetchAndStore(spec, key);
  }

  function prefetchRelated<T>(
    resource: CacheResource,
    params: CacheParams,
    run: (spec: Pick<ResourceFetchSpec<T>, 'resource' | 'params'>) => Promise<T | null>,
  ): void {
    for (const related of wizardPrefetchRelated(resource, params)) {
      const key = buildKey(related.resource, related.params);
      if (getEntry(key) !== null || inFlight.has(key)) {
        continue;
      }
      void load(
        {
          resource: related.resource,
          params: related.params,
          request: () => run({ resource: related.resource, params: related.params }),
        },
        { priority: 'prefetch' },
      );
    }
  }

  function clearIfGenerationStale(generation: number): void {
    if (generationRef.value === generation) {
      return;
    }
    generationRef.value = generation;
    memory.clear();
    inFlight.clear();
    syncSessionGeneration(generation);
  }

  function clear(): void {
    memory.clear();
    inFlight.clear();
    writeSessionStore({ generation: sessionGeneration, entries: {} });
  }

  syncSessionGeneration(generationRef.value);
  watch(
    () => generationRef.value,
    (gen) => clearIfGenerationStale(gen),
    { immediate: true },
  );

  return { load, prefetchRelated, clearIfGenerationStale, clear };
}

export function clearWizardResourceCache(cache: WizardResourceCache): void {
  cache.clear();
}
