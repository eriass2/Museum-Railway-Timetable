import { createApp, type App } from 'vue';

/** Run a composable inside a mounted component (lifecycle hooks work). */
export function withSetup<T>(composable: () => T): { result: T; app: App; unmount: () => void } {
  let result!: T;
  const app = createApp({
    setup() {
      result = composable();
      return () => null;
    },
  });
  app.mount(document.createElement('div'));
  return {
    result,
    app,
    unmount: () => app.unmount(),
  };
}
