import { describe, expect, it } from 'vitest';
import { ref } from 'vue';
import { useAdminFormDirty } from '../src/admin/composables/useAdminFormDirty';

describe('useAdminFormDirty', () => {
  it('tracks unsaved changes', () => {
    const source = ref({ name: 'A' });
    const { dirty, syncSnapshot } = useAdminFormDirty(source);

    syncSnapshot();
    expect(dirty.value).toBe(false);

    source.value = { name: 'B' };
    expect(dirty.value).toBe(true);

    syncSnapshot();
    expect(dirty.value).toBe(false);
  });
});
