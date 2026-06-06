import type { App } from 'vue';
import type { MrtRestConfig } from '../config/types';

export type MrtLogSource = 'admin' | 'wizard' | 'overview' | 'month' | 'index';
export type MrtLogLevel = 'error' | 'warn' | 'info';

export type MrtLogEntry = {
  level: MrtLogLevel;
  source: MrtLogSource;
  message: string;
  context?: Record<string, unknown>;
};

type MrtLogRelay = {
  restUrl: string;
  restNonce: string;
};

type MrtLogRuntime = {
  isDevMode: boolean;
  defaultSource: MrtLogSource;
  relay?: MrtLogRelay;
};

const runtime: MrtLogRuntime = {
  isDevMode: false,
  defaultSource: 'admin',
};

let rejectionListenerInstalled = false;

export function configureMrtLog(options: {
  isDevMode?: boolean;
  defaultSource?: MrtLogSource;
  relay?: MrtLogRelay;
}): void {
  if (options.isDevMode !== undefined) {
    runtime.isDevMode = options.isDevMode;
  }
  if (options.defaultSource) {
    runtime.defaultSource = options.defaultSource;
  }
  if (options.relay !== undefined) {
    runtime.relay = options.relay;
  }
}

export function configureMrtLogFromRestConfig(
  config: MrtRestConfig,
  defaultSource: MrtLogSource,
): void {
  configureMrtLog({
    isDevMode: config.isDevMode ?? false,
    defaultSource,
    relay:
      config.isDevMode && config.restUrl && config.restNonce && defaultSource === 'admin'
        ? { restUrl: config.restUrl, restNonce: config.restNonce }
        : undefined,
  });
}

export function resolveMrtLogSource(config: MrtRestConfig): MrtLogSource {
  return config.app ?? runtime.defaultSource;
}

export function mrtLog(entry: MrtLogEntry): void {
  if (!runtime.isDevMode) {
    return;
  }

  const prefix = `[MRT ${entry.source}]`;
  const payload = entry.context ? [entry.message, entry.context] : [entry.message];
  if (entry.level === 'warn') {
    console.warn(prefix, ...payload);
  } else if (entry.level === 'info') {
    console.info(prefix, ...payload);
  } else {
    console.error(prefix, ...payload);
  }

  if (entry.source === 'admin' && runtime.relay) {
    void relayToServer(entry);
  }
}

async function relayToServer(entry: MrtLogEntry): Promise<void> {
  const relay = runtime.relay;
  if (!relay?.restUrl) {
    return;
  }

  const base = relay.restUrl.replace(/\/$/, '');
  try {
    await fetch(`${base}/dev/client-log`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': relay.restNonce,
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        level: entry.level,
        source: entry.source,
        message: entry.message,
        context: entry.context ?? {},
      }),
    });
  } catch {
    // Logging must never break the app.
  }
}

export function installMrtErrorHandlers(app: App, source: MrtLogSource): void {
  app.config.errorHandler = (err, _instance, info) => {
    mrtLog({
      level: 'error',
      source,
      message: err instanceof Error ? err.message : String(err),
      context: {
        info,
        stack: err instanceof Error ? err.stack : undefined,
      },
    });
  };

  if (rejectionListenerInstalled) {
    return;
  }
  rejectionListenerInstalled = true;
  window.addEventListener('unhandledrejection', (event) => {
    mrtLog({
      level: 'error',
      source: runtime.defaultSource,
      message: event.reason instanceof Error ? event.reason.message : String(event.reason),
      context: {
        stack: event.reason instanceof Error ? event.reason.stack : undefined,
      },
    });
  });
}

/** Reset module state between unit tests. */
export function resetMrtLogForTests(): void {
  runtime.isDevMode = false;
  runtime.defaultSource = 'admin';
  runtime.relay = undefined;
  rejectionListenerInstalled = false;
}
